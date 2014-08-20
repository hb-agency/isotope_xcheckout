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
