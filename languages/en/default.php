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
 * Frontend Modules
 */
$GLOBALS['TL_LANG']['FMD']['iso_xcheckout']	= array('XCheckout', 'Allow store customers to complete their transactions in one step.');

/**
 * Checkout Steps
 */
$GLOBALS['TL_LANG']['ISO']['checkout_address_shipping']	= 'Address & Shipping';
$GLOBALS['TL_LANG']['ISO']['checkout_review_payment'] = 'Review & Payment';

/**
 * Checkout Step Labels
 */
$GLOBALS['TL_LANG']['ISO']['login']	= 'Login';

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['noPaymentModules'] = 'No payment options are currently available. Please select a shipping method to choose from available payment methods.';
$GLOBALS['TL_LANG']['MSC']['noShippingModules'] = 'No shipping options are currently available. Shipping methods will be available once you have entered your shipping address.';

$GLOBALS['TL_LANG']['MSC']['loginMessage'] = 'Login to your account in order to complete your purchase.';
$GLOBALS['TL_LANG']['MSC']['registerMessage'] = 'Create a password to access your account information for future purchases. (optional)';
$GLOBALS['TL_LANG']['MSC']['bothMessage'] = 'Login to your account or checkout as a guest. You can choose to register for a new account below.';


$GLOBALS['TL_LANG']['ERR']['passwordUnique'] = 'Your password cannot be the same as your username/email address. Please select another password.';
$GLOBALS['TL_LANG']['ERR']['emailUnique'] = 'Your email address is already associated with an account on our system.';

?>