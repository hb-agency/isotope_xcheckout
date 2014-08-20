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
 * Table tl_iso_addresses
 */
$GLOBALS['TL_DCA']['tl_iso_addresses']['palettes']['default'] = '{store_legend},label,store_id;{personal_legend},salutation,firstname,lastname;{address_legend},company,street_1,street_2,street_3,postal,city,subdivision,country;{contact_legend},email,phone;{default_legend:hide},isDefaultBilling,isDefaultShipping';

// Fields
$GLOBALS['TL_DCA']['tl_iso_addresses']['fields']['memberdiscount'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_addresses']['memberdiscount'],
	'exclude'				=> true,
	'search'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array('feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
	'sql'                   => "char(1) NOT NULL default ''"
);
