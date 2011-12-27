<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009-2011
 * @author     Blair Winans <blair@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */



class ModuleIsotopeXCheckout extends ModuleIsotopeCheckout
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_xcheckout';


	/**
	 * Form ID
	 * @var string
	 */
	protected $strFormId = 'iso_mod_checkout_payment';
	
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
			$objTemplate = new BackendTemplate('be_wildcard');

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
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope_xcheckout/html/xcheckout.js'; 
	
		// Order has been completed (postsale request)
		if ($this->strCurrentStep == 'complete' && $this->Input->get('uid') != '')
		{
			$objOrder = new IsotopeOrder();

			if ($objOrder->findBy('uniqid', $this->Input->get('uid')) && $objOrder->checkout_complete)
			{
				$this->redirect($this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->orderCompleteJumpTo)->fetchAssoc()) . '?uid='.$objOrder->uniqid);
			}
		}

		// Return error message if cart is empty
		if (!$this->Isotope->Cart->items)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
			return;
		}

		// Insufficient cart subtotal
		if ($this->Isotope->Config->cartMinSubtotal > 0 && $this->Isotope->Config->cartMinSubtotal > $this->Isotope->Cart->subTotal)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'error';
			$this->Template->message = sprintf($GLOBALS['TL_LANG']['ERR']['cartMinSubtotal'], $this->Isotope->formatPriceWithCurrency($this->Isotope->Config->cartMinSubtotal));
			return;
		}

		// Redirect to login page if not logged in
		if ($this->iso_checkout_method == 'member' && !FE_USER_LOGGED_IN)
		{
			$objPage = $this->Database->prepare("SELECT id,alias FROM tl_page WHERE id=?")->limit(1)->execute($this->iso_login_jumpTo);

			if (!$objPage->numRows)
			{
				$this->Template = new FrontendTemplate('mod_message');
				$this->Template->type = 'error';
				$this->Template->message = $GLOBALS['TL_LANG']['ERR']['isoLoginRequired'];
				return;
			}

			$this->redirect($this->generateFrontendUrl($objPage->row()));
		}
		elseif ($this->iso_checkout_method == 'guest' && FE_USER_LOGGED_IN)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'error';
			$this->Template->message = 'User checkout not allowed';
			return;
		}

		if (!$this->iso_forward_review && !strlen($this->Input->get('step')))
		{
			$this->redirectToNextStep();
		}

		// Default template settings. Must be set at beginning so they can be overwritten later (eg. trough callback)
		$this->Template->login = $this->getLoginInterface();
		$this->Template->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$this->Template->formId = $this->strFormId;
		$this->Template->formSubmit = $this->strFormId;
		$this->Template->enctype = 'application/x-www-form-urlencoded';
		$this->Template->previousLabel = specialchars($GLOBALS['TL_LANG']['MSC']['previousStep']);
		$this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['nextStep']);
		$this->Template->nextClass = 'next';
		$this->Template->showPrevious = true;
		$this->Template->showNext = true;
		$this->Template->showForm = true;
		
		// Remove shipping step if no items are shipped
		if (!$this->Isotope->Cart->requiresShipping)
		{
			unset($GLOBALS['ISO_CHECKOUT_STEPS']['address_shipping'][2]);

			// Remove payment step if items are free of charge. We need to do this here because shipping might have a price.
			if (!$this->Isotope->Cart->requiresPayment)
			{
				unset($GLOBALS['ISO_CHECKOUT_STEPS']['review_payment'][1]);
			}
		}

		if ($this->strCurrentStep == 'failed')
		{
			$this->Database->prepare("UPDATE tl_iso_orders SET status='on_hold' WHERE cart_id=?")->execute($this->Isotope->Cart->id);
			$this->Template->mtype = 'error';
			$this->Template->message = strlen($this->Input->get('reason')) ? $this->Input->get('reason') : $GLOBALS['TL_LANG']['ERR']['orderFailed'];
			$this->strCurrentStep = 'review_payment';
		}

		// Run trough all steps until we find the current one or one reports failure
		foreach ($GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks)
		{
			// Step could be removed while looping
			if (!isset($GLOBALS['ISO_CHECKOUT_STEPS'][$step]))
			{
				continue;
			}

			$this->strFormId = 'iso_mod_checkout_' . $step;
			$this->Template->formId = $this->strFormId;
			$this->Template->formSubmit = $this->strFormId;
			$strBuffer = '';

			foreach ($arrCallbacks as $callback)
			{
				if ($callback[0] == 'ModuleIsotopeCheckout' || $callback[0] == 'ModuleIsotopeXCheckout')
				{
					$strBuffer .= $this->{$callback[1]}();
				}
				else
				{
					$this->import($callback[0]);
					$strBuffer .= $this->{$callback[0]}->{$callback[1]}($this);
				}

				// the user wanted to proceed but the current step is not completed yet
				if ($this->doNotSubmit && $step != $this->strCurrentStep)
				{
					$this->redirect($this->addToUrl('step=' . $step, true));
				}
			}

			if ($step == $this->strCurrentStep)
			{
				break;
			}
		}

		if ($this->strCurrentStep == 'process')
		{
			$this->writeOrder();
			$strBuffer = $this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->checkoutForm() : false;

			if ($strBuffer === false)
			{
				$this->redirect($this->addToUrl('step=complete', true));
			}

			$this->Template->showForm = false;
			$this->doNotSubmit = true;
		}

		if ($this->strCurrentStep == 'complete')
		{
			$strBuffer = $this->Isotope->Cart->hasPayment ? $this->Isotope->Cart->Payment->processPayment() : true;

			if ($strBuffer === true)
			{
				unset($_SESSION['FORM_DATA']);
				unset($_SESSION['FILES']);

				$objOrder = new IsotopeOrder();

				if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id) || !$objOrder->checkout($this->Isotope->Cart))
				{
					$this->redirect($this->addToUrl('step=failed', true));
				}

				unset($_SESSION['CHECKOUT_DATA']);
				unset($_SESSION['ISOTOPE']);

				$this->redirect($this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->orderCompleteJumpTo)->fetchAssoc()) . '?uid='.$objOrder->uniqid);
			}
			elseif ($strBuffer === false)
			{
				$this->redirect($this->addToUrl('step=failed', true));
			}
			else
			{
				$this->Template->showNext = false;
				$this->Template->showPrevious = false;
			}
		}

		$this->Template->fields = $strBuffer;

		if (!strlen($this->strCurrentStep))
		{
			$this->strCurrentStep = $step;
		}

		// Show checkout steps
		$arrStepKeys = array_keys($GLOBALS['ISO_CHECKOUT_STEPS']);
		$blnPassed = true;
		$total = count($arrStepKeys) - 1;
		$arrSteps = array();
		
		if ($this->strCurrentStep != 'process' && $this->strCurrentStep != 'complete')
		{
			foreach ($arrStepKeys as $i => $step)
			{
				if ($this->strCurrentStep == $step)
				{
					$blnPassed = false;
				}
	
				$blnActive = $this->strCurrentStep == $step ? true : false;
	
				$arrSteps[] = array
				(
					'isActive'	=> $blnActive,
					'class'		=> 'step_' . $i . (($i == 0) ? ' first' : '') . ($i == $total ? ' last' : '') . ($blnActive ? ' active' : '') . ($blnPassed ? ' passed' : '') . ((!$blnPassed && !$blnActive) ? ' upcoming' : '') . ' '. $step,
					'label'		=> (strlen($GLOBALS['TL_LANG']['ISO']['checkout_' . $step]) ? $GLOBALS['TL_LANG']['ISO']['checkout_' . $step] : $step),
					'href'		=> ($blnPassed ? $this->addToUrl('step=' . $step, true) : ''),
					'title'		=> specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['checkboutStepBack'], (strlen($GLOBALS['TL_LANG']['ISO']['checkout_' . $step]) ? $GLOBALS['TL_LANG']['ISO']['checkout_' . $step] : $step))),
				);
			}
		}
		
		$this->Template->steps = $arrSteps;
		$this->Template->activeStep = $GLOBALS['ISO_LANG']['MSC']['activeStep'];

		// Hide back buttons it this is the first step
		if (array_search($this->strCurrentStep, $arrStepKeys) === 0)
		{
			$this->Template->showPrevious = false;
		}

		// Show "confirm order" button if this is the last step
		elseif (array_search($this->strCurrentStep, $arrStepKeys) === $total)
		{
			$this->Template->nextClass = 'confirm';
			$this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
		}

		// User pressed "back" button
		if (strlen($this->Input->post('previousStep')))
		{
			$this->redirectToPreviousStep();
		}

		// Valid input data, redirect to next step
		elseif ($this->Input->post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit && !$this->isAjax)
		{
			$this->redirectToNextStep();
		}
		
		list(,$startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();
		
		global $objPage;
		
		$GLOBALS['TL_MOOTOOLS'][] = $startScript."\nwindow.addEvent('domready', function(event) { new IsotopeXcheckout('" . $this->id . "', '". $this->strCurrentStep ."', {params: '".$strParams."', language: '" . $GLOBALS['TL_LANGUAGE'] . "', page: '" . $objPage->id . "', mode: '".(TL_MODE=='FE' ? 'FE' : 'BE')."'});});\n".$endScript;
		
	}
	
	
	/**
	 * AJAX callback
	 * return content based on user input
	 */
	public function generateAjax()
	{	
		//Generate the methods
		$this->isAjax = true;
		$this->generate();
			
		return array(
			array('type'=>'shipping_method', 'html' => $this->getShippingModulesInterface()),
			array('type'=>'payment_method', 'html' => $this->getPaymentModulesInterface())
		);
	}


	/**
	 * Return login step interface
	 * Nothing to return on review stage
	 * @param boolean
	 * @return string
	 */
	public function getLoginInterface()
	{				
		global $objPage;
			
		if(!$this->iso_loginModule || $this->iso_checkout_method == 'guest')
		{	
			return;
		}
		
		$objTemplate = new IsotopeTemplate('iso_checkout_login');
		
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['login'];
		$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['loginMessage'];
		$objTemplate->login = $this->getFrontendModule($this->iso_loginModule);
		
		if($this->iso_checkout_method == 'both')
		{
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['bothMessage'];
		}
		
		return $objTemplate->parse();
	}


	/**
	 * Generate the current step widgets.
	 * Overwrite the parent method to add in a username/password entry on billing address step
	 *
	 * @param string
	 * @param integer
	 * @return string
	 */
	protected function generateAddressWidgets($strAddressType, $intOptions)
	{
		//Generate default widgets
		$strBuffer = parent::generateAddressWidgets($strAddressType, $intOptions);
		
		//Only add to billing
		if($strAddressType=='billing_address' && $this->iso_checkout_method == 'both' && !FE_USER_LOGGED_IN)
		{			
			$objTemplate = new IsotopeTemplate('iso_checkout_register');
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['registerMessage'];
						
			//Construct password widget
			$arrData = array
			(
				'label'	=> &$GLOBALS['TL_LANG']['MSC']['password'],
				'eval'	=> array('mandatory'=>true, 'rgxp'=>'extnd', 'minlength'=>$GLOBALS['TL_CONFIG']['minPasswordLength']),
			);
			
			$objWidget = new FormPassword($this->prepareForWidget($arrData, 'password', $_SESSION['CHECKOUT_DATA']['billing_address']['password']));
			
			$objWidget->tableless = $this->tableless;
			$objWidget->rowClass = 'row_0 row_first';
			$objWidget->rowClassConfirm = 'row_1 row_last';	
				
			//Process the input
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && strlen($this->Input->post('password')))
			{
				$objWidget->validate();
				$varValue = $objWidget->value;

				// Check whether the password matches the username
				if ($this->Input->post('password') == $this->Input->post('billing_address_email'))
				{
					$objWidget->addError($GLOBALS['TL_LANG']['ERR']['passwordUnique']);
				}
				
				//Check whether the username/email exists
				$objUnique = $this->Database->prepare("SELECT * FROM tl_member WHERE email=?")
												->limit(1)
												->execute($this->Input->post('billing_address_email'));

				if ($objUnique->numRows)
				{
					$objWidget->addError($GLOBALS['TL_LANG']['ERR']['emailUnique']);
					
					//@todo - Create link to "forgot password" page - add to module config
					
				}
				
				if ($objWidget->hasErrors())
				{
					$this->doNotSubmit = true;
				}
				else
				{
					$_SESSION['CHECKOUT_DATA']['billing_address']['password'] = $this->Input->post('password');
				}
				
			}
						
			$strFields .= $this->tableless ? $objPassWidget->parse() : '<table>' . "\n" . $objWidget->parse() . '</table>';
			
			$objTemplate->fields = $strFields;
			
			$strBuffer .=  $objTemplate->parse();
		}
		
		return $strBuffer;
	}



}