<?php 
/**
 * Asterisk SugarCRM Integration 
 * (c) KINAMU Business Solutions AG 2009
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

$admin_option_defs=array();
$admin_option_defs['Administration']['asterisk_configurator']= array('Administration','LBL_MANAGE_ASTERISK','LBL_ASTERISK','./index.php?module=Configurator&action=asterisk_configurator');
$admin_option_defs['Administration']['asterisk_donate']= array('Opportunities','LBL_ASTERISK_DONATE','LBL_ASTERISK_DONATE_DESC','http://www.blakerobertson.com/yaai-donation-page');
$admin_option_defs['Administration']['asterisk_usermanual']= array('Support','LBL_ASTERISK_USERMANUAL','LBL_ASTERISK_USERMANUAL_DESC','https://github.com/blak3r/yaai/wiki/User-Manual');
$admin_option_defs['Administration']['asterisk_mailinglist']= array('Emails','LBL_ASTERISK_MAILINGLIST','LBL_ASTERISK_MAILINGLIST_DESC','http://eepurl.com/rmdML');

$admin_group_header[]=array('LBL_ASTERISK_TITLE','',false,$admin_option_defs, 'LBL_ASTERISK_DESC');


?>