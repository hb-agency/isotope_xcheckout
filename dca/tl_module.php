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


$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckout']				= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,nc_notification;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},iso_collectionTpl,iso_orderCollectionBy,iso_gallery,tableless,iso_includeMessages,iso_order_conditions,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckoutmember']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_loginModule,iso_addToAddressbook,nc_notification;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_login_jumpTo,iso_cart_jumpTo;{template_legend},iso_collectionTpl,iso_orderCollectionBy,iso_gallery,tableless,iso_includeMessages,iso_order_conditions,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckoutguest']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,nc_notification;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_cart_jumpTo;{template_legend},iso_collectionTpl,iso_orderCollectionBy,iso_gallery,tableless,iso_includeMessages,iso_order_conditions,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_xcheckoutboth']			= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_loginModule,iso_addToAddressbook,nc_notification;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_login_jumpTo,iso_cart_jumpTo;{template_legend},iso_collectionTpl,iso_orderCollectionBy,iso_gallery,tableless,iso_includeMessages,iso_order_conditions,iso_order_conditions_position;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


$GLOBALS['TL_DCA']['tl_module']['fields']['iso_loginModule'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_loginModule'],
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope_xcheckout', 'getLoginModules'),
	'eval'						=> array('mandatory'=>true),
	'sql'                       => "int(10) NOT NULL default '0'"
);


class tl_module_isotope_xcheckout extends Backend
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