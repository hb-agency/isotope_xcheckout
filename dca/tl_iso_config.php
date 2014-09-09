<?php

/**
 * Copyright (C) 2014 HB Agency
 * 
 * @author		Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @link		http://www.hbagency.com
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['__selector__'][] = 'createMember';
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default'] .= ';{createMember_legend:hide},createMember';
$GLOBALS['TL_DCA']['tl_iso_config']['subpalettes']['createMember_always'] = 'createMember_groups';
$GLOBALS['TL_DCA']['tl_iso_config']['subpalettes']['createMember_product'] = 'createMember_groups';
$GLOBALS['TL_DCA']['tl_iso_config']['subpalettes']['createMember_guest'] = 'createMember_groups';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['createMember'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_config']['createMember'],
	'exclude'		=> true,
	'inputType'		=> 'radio',
	'options'		=> array('always', 'product', 'guest'),
	'reference'		=> &$GLOBALS['TL_LANG']['tl_iso_config']['createMember'],
	'eval'			=> array('submitOnChange'=>true, 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_iso_config']['createMember']['never'], 'tl_class'=>'clr'),
	'sql'			=> "varchar(8) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['createMember_groups'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_config']['createMember_groups'],
	'inputType'		=> 'checkboxWizard',
	'foreignKey'	=> 'tl_member_group.name',
	'eval'			=> array('multiple'=>true, 'tl_class'=>'clr'),
	'sql'			=> "blob NULL"
);

