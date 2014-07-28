<?php

/**
 * IsotopeXCheckout for Isotope eCommerce
 *
 * Copyright (C) 2011-2014 HB Agency
 *
 * @package    IsotopeXCheckout
 * @link       http://www.hbagency.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
namespace HBAgency\Module;

use Isotope\Module\Checkout as IsotopeCheckout;


/**
 * Class XCheckout
 * Adaptation of front end module Isotope "checkout".
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2014
 * @copyright  HB Agency 2014
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 * @author     Blair Winans <bwinans@hbagency.com>
 * @author     Adam Fisher <afisher@hbagency.com>
 */
class XCheckout extends IsotopeCheckout
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_xcheckout';
	
	/**
	 * Ajax
	 * @var bool
	 */
	protected $isAjax = false;


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE X-CHECKOUT ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Generate module
	 * @return void
	 */
	protected function compile()
	{
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope_xcheckout/assets/json2.js';
	    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope_xcheckout/assets/xcheckout.js';
        
        global $objPage;
        $this->Template->pageid = $objPage->id;
        $this->Template->step   = $this->strCurrentStep;
		parent::compile();
	}
	
	
	/**
	 * AJAX callback
	 * return content based on user input
	 */
	public function generateAjax()
	{	
		$this->isAjax = true;
	
		//Check for step and set to auto_item
		if($GLOBALS['TL_CONFIG']['useAutoItem'])
		{
			\Input::setGet('auto_item', \Input::get('step'));
		}
		
		$this->strCurrentStep = \Haste\Input\Input::getAutoItem('step');
		
		//Define Template object
		if ($this->arrData['space'][0] != '')
		{
			$this->arrStyle[] = 'margin-top:'.$this->arrData['space'][0].'px;';
		}

		if ($this->arrData['space'][1] != '')
		{
			$this->arrStyle[] = 'margin-bottom:'.$this->arrData['space'][1].'px;';
		}

		$this->Template = new \FrontendTemplate($this->strTemplate);
		$this->Template->setData($this->arrData);
				
		//Generate the methods
		$arrBuffer = $this->generateSteps($this->getSteps());
			
		return array(
			'lock'=> $this->doNotSubmit,
			'steps'=> $arrBuffer,
		);
	}
	
	/**
     * Allow steps to choose whether to show the form wrapper in the template
     * Useful for Direct Post Methods
     * @param	boolean
     * @return  void
     */
    public function setFormVisibility($blnShow)
    {    	
    	$this->Template->showForm = $blnShow;
    }

    /**
     * Run through all steps until we find the current one or one reports failure
     ********* COPIED FROM PARENT "generateSteps", we need to check for Ajax before redirecting *********
     * @param   array
     * @return  array
     */
    protected function generateStepsParent(array $arrSteps)
    {
        $intCurrentStep = 0;
        $intTotalSteps  = count($arrSteps);

        if (!isset($arrSteps[$this->strCurrentStep]) && !$this->isAjax) {
            $this->redirectToNextStep();
        }
        
        // Run trough all steps until we find the current one or one reports failure
        foreach ($arrSteps as $step => $arrModules) {
            $this->strFormId            = 'iso_mod_checkout_' . $step;
            $this->Template->formId     = $this->strFormId;
            $this->Template->formSubmit = $this->strFormId;

            $intCurrentStep += 1;
            $arrBuffer = array();

            foreach ($arrModules as $key => $objModule) {
								
                $arrBuffer[] = array(
                    'class' => standardize($step) . ' ' . standardize($objModule->getStepClass()),
                    'html'  => $objModule->generate()
                );
                				
                if ($objModule->hasError()) {
                    $this->doNotSubmit = true;
                }

                // the user wanted to proceed but the current step is not completed yet
                if ($this->doNotSubmit && $step != $this->strCurrentStep && !$this->isAjax) {
                    static::redirectToStep($step);
                }
            }

            if ($step == $this->strCurrentStep) {
                global $objPage;
                $objPage->pageTitle = sprintf($GLOBALS['TL_LANG']['MSC']['checkoutStep'], $intCurrentStep, $intTotalSteps, ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ?: $step)) . ($objPage->pageTitle ?: $objPage->title);
                break;
            }
        }

        $arrStepKeys = array_keys($arrSteps);

        $this->Template->steps      = $this->generateStepNavigation($arrStepKeys);
        $this->Template->activeStep = $GLOBALS['TL_LANG']['MSC']['activeStep'];

        // Hide back buttons it this is the first step
        if (array_search($this->strCurrentStep, $arrStepKeys) === 0) {
        	
            $this->Template->showPrevious = false;
        } // Show "confirm order" button if this is the last step
        elseif (array_search($this->strCurrentStep, $arrStepKeys) === (count($arrStepKeys) - 1)) {
            $this->Template->nextClass = 'confirm';
            $this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
        }
        
		/******************** CUSTOM ********************/
		//Generate login
		if($this->strCurrentStep == 'address_shipping' && $this->iso_checkout_method != 'guest'){
		    $objLogin = new \HBAgency\CheckoutStep\Login($this);
            $this->Template->login = $objLogin->generate();
		}
        // User pressed "back" button
        if (strlen(\Input::post('previousStep')) && !$this->isAjax) {
            $this->redirectToPreviousStep();
        } // Valid input data, redirect to next step
        elseif (\Input::post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit && !$this->isAjax) {
            $this->redirectToNextStep();
        }
		/******************** CUSTOM ********************/

        return $arrBuffer;
    }
	
	/**
     * Run through all steps until we find the current one or one reports failure
     * CHANGE FROM PARENT CLASS - Adding ID to each step to make it easier on Vanilla JS to find
     * @param   array
     * @return  array
     */
    protected function generateSteps(array $arrSteps)
    {    
    	$arrBuffer = $this->generateStepsParent($arrSteps);
    	
    	foreach($arrBuffer as $key => $arrSteps)
    	{
	    	$arrBuffer[$key]['id'] = str_replace('-', '_' , standardize($arrSteps['class']));
    	}
    	
    	return $arrBuffer;
    }

}