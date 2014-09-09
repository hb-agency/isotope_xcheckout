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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_config']['createMember']				= array('Create member', 'Create a member account after a guest checkout.');
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_groups']			= array('Groups', 'Select the groups a new member is assigned to.');
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_newsletters']	= array('Newsletters', 'Assign new members to these newsletters.');
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_mail']			= array('Customer notification', 'Select the mail template for customer notification.');
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_adminMail']		= array('Admin notification', 'Select the mail template for admin notification.');
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_assignDir']		= array('Set home directory', 'Define a home directory for the member.');
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_homeDir']		= array('Home directory', 'Please select a folder from the files directory.');
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_expiration']		= array('Set member lifetime', 'Enter a format to set the member stop date. See <a href="http://php.net/strtotime" target="_blank">php.net/strtotime</a> for a reference. This will set the member "stop" date.');



/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_config']['createMember_legend']		= 'Create Member';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_config']['createMember']['never']	= 'Never';
$GLOBALS['TL_LANG']['tl_iso_config']['createMember']['always']	= 'Always';
$GLOBALS['TL_LANG']['tl_iso_config']['createMember']['product']	= 'Defined by product';
$GLOBALS['TL_LANG']['tl_iso_config']['createMember']['guest']	= 'Checkout option for guests';

