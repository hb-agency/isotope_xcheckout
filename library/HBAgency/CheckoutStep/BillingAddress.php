<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace HBAgency\CheckoutStep;

use Isotope\CheckoutStep\BillingAddress as IsoBillingAddress;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address as AddressModel;


class BillingAddress extends IsoBillingAddress implements IsotopeCheckoutStep
{
	/**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
    	//Set blnError to true until we have validated all widgets
        $this->blnError = true;
        
        return parent::generate();
    }
	

    /**
     * Get widget objects for address fields
     * CHANGE FROM PARENT:: ADD IN USERNAME AND PASSWORD FIELDS
     * @return  array
     */
    protected function getWidgets()
    {
    	//Parent getWidgets
    	$this->arrWidgets = parent::getWidgets();
    	
    	//************************ CUSTOM ****************************//
    	//Add in username and password fields
    	//Construct username widget
		$arrUserData = array
		(
			'label'	=> &$GLOBALS['TL_LANG']['MSC']['username'],
			'eval'	=> array('mandatory'=>true, 'unique'=>true, 'rgxp'=>'extnd', 'nospace'=>true, 'maxlength'=>64 ),
		);
					
		//Construct password widget
		$arrPassData = array
		(
			'label'	=> &$GLOBALS['TL_LANG']['MSC']['password'],
			'eval'	=> array('mandatory'=>true, 'rgxp'=>'extnd', 'minlength'=>$GLOBALS['TL_CONFIG']['minPasswordLength']),
		);
		
		$objExplainWidget = new \FormExplanation();
		$objExplainWidget->text = $GLOBALS['TL_LANG']['MSC']['registerMessage'];
		$objExplainWidget->class = 'registration_explanation';
		
		$objUserWidget = new \FormTextField(\FormTextField::getAttributesFromDca($arrUserData, 'username', $_SESSION['CHECKOUT_DATA']['billing_address']['username']));
		$objPassWidget = new \FormPassword(\FormPassword::getAttributesFromDca($arrPassData, 'password', $_SESSION['CHECKOUT_DATA']['billing_address']['password']));
		
		$objUserWidget->tableless = $this->objModule->tableless;
		$objUserWidget->rowClass = 'row_0 row_first';
		$objUserWidget->rowClassConfirm = 'row_0 row_first';	
		
		$objPassWidget->tableless = $this->objModule->tableless;
		$objPassWidget->rowClass = 'row_1';
		$objPassWidget->rowClassConfirm = 'row_1 row_last';	
		
		$this->arrWidgets[] = $objExplainWidget;
		$this->arrWidgets[] = $objUserWidget;
		$this->arrWidgets[] = $objPassWidget;
		//************************ CUSTOM ****************************//
		
        return $this->arrWidgets;
    }
    
    
    /**
     * Validate input and return address data
     * CHANGE FROM PARENT - SET blnError TO FALSE WHEN ALL WIDGETS HAVE VALIDATED
     * CHANGE FROM PARENT - ALSO CONSIDER USERNAME/PASSWORD FIELDS
     * @return  array
     */
    protected function validateFields($blnValidate)
    {
        $arrAddress = array();
        $arrWidgets = $this->getWidgets();
        
        //************************ CUSTOM ****************************//
        $blnIsValid = true; //Replacement for blnError here
        //************************ CUSTOM ****************************//

        foreach ($arrWidgets as $strName => $objWidget) {
            
            //************************ CUSTOM ****************************//
            //Don't validate username and password here!
            if($objWidget->name != 'username' && $objWidget->name != 'password' && $objWidget->name != 'password_confirm')
            {
            //************************ CUSTOM ****************************//  
                $arrData = &$GLOBALS['TL_DCA'][\Isotope\Model\Address::getTable()]['fields'][$strName];
    
                // Validate input
                if ($blnValidate) {
    
                    $objWidget->validate();
                    $varValue = $objWidget->value;
    
                    // Convert date formats into timestamps
                    if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim'))) {
                        $objDate  = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
                        $varValue = $objDate->tstamp;
                    }
    
                    // Do not submit if there are errors
                    if ($objWidget->hasErrors()) {
                        $blnIsValid = false; //****************CUSTOM!
                        $this->blnError = true;
                    } // Store current value
                    elseif ($objWidget->submitInput()) {
                        $arrAddress[$strName] = $varValue;
                    }
    
                } else {
    
                    \Input::setPost($objWidget->name, $objWidget->value);
    
                    $objValidator = clone $objWidget;
                    $objValidator->validate();
    
                    if ($objValidator->hasErrors()) {
                        $blnIsValid = false; //****************CUSTOM!
                        $this->blnError = true;
                    }
                }
            //************************ CUSTOM ****************************//
            //Handle username and password
            } else {
            
                $varUserValue = \Input::post('username');
				$varPassValue = \Input::post('password');
            
                // Validate input on Non-AJAX request
                if ($blnValidate && !\Environment::get('isAjaxRequest') && (!empty($varUserValue) || !empty($varPassValue) ) ) {
                    
                    //Validate the widget first
                    $objWidget->validate();
                    
                    // Check whether the password matches the username
    				if ($varUserValue == $varPassValue)
    				{
    					$objWidget->addError($GLOBALS['TL_LANG']['ERR']['passwordUnique']);
    				}
    				
    				//Check whether the username/email exists
    				$m = \MemberModel::getTable();
                    $objUnique = \MemberModel::findOneBy(array("$m.username=?", "$m.email=?"), array($varUserValue, $varPassValue));
                    if(null != $objUnique && $objWidget->name == 'username'){
                        $objWidget->addError($GLOBALS['TL_LANG']['ERR']['emailUnique']);
                    }
                    
                    if($objWidget->hasErrors()){
                         $blnIsValid = false;
                    }
                
                } else {
                
                    \Input::setPost($objWidget->name, $objWidget->value);
                    
                    $objValidator = clone $objWidget;
                    $objValidator->validate();
    
                    if ($objValidator->hasErrors()) {
                        $blnIsValid = false;
                    }
                
                }
                
            }
            
            //Success!
            if($blnIsValid){
                $this->blnError = false;
                //Encrypt & save the hashed password in the session so we can set it later in the Create_member Hook
				$_SESSION['CREATE_MEMBER']['password'] = \Encryption::encrypt($varPassValue);
				$_SESSION['CREATE_MEMBER']['username'] = $varUserValue;
            }
            
            //************************ CUSTOM ****************************//
        }

        return $arrAddress;
    }




}