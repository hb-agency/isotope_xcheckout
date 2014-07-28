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


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope']['iso_xcheckout'] = 'HBAgency\Module\XCheckout';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['ajaxRequest'][] = array('\HBAgency\Hooks\AjaxRequest\LoadXCheckout', 'run');


/**
 * Re-arrange step callbacks for checkout module to make 2 full steps
 */
$GLOBALS['ISO_CHECKOUTSTEP'] = array
(
    'address_shipping'   => array( 
        'HBAgency\CheckoutStep\BillingAddress', 
        'Isotope\CheckoutStep\ShippingAddress', 
        'Isotope\CheckoutStep\ShippingMethod'
    ),
    'review_payment'   => array(
        'HBAgency\CheckoutStep\PaymentMethod', 
        'Isotope\CheckoutStep\OrderConditionsOnTop', 
        'Isotope\CheckoutStep\OrderInfo', 
        'Isotope\CheckoutStep\OrderConditionsBeforeProducts', 
        'Isotope\CheckoutStep\OrderProducts', 
        'Isotope\CheckoutStep\OrderConditionsAfterProducts'
    ),
);