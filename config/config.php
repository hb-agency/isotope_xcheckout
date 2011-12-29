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


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope']['iso_xcheckout'] = 'ModuleIsotopeXCheckout';


/**
 * Re-arrange step callbacks for checkout module to make 2 full steps
 */
$GLOBALS['ISO_CHECKOUT_STEPS'] = array
(
	'address_shipping' => array
	(
		array('ModuleIsotopeCheckout', 'getBillingAddressInterface'),
		array('ModuleIsotopeCheckout', 'getShippingAddressInterface'),
		array('ModuleIsotopeCheckout', 'getShippingModulesInterface'),
	),
	'review_payment' => array
	(
		array('ModuleIsotopeCheckout', 'getOrderReviewInterface'),
		array('ModuleIsotopeCheckout', 'getOrderConditionsInterface'),
		array('ModuleIsotopeCheckout', 'getPaymentModulesInterface'),
	)
);


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['iso_writeOrder'][] = array('IsotopeXCheckout', 'triggerAction');	// Isotope 0.2
$GLOBALS['ISO_HOOKS']['preCheckout'][] = array('IsotopeXCheckout', 'triggerAction');	// Isotope 1.3+
 
 
?>