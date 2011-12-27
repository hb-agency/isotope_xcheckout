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

$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckout']				= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_mail_customer,iso_mail_admin,iso_sales_email,iso_includeMessages,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckoutmember']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_loginModule;{template_legend},iso_mail_customer,iso_mail_admin,iso_sales_email,iso_includeMessages,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckoutguest']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_mail_customer,iso_mail_admin,iso_sales_email,iso_includeMessages,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckoutboth']			= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_loginModule,iso_mail_customer,iso_mail_admin,iso_sales_email,iso_includeMessages,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


$GLOBALS['TL_DCA']['tl_module']['fields']['iso_loginModule'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_loginModule'],
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope_xcheckout', 'getLoginModules'),
	'eval'						=> array('mandatory'=>true)
);


class tl_module_isotope_xcheckout extends tl_module_isotope
{

	/**
	 * Returns a list of login modules.
	 *
	 * @access public
	 * @return array
	 */
	public function getLoginModules(DataContainer $dc)
	{
		$arrLoginModules = array();
		$objLoginModules = $this->Database->execute("SELECT * FROM tl_module WHERE type='login' AND pid={$dc->activeRecord->pid}");
		
		while( $objLoginModules->next() )
		{
			$arrLoginModules[$objLoginModules->id] = $objLoginModules->name;
		}
		
		return $arrLoginModules;
	}

}