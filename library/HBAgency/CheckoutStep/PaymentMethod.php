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

namespace HBAgency\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\CheckoutStep\PaymentMethod as Isotope_PaymentMethod;
use Isotope\Isotope;
use Isotope\Model\Payment;

class PaymentMethod extends Isotope_PaymentMethod implements IsotopeCheckoutStep
{
	/**
     * Generate the checkout step
     * Override parent by also generating for the payment form
     * @return  string
     */
    public function generate()
    {
        $arrModules = array();
        $arrOptions = array();
        $arrForms	= array(); //****************OVERRIDE**********************

        $arrIds = deserialize($this->objModule->iso_payment_modules);

        if (!empty($arrIds) && is_array($arrIds)) {
            $objModules = Payment::findBy(array('id IN (' . implode(',', $arrIds) . ')', (BE_USER_LOGGED_IN === true ? '' : "enabled='1'")), null, array('order' => \Database::getInstance()->findInSet('id', $arrIds)));

            if (null !== $objModules) {
                while ($objModules->next()) {

                    $objModule = $objModules->current();

                    if (!$objModule->isAvailable()) {
                        continue;
                    }

                    $strLabel = $objModule->getLabel();
                    $fltPrice = $objModule->getPrice();

                    if ($fltPrice != 0) {
                        if ($objModule->isPercentage()) {
                            $strLabel .= ' (' . $objModule->getPercentageLabel() . ')';
                        }

                        $strLabel .= ': ' . Isotope::formatPriceWithCurrency($fltPrice);
                    }

                    if ($objModule->note != '') {
                        $strLabel .= '<span class="note">' . $objModule->note . '</span>';
                    }

                    $arrOptions[] = array(
                        'value'     => $objModule->id,
                        'label'     => $strLabel,
                    );
                    
                    //****************************OVERRIDE********************************
                    if(method_exists($objModule, 'paymentForm')) {
	                    $arrForms[$objModule->id] = $objModule->paymentForm($this->objModule);
                    }
                    //****************************OVERRIDE********************************

                    $arrModules[$objModule->id] = $objModule;
                }
            }
        }

        if (empty($arrModules)) {
            $this->blnError = true;

            \System::log('No payment methods available for cart ID ' . Isotope::getCart()->id, __METHOD__, TL_ERROR);

            $objTemplate           = new \Isotope\Template('mod_message');
            $objTemplate->class    = 'payment_method';
            $objTemplate->hl       = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['payment_method'];
            $objTemplate->type     = 'error';
            $objTemplate->message  = $GLOBALS['TL_LANG']['MSC']['noPaymentModules'];

            return $objTemplate->parse();
        }

        $strClass  = $GLOBALS['TL_FFL']['radio'];
        $objWidget = new $strClass(array(
            'id'            => $this->getStepClass(),
            'name'          => $this->getStepClass(),
            'mandatory'     => true,
            'options'       => $arrOptions,
            'value'         => Isotope::getCart()->payment_id,
            'storeValues'   => true,
            'tableless'     => true,
        ));

        // If there is only one payment method, mark it as selected by default
        if (count($arrModules) == 1) {
            $objModule        = reset($arrModules);
            $objWidget->value = $objModule->id;
            Isotope::getCart()->setPaymentMethod($objModule);
        }

        if (\Input::post('FORM_SUBMIT') == $this->objModule->getFormId()) {
            $objWidget->validate();

            if (!$objWidget->hasErrors()) {
                Isotope::getCart()->setPaymentMethod($arrModules[$objWidget->value]);
            }
        }

        $objTemplate = new \Isotope\Template('iso_checkout_payment_method');

        if (!Isotope::getCart()->hasPayment() || !isset($arrModules[Isotope::getCart()->payment_id])) {
            $this->blnError = true;
        }

        $objTemplate->headline       = $GLOBALS['TL_LANG']['MSC']['payment_method'];
        $objTemplate->message        = $GLOBALS['TL_LANG']['MSC']['payment_method_message'];
        $objTemplate->options        = $objWidget->parse();
        $objTemplate->paymentMethods = $arrModules;
        $objTemplate->paymentForms 	 = $arrForms; //****************OVERRIDE**********************
        
        return $objTemplate->parse();
    }
}