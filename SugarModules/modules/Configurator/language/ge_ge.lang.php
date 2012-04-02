<?php

/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 * (c) abcona e. K. 2009
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * http://www.sugarforge.org/projects/yaai/
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact KINAMU Business Solutions AG at office@kinamu.com
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 */


//$mod_strings = array (
//  'LBL_MANAGE_ASTERISK' => 'ASTERISK Konfiguration',
//  'LBL_ASTERISK_HOST' => 'Host',
//  'LBL_ASTERISK_PORT' => 'Port',
//  'LBL_ASTERISK_USER' => 'Benutzer',
//  'LBL_ASTERISK_SECRET' => 'Password',
//  'LBL_ASTERISK_PREFIX' => 'Dialout Prefix',
//  'LBL_ASTERISK_CONTEXT' => 'Dial Context',
//  'LBL_ASTERISK_EXPR' => 'Regul&auml;rer-Ausdruck um intern nach extern zu bestimmen',
//  'LBL_ASTERISK_EXPR_DESC' => 'Regul&auml;re Ausdr&uuml;cke siehe PHP Doku',
//  'LBL_ASTERISK_SOAPUSER' => 'Asterisk Soap-Benutzer',
//);

if (!isset($mod_strings)) { $mod_strings = array(); }

$mod_strings['LBL_MANAGE_ASTERISK'] = 'ASTERISK Konfiguration';
$mod_strings['LBL_ASTERISK_HOST'] = 'Asterisk Manager Host';
$mod_strings['LBL_ASTERISK_PORT'] = 'Asterisk Manager Port';
$mod_strings['LBL_ASTERISK_USER'] = 'Asterisk Manager Login';
$mod_strings['LBL_ASTERISK_SECRET'] = 'Asterisk Manager Passwort';
$mod_strings['LBL_ASTERISK_PREFIX'] = 'Vorwahl für ausgehende Gespr&auml;che';
$mod_strings['LBL_ASTERISK_CONTEXT'] = 'Asterisk Kontext f&uuml;r ausgehende Gespr&auml;che';
$mod_strings['LBL_ASTERISK_EXPR'] = 'Regul&auml;rer Ausdruck f&uuml;r eingehende Gespr&auml;che';
$mod_strings['LBL_ASTERISK_EXPR_DESC'] = 'Regul&auml;rer Ausdruck um eingehende von ausgehenden Anrufen zu unterscheiden';
$mod_strings['LBL_ASTERISK_SOAPUSER'] = 'Asterisk Soap-User';

?>