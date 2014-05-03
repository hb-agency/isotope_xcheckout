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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\CheckoutStep\CheckoutStep;
use Isotope\Isotope;

class Login extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true to enable the module
     * @return  bool
     */
    public function isAvailable()
    {
        if($this->objModule->iso_checkout_method == 'guest')
        {
            return false;
        }
        
        return FE_USER_LOGGED_IN ? false: true;
    }
    
    /**
     * Generate the checkout step
     * Override parent by also generating for the payment form
     * @return  string
     */
    public function generate()
    {
        $strLogin = $this->getFrontendModule($this->objModule->iso_loginModule);
        
        if($strLogin === '')
        {
            return '';
        }
        
        $objTemplate = new \Isotope\Template('iso_checkout_login');
        $objTemplate->login = $strLogin;
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['login'];
        $objTemplate->message = $this->objModule->iso_checkout_method == 'member' ? $GLOBALS['TL_LANG']['MSC']['loginMessage'] : $GLOBALS['TL_LANG']['MSC']['bothMessage'];
        return $objTemplate->parse();
    }
    
    /**
     * Get review information about this step
     * @return  array
     */
    public function review()
    {
        return '';
    }
    
    /**
     * Return array of tokens for notification
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        return array();
    }

}