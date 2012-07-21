<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
if(!is_admin($current_user)){
	sugar_die('Admin Only');	
}

require_once('modules/Configurator/Forms.php');
echo get_module_title($mod_strings['LBL_MANAGE_ASTERISK'], $mod_strings['LBL_MANAGE_ASTERISK'].": ", true);
require_once('modules/Configurator/Configurator.php');


$asterisk_config['asterisk_host'] 	= '127.0.0.1';
$asterisk_config['asterisk_port'] 	= '5038';
$asterisk_config['asterisk_user'] = 'dialog';
$asterisk_config['asterisk_secret'] = 'dialog'; 
$asterisk_config['asterisk_prefix'] = '1';
$asterisk_config['asterisk_dialinPrefix'] = '+1';
$asterisk_config['asterisk_context'] = 'from-internal';
$asterisk_config['asterisk_expr'] = '^(sip\/[1-9][0-9][0-9]?[0-9]?-|Local)';
$asterisk_config['asterisk_soapuser'] = 'admin';
$asterisk_config['asterisk_soappass'] = 'soap_password';

$asterisk_config['asterisk_log_file'] = '';
$asterisk_config['asterisk_dialout_channel'] = 'SIP/###';
$asterisk_config['asterisk_dialin_ext_match'] = 'Local\/(?:.*?)(\d\d\d?\d?\d?)@';
$asterisk_config['asterisk_listener_poll_rate'] = '3000';
$asterisk_config['asterisk_call_subject_inbound_abbr'] = "IBC: ";
$asterisk_config['asterisk_call_subject_outbound_abbr'] = "OBC: ";
$asterisk_config['asterisk_call_subject_max_length'] = '50';
$asterisk_config['asterisk_listener_poll_rate'] = '5000';

//add asterisk vars to sugar config. need by Configurator class
global $sugar_config;
foreach ($asterisk_config as $key => $value) {
	if (!isset($sugar_config[$key])) {
		$sugar_config[$key] = '';
		$GLOBALS['sugar_config'][$key] = '';
	}
}

$configurator = new Configurator();
$focus = new Administration();

if(!empty($_POST['save'])){
	//set defaults for saving
	foreach ($asterisk_config as $key => $value) {
		if (isset($_REQUEST[$key]) && $_REQUEST[$key] == '') {	
			$_REQUEST[$key] = $value; 
		}
	}
	$configurator->saveConfig();	
	$focus->saveConfig();
	header('Location: index.php?module=Administration&action=index');
}

$focus->retrieveSettings();
if(!empty($_POST['restore'])){
	$configurator->restoreConfig();	
}

require_once('include/Sugar_Smarty.php');
$sugar_smarty = new Sugar_Smarty();


$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('APP_LIST', $app_list_strings);

$sugar_smarty->assign('config', $configurator->config);
$sugar_smarty->assign('asterisk_config', $asterisk_config);

$sugar_smarty->assign('error', $configurator->errors);

$sugar_smarty->display('custom/modules/Configurator/asterisk_configurator.tpl');

require_once("include/javascript/javascript.php");
$javascript = new javascript();
$javascript->setFormName("ConfigureSettings");
$javascript->addFieldGeneric("asterisk_host", "varchar", $mod_strings['LBL_ASTERISK_HOST'], TRUE, "");
$javascript->addFieldGeneric("asterisk_port", "int", $mod_strings['LBL_ASTERISK_PORT'], TRUE, "");
$javascript->addFieldGeneric("asterisk_user", "varchar", $mod_strings['LBL_ASTERISK_USER'], TRUE, "");
$javascript->addFieldGeneric("asterisk_secret", "varchar", $mod_strings['LBL_ASTERISK_SECRET'], TRUE, "");
$javascript->addFieldGeneric("asterisk_prefix", "varchar", $mod_strings['LBL_ASTERISK_PREFIX'], TRUE, "");
$javascript->addFieldGeneric("asterisk_context", "varchar", $mod_strings['LBL_ASTERISK_CONTEXT'], TRUE, "");
$javascript->addFieldGeneric("asterisk_expr", "varchar", $mod_strings['LBL_ASTERISK_EXPR'], TRUE, "");
$javascript->addFieldGeneric("asterisk_soapuser", "varchar", $mod_strings['LBL_ASTERISK_SOAPUSER'], TRUE, "");

// Added in yaii-2.0
$javascript->addFieldGeneric("asterisk_dialinPrefix", "varchar", $mod_strings['LBL_ASTERISK_DIALINPREFIX'], TRUE, "");
$javascript->addFieldGeneric('asterisk_log_file', "varchar", $mod_strings['LBL_ASTERISK_LOG_FILE'], TRUE, "");
$javascript->addFieldGeneric('asterisk_dialout_channel', "varchar", $mod_strings['LBL_ASTERISK_DIALOUT_CHANNEL'], TRUE, "");
$javascript->addFieldGeneric('asterisk_dialin_ext_match', "varchar", $mod_strings['LBL_ASTERISK_DIALIN_EXT_MATCH'], TRUE, "");
$javascript->addFieldGeneric('asterisk_call_subject_inbound_abbr', "varchar", $mod_strings['LBL_ASTERISK_CALL_SUBJECT_INBOUND_ABBR'], TRUE, "");
$javascript->addFieldGeneric('asterisk_call_subject_outbound_abbr', "varchar", $mod_strings['LBL_ASTERISK_CALL_SUBJECT_OUTBOUND_ABBR'], TRUE, "");
$javascript->addFieldGeneric('asterisk_call_subject_max_length', "varchar", $mod_strings['LBL_ASTERISK_CALL_SUBJECT_MAX_LENGTH'], TRUE, "");
$javascript->addFieldGeneric('asterisk_listener_poll_rate', "varchar", $mod_strings['LBL_ASTERISK_LISTENER_POLL_RATE'], TRUE, "");

// Added in yaai-2.4
$javascript->addFieldGeneric("asterisk_soappass", "varchar", $mod_strings['LBL_ASTERISK_SOAPPASS'], TRUE, "");


echo $javascript->getScript();
?>
