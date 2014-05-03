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
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('HBAgency', 'system/modules/isotope_xcheckout/library');


/**
 * Register classes outside the namespace folder
 */
NamespaceClassLoader::addClassMap(array
(

));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	//Checkout
    'iso_checkout_login'          => 'system/modules/isotope_xcheckout/templates/checkout',
    'iso_checkout_register'       => 'system/modules/isotope_xcheckout/templates/checkout',
	'iso_checkout_payment_method' => 'system/modules/isotope_xcheckout/templates/checkout',
    
    //Modules
    'mod_iso_xcheckout'           => 'system/modules/isotope_xcheckout/templates/modules',
    
));
