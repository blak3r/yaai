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
if(!is_admin($current_user) && !defined("configurator_util")){
	sugar_die('Admin Only');	
}

require_once('modules/Configurator/Forms.php');
echo get_module_title($mod_strings['LBL_MANAGE_ASTERISK'], $mod_strings['LBL_MANAGE_ASTERISK'].": ", true);
require_once('modules/Configurator/Configurator.php');
require_once('asterisk_config_meta.php');

//Need configurable Channel detection in order to assign calls to users when they answer on cell phones.

//add asterisk vars to sugar config. need by Configurator class
global $sugar_config;
foreach ($config_meta as $key => $value) {
	if (!isset($sugar_config[$key])) {
		$sugar_config[$key] = '';
		$GLOBALS['sugar_config'][$key] = '';
	}
}

$configurator = new Configurator();
$focus = new Administration();

if(!empty($_POST['save'])){
	//set defaults for saving

    foreach ($config_meta as $key => $value) {
	   // BR Modified so now if empty params come in they aren't required.
        if (isset($_REQUEST[$key]) && $_REQUEST[$key] == '') {
            if( isset($value['required']) && $value['required'] != "true") {
				$_REQUEST[$key] = $value['default'];
            }
            else {
                $_REQUEST[$key] = '';
            }
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

// Build the $asterisk_config array which stores all the default values used in smart template below if
// they aren't already set
$asterisk_config = array();
foreach ($config_meta as $key => $value) {
    $asterisk_config[$key] = $value['default'];
}

try {
    $statResult = $GLOBALS['db']->query("select count(*) as CallsLogged from calls_cstm where calls_cstm.asterisk_caller_id_c is not NULL");
    $statRow = $GLOBALS['db']->fetchByAssoc($statResult);
    $callsLogged = $statRow['CallsLogged'];
}
catch(Exception $ex) {
    $callsLogged = "Unknown";
}

$licenseSummary = '';
require_once('include/Sugar_Smarty.php');
$sugar_smarty = new Sugar_Smarty();

$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('APP_LIST', $app_list_strings);

$sugar_smarty->assign('config', $configurator->config);
$sugar_smarty->assign('asterisk_config', $asterisk_config);
$sugar_smarty->assign('callsLogged', $callsLogged);
$sugar_smarty->assign('licenseSummary', $licenseSummary);

$sugar_smarty->assign('error', $configurator->errors);

$sugar_smarty->display('custom/modules/Configurator/asterisk_configurator.tpl');

require_once("include/javascript/javascript.php");
$javascript = new javascript();
$javascript->setFormName("ConfigureSettings");

foreach ($config_meta as $key => $value) {
    $type = "varchar";
    $required = TRUE;

    if( isset($value['required'])) {
        $required = $value['required'];
    }
    if( isset($value['type'])) {
        $type = $value['type'];
    }

    $javascript->addFieldGeneric($key, $type, $mod_strings['LBL_' . strtoupper($key)], $required, "");
}

echo $javascript->getScript();

print <<<ENDJS

ENDJS;


?>
