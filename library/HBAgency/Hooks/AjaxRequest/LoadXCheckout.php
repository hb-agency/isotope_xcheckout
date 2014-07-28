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
 
namespace HBAgency\Hooks\AjaxRequest;

use HBAgency\AjaxInput;

use Isotope\Isotope;

/**
 * Load a AJAX requested checkout module
 *
 * @copyright  HBAgency 2014
 * @author     Blair Winans <bwinans@hbagency.com>
 * @author     Adam Fisher <afisher@hbagency.com>
 * @package    IsotopeXCheckout
 */
class LoadXCheckout extends \Frontend
{

    /**
	 * Check if the request is specifically for XCheckout
	 */
	public function run()
	{
	    if(AjaxInput::get('mod')=='xcheckout' && AjaxInput::get('action')=='fmd' && intval(AjaxInput::get('id') > 0))
	    {
	        $this->setAjaxGetAndPostVals();
	            	    
    	    $varValue = \Controller::getFrontendModule(AjaxInput::get('id'));
    	    
    	    var_dump($varValue); exit;
    	    
    	    $varValue = json_encode(array
			(
				'token'		=> REQUEST_TOKEN,
				'content'	=> $varValue,
			));
			
			echo $varValue;
			exit;
	    }
	}
	
	/**
	 * Set all get and post vals so that xCheckout can proces them
	 */
	protected function setAjaxGetAndPostVals()
	{
	    //Set basic GET and POST vals for XCheckout
	    $arrGetVals = array('step');
	    $arrPostVals = array();
	    
	    //Get Billing/Shipping Address Fields
	    $arrBilling = Isotope::getConfig()->getBillingFieldsConfig();
	    $arrShipping = Isotope::getConfig()->getShippingFieldsConfig();
	    foreach($arrBilling as $strField)
	    {
    	   $arrPostVals[] = 'BillingAddress_' . $strField; 
	    }
	    foreach($arrShipping as $strField)
	    {
    	   $arrPostVals[] = 'ShippingAddress_' . $strField; 
	    }
	    
	    //Shipping and Payment Methods
	    $arrPostVals[] = 'ShippingMethod';
	    $arrPostVals[] = 'PaymentMethod';
	    
	    //Username/Password
	    $arrPostVals[] = 'username';
	    $arrPostVals[] = 'password';
	    $arrPostVals[] = 'password_confirm';
	    
	    //Form submits and RT
	    $arrPostVals[] = 'previousStep';
	    $arrPostVals[] = 'nextStep';
	    $arrPostVals[] = 'FORM_SUBMIT';
	    $arrPostVals[] = 'REQUEST_TOKEN';
        
        // HOOK: Add custom fields
		if (isset($GLOBALS['TL_HOOKS']['setAjaxGetAndPostVals']) && is_array($GLOBALS['TL_HOOKS']['setAjaxGetAndPostVals']))
		{
			foreach ($GLOBALS['TL_HOOKS']['setAjaxGetAndPostVals'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($arrGetVals, $arrPostVals);
			}
		}
		
		foreach($arrGetVals as $strValue)
		{
    		if(AjaxInput::get($strValue))
    		{
        		\Input::setGet($strValue, AjaxInput::get($strValue));
    		}
		}
		foreach($arrPostVals as $strValue)
		{
    		if(AjaxInput::post($strValue))
    		{
        		\Input::setPost($strValue, AjaxInput::post($strValue));
    		}
		}
		
	}
}