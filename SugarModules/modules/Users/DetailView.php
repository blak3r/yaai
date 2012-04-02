<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/

/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/


require_once('include/DetailView/DetailView.php');
require_once('include/export_utils.php');
require_once('include/timezone/timezones.php');



global $current_user;
global $theme;
global $app_strings;
global $mod_strings;
global $timezones;
if (!is_admin($current_user) && !is_admin_for_module($GLOBALS['current_user'],'Users')
      && ($_REQUEST['record'] != $current_user->id)) sugar_die("Unauthorized access to administration.");

$focus = new User();

$detailView = new DetailView();
$offset=0;
if (isset($_REQUEST['offset']) || !empty($_REQUEST['record'])) {
	$result = $detailView->processSugarBean("USER", $focus, $offset);
	
	if($result == null) {
	    sugar_die($app_strings['ERROR_NO_RECORD']);
	}
	$focus=$result;
} else {
	header("Location: index.php?module=Users&action=index");
}

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$focus->id = "";
}
if(isset($_REQUEST['reset_preferences'])){
	$focus->resetPreferences();
}
if(isset($_REQUEST['reset_homepage'])){
    $focus->resetPreferences('Home');
    if($focus->id == $current_user->id) {
        $_COOKIE[$current_user->id . '_activePage'] = '0';
        setcookie($current_user->id . '_activePage','0',3000);
    }
}

$params = array();
$params[] = "<span class='pointer'>&raquo;</span>".$locale->getLocaleFormattedName($focus->first_name,$focus->last_name);
echo getClassicModuleTitle("Users", $params, true);

global $app_list_strings;

$GLOBALS['log']->info("User detail view");

$sugar_smarty = new Sugar_Smarty();
$sugar_smarty->assign("MOD", $mod_strings);
$sugar_smarty->assign("APP", $app_strings);
$sugar_smarty->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$sugar_smarty->assign("ID", $focus->id);
$sugar_smarty->assign("USER_NAME", $focus->user_name);
$sugar_smarty->assign("FULL_NAME", $focus->full_name);
if(!empty($GLOBALS['sugar_config']['authenticationClass'])){
		$authclass =  $GLOBALS['sugar_config']['authenticationClass'];
}else if(!empty($GLOBALS['system_config']->settings['system_ldap_enabled'])){
		$authclass =  'LDAPAuthenticate';
}
if(is_admin($GLOBALS['current_user']) && !empty($authclass)){
$str = '<tr><td valign="top" scope="row">';
$str .= $authclass . ':';
$str .= '</td><td><input type="checkbox" disabled ';
if(!empty($focus->external_auth_only))$str .= ' CHECKED ';
$str .='/></td><td>'.  $mod_strings['LBL_EXTERNAL_AUTH_ONLY'] . ' ' . $authclass. '</td></tr>';
$sugar_smarty->assign('EXTERNAL_AUTH', $str);
}

$edit_self = $current_user->id == $focus->id;
if($edit_self) {
	$sugar_smarty->assign('EDIT_SELF','1');
}

///////////////////////////////////////////////////////////////////////////////
////	TO SUPPORT LEGACY XTEMPLATES
$sugar_smarty->assign('FIRST_NAME', $focus->first_name);
$sugar_smarty->assign('LAST_NAME', $focus->last_name);
////	END SUPPORT LEGACY XTEMPLATES
///////////////////////////////////////////////////////////////////////////////

$status = '';
if(!empty($focus->status)) {
  // jc:#12261 - while not apparent, replaced the explicit reference to the
  // app_strings['user_status_dom'] element with a call to the ultility translate
  // function to retrieved the mapped value for User::status
  $status = translate('user_status_dom', '', $focus->status);
}
$sugar_smarty->assign("STATUS", $status);
$detailView->processListNavigation($sugar_smarty, "USER", $offset);
$reminder_time = $focus->getPreference('reminder_time');

if(empty($reminder_time)){
	$reminder_time = -1;
}
if($reminder_time != -1){
	$sugar_smarty->assign("REMINDER_CHECKED", 'checked');
	$sugar_smarty->assign("REMINDER_TIME", translate('reminder_time_options', '', $reminder_time));
}
// Display the good usertype
$user_type_label=$mod_strings['LBL_REGULAR_USER'];
$usertype='RegularUser';

if((is_admin($current_user) || $_REQUEST['record'] == $current_user->id || is_admin_for_module($current_user,'Users')) && $focus->is_admin == '1'){
	$user_type_label=$mod_strings['LBL_ADMIN_USER'];
	$usertype='Administrator';
}


$sugar_smarty->assign('IS_GROUP_OR_PORTAL','0');
if(!empty($focus->is_group) && $focus->is_group == 1){
	$user_type_label=$mod_strings['LBL_GROUP_USER'];
	$usertype='GroupUser';
    $sugar_smarty->assign('IS_GROUP_OR_PORTAL','1');
}

$sugar_smarty->assign("USER_TYPE", $usertype);
$sugar_smarty->assign("USER_TYPE_LABEL", $user_type_label);





// adding custom fields:
$xtpl = $sugar_smarty;
require_once('modules/DynamicFields/templates/Files/DetailView.php');
$errors = "";
$msgGood = false;
if (isset($_REQUEST['pwd_set']) && $_REQUEST['pwd_set']!= 0){
	if ($_REQUEST['pwd_set']=='4'){
		require_once('modules/Users/password_utils.php');
		$errors.=canSendPassword();
	}
	else {
		$errors.=$mod_strings['LBL_NEW_USER_PASSWORD_'.$_REQUEST['pwd_set']];	
		$msgGood = true;
	}
}else{
	//IF FOCUS USER IS LOCKOUT
	if($focus->getPreference('lockout')=='1')
		$errors.=$mod_strings['ERR_USER_IS_LOCKED_OUT'];
}
$sugar_smarty->assign("ERRORS", $errors);
$sugar_smarty->assign("ERROR_MESSAGE", $msgGood ? $mod_strings['LBL_PASSWORD_SENT'] : $mod_strings['LBL_CANNOT_SEND_PASSWORD']);
$buttons = "";
if ((is_admin($current_user) || $_REQUEST['record'] == $current_user->id
     )
        && !empty($sugar_config['default_user_name'])
		&& $sugar_config['default_user_name'] == $focus->user_name
		&& isset($sugar_config['lock_default_user_name'])
		&& $sugar_config['lock_default_user_name']) {
	$buttons .= "<input id='edit_button' title='".$app_strings['LBL_EDIT_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_EDIT_BUTTON_KEY']."' class='button primary' onclick=\"this.form.return_module.value='Users'; this.form.return_action.value='DetailView'; this.form.return_id.value='$focus->id'; this.form.action.value='EditView'\" type='submit' name='Edit' value='".$app_strings['LBL_EDIT_BUTTON_LABEL']."'>  ";
}
elseif (is_admin($current_user)|| (is_admin_for_module($GLOBALS['current_user'],'Users')&& !$focus->is_admin)
     || $_REQUEST['record'] == $current_user->id) {
	$buttons .= "<input id='edit_button' title='".$app_strings['LBL_EDIT_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_EDIT_BUTTON_KEY']."' class='button primary' onclick=\"this.form.return_module.value='Users'; this.form.return_action.value='DetailView'; this.form.return_id.value='$focus->id'; this.form.action.value='EditView'\" type='submit' name='Edit' value='".$app_strings['LBL_EDIT_BUTTON_LABEL']."'>  ";
	if ((is_admin($current_user)|| is_admin_for_module($GLOBALS['current_user'],'Users')
        )
	){
		if (!$current_user->is_group){
			$buttons .= "<input title='".$app_strings['LBL_DUPLICATE_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_DUPLICATE_BUTTON_KEY']."' class='button' onclick=\"this.form.return_module.value='Users'; this.form.return_action.value='DetailView'; this.form.isDuplicate.value=true; this.form.action.value='EditView'\" type='submit' name='Duplicate' value='".$app_strings['LBL_DUPLICATE_BUTTON_LABEL']."'>  ";

                if($focus->id != $current_user->id) {
                    $buttons .="<input type='button' class='button' onclick='confirmDelete();' value='".$app_strings['LBL_DELETE_BUTTON_LABEL']."' /> ";
                }

			if (!$focus->portal_only && !$focus->is_group && !$focus->external_auth_only 
			&& isset($sugar_config['passwordsetting']['SystemGeneratedPasswordON']) && $sugar_config['passwordsetting']['SystemGeneratedPasswordON']){
				$buttons .= "<input title='".$mod_strings['LBL_GENERATE_PASSWORD_BUTTON_TITLE']."' accessKey='".$mod_strings['LBL_GENERATE_PASSWORD_BUTTON_KEY']."' class='button' LANGUAGE=javascript onclick='generatepwd(\"".$focus->id."\");' type='button' name='password' value='".$mod_strings['LBL_GENERATE_PASSWORD_BUTTON_LABEL']."'>  ";
			}
		}
	}
}

if(isset($_SERVER['QUERY_STRING'])) $the_query_string = $_SERVER['QUERY_STRING'];
else $the_query_string = '';

if (!$current_user->is_group){
    if ($focus->id == $current_user->id) {
        $reset_pref_warning = $mod_strings['LBL_RESET_PREFERENCES_WARNING'];
        $reset_home_warning = $mod_strings['LBL_RESET_HOMEPAGE_WARNING'];
    }
    else {
        $reset_pref_warning = $mod_strings['LBL_RESET_PREFERENCES_WARNING_USER'];
        $reset_home_warning = $mod_strings['LBL_RESET_HOMEPAGE_WARNING_USER'];
    }
	$buttons .="<input type='button' class='button' onclick='if(confirm(\"{$reset_pref_warning}\"))window.location=\"".$_SERVER['PHP_SELF'] .'?'.$the_query_string."&reset_preferences=true\";' value='".$mod_strings['LBL_RESET_PREFERENCES']."' />";
	$buttons .="&nbsp;<input type='button' class='button' onclick='if(confirm(\"{$reset_home_warning}\"))window.location=\"".$_SERVER['PHP_SELF'] .'?'.$the_query_string."&reset_homepage=true\";' value='".$mod_strings['LBL_RESET_HOMEPAGE']."' />";
 
}
if (isset($buttons)) $sugar_smarty->assign("BUTTONS", $buttons);

require_once("include/templates/TemplateGroupChooser.php");
require_once("modules/MySettings/TabController.php");
$chooser = new TemplateGroupChooser();
$controller = new TabController();

//if(is_admin($current_user) || $controller->get_users_can_edit())
if(is_admin($current_user)||is_admin_for_module($GLOBALS['current_user'],'Users'))
{
	$chooser->display_third_tabs = true;
	$chooser->args['third_name'] = 'remove_tabs';
	$chooser->args['third_label'] =  $mod_strings['LBL_REMOVED_TABS'];
}
elseif(!$controller->get_users_can_edit())
{
	$chooser->display_hide_tabs = false;
}
else
{
	$chooser->display_hide_tabs = true;
}

$chooser->args['id'] = 'edit_tabs';
$chooser->args['values_array'] = $controller->get_tabs($focus);
$chooser->args['left_name'] = 'display_tabs';
$chooser->args['right_name'] = 'hide_tabs';
$chooser->args['left_label'] =  $mod_strings['LBL_DISPLAY_TABS'];
$chooser->args['right_label'] =  $mod_strings['LBL_HIDE_TABS'];
$chooser->args['title'] =  $mod_strings['LBL_EDIT_TABS'];
$chooser->args['disable'] = true;

foreach ($chooser->args['values_array'][0] as $key=>$value)
{
$chooser->args['values_array'][0][$key] = $app_list_strings['moduleList'][$key];
}
foreach ($chooser->args['values_array'][1] as $key=>$value)
{
$chooser->args['values_array'][1][$key] = $app_list_strings['moduleList'][$key];
}


$sugar_smarty->assign("TAB_CHOOSER", $chooser->display());
$sugar_smarty->assign("CHOOSE_WHICH", $mod_strings['LBL_CHOOSE_WHICH']);

// <abcona>
// Extension for Sugar Asterisk integration
//
$xtpl->assign("ASTERISK_EXT_C", $focus->asterisk_ext_c);
if ($focus->asterisk_inbound_c)
{
    $xtpl->assign("ASTERISK_INBOUND_C", 'checked');
}
else
{
    $xtpl->assign("ASTERISK_INBOUND_C", '');
}
if ($focus->asterisk_outbound_c)
{
    $xtpl->assign("ASTERISK_OUTBOUND_C", 'checked');
}
else
{
    $xtpl->assign("ASTERISK_OUTBOUND_C", '');
}
// </abcona>


if ($focus->receive_notifications) $sugar_smarty->assign("RECEIVE_NOTIFICATIONS", "checked");
if($focus->getPreference('mailmerge_on') == 'on') {
$sugar_smarty->assign("MAILMERGE_ON", "checked");
}
$sugar_smarty->assign("SETTINGS_URL", $sugar_config['site_url']);


$sugar_smarty->assign("EXPORT_DELIMITER", $focus->getPreference('export_delimiter'));
$sugar_smarty->assign('EXPORT_CHARSET', $locale->getExportCharset('', $focus));
$sugar_smarty->assign('USE_REAL_NAMES', $focus->getPreference('use_real_names'));


global $timedate;
$sugar_smarty->assign("DATEFORMAT", $sugar_config['date_formats'][$timedate->get_date_format()]);
$sugar_smarty->assign("TIMEFORMAT", $sugar_config['time_formats'][$timedate->get_time_format()]);

$userTZ = $focus->getPreference('timezone');
if(!empty($userTZ) && isset($timezones[$userTZ])) {
	$value = $timezones[$userTZ];
}
if(!empty($value['dstOffset'])) {
	$dst = " (+DST)";
} else {
	$dst = "";
}
$gmtOffset = ($value['gmtOffset'] / 60);
if(!strstr($gmtOffset,'-')) {
	$gmtOffset = "+".$gmtOffset;
}

$sugar_smarty->assign("TIMEZONE", $userTZ. str_replace('_',' '," (GMT".$gmtOffset.") ".$dst) );
$datef = $focus->getPreference('datef');
$timef = $focus->getPreference('timef');

if(!empty($datef))
$sugar_smarty->assign("DATEFORMAT", $sugar_config['date_formats'][$datef]);
if(!empty($timef))
$sugar_smarty->assign("TIMEFORMAT", $sugar_config['time_formats'][$timef]);

$num_grp_sep = $focus->getPreference('num_grp_sep');
$dec_sep = $focus->getPreference('dec_sep');
$sugar_smarty->assign("NUM_GRP_SEP", (empty($num_grp_sep) ? $sugar_config['default_number_grouping_seperator'] : $num_grp_sep));
$sugar_smarty->assign("DEC_SEP", (empty($dec_sep) ? $sugar_config['default_decimal_seperator'] : $dec_sep));



$currency  = new Currency();
if($focus->getPreference('currency') ) {
	$currency->retrieve($focus->getPreference('currency'));
	$sugar_smarty->assign("CURRENCY", $currency->iso4217 .' '.$currency->symbol );
} else {
	$sugar_smarty->assign("CURRENCY", $currency->getDefaultISO4217() .' '.$currency->getDefaultCurrencySymbol() );
}
$sugar_smarty->assign('CURRENCY_SIG_DIGITS', $locale->getPrecedentPreference('default_currency_significant_digits', $focus));

$sugar_smarty->assign('NAME_FORMAT', $focus->getLocaleFormatDesc());


$sugar_smarty->assign("DESCRIPTION", nl2br(url2html($focus->description)));
$sugar_smarty->assign("TITLE", $focus->title);
$sugar_smarty->assign("DEPARTMENT", $focus->department);
$sugar_smarty->assign("REPORTS_TO_ID", $focus->reports_to_id);
$sugar_smarty->assign("REPORTS_TO_NAME", $focus->reports_to_name);
$sugar_smarty->assign("PHONE_HOME", $focus->phone_home);
$sugar_smarty->assign("PHONE_MOBILE", $focus->phone_mobile);
$sugar_smarty->assign("PHONE_WORK", $focus->phone_work);
$sugar_smarty->assign("PHONE_OTHER", $focus->phone_other);
$sugar_smarty->assign("PHONE_FAX", $focus->phone_fax);
if (!empty($focus->employee_status)) {
	$sugar_smarty->assign("EMPLOYEE_STATUS", $app_list_strings['employee_status_dom'][$focus->employee_status]);
}
$sugar_smarty->assign("MESSENGER_ID", $focus->messenger_id);
$sugar_smarty->assign("MESSENGER_TYPE", $focus->messenger_type);
$sugar_smarty->assign("ADDRESS_STREET", $focus->address_street);
$sugar_smarty->assign("ADDRESS_CITY", $focus->address_city);
$sugar_smarty->assign("ADDRESS_STATE", $focus->address_state);
$sugar_smarty->assign("ADDRESS_POSTALCODE", $focus->address_postalcode);
$sugar_smarty->assign("ADDRESS_COUNTRY", $focus->address_country);
$sugar_smarty->assign("EMAIL_ADDRESSES", $focus->emailAddress->getEmailAddressWidgetDetailView($focus));
$sugar_smarty->assign("CALENDAR_PUBLISH_KEY", $focus->getPreference('calendar_publish_key' ));
if (! empty($current_user->email1))
{
    $publish_url = $sugar_config['site_url'].'/vcal_server.php';
    $token = "/";
    //determine if the web server is running IIS
    //if so then change the publish url
    if(isset($_SERVER) && !empty($_SERVER['SERVER_SOFTWARE'])){
        $position = strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'iis');
        if($position !== false){
            $token = '?parms=';
        }
    }

    $publish_url .= $token.'type=vfb&email='.$focus->email1.'&source=outlook&key='.$focus->getPreference('calendar_publish_key' );
    $sugar_smarty->assign("CALENDAR_PUBLISH_URL", $publish_url);
    $sugar_smarty->assign("CALENDAR_SEARCH_URL", $sugar_config['site_url'].'/vcal_server.php/type=vfb&email=%NAME%@%SERVER%');
}
else
{
  $sugar_smarty->assign("CALENDAR_PUBLISH_URL", $sugar_config['site_url'].'/vcal_server.php/type=vfb&user_name='.$focus->user_name.'&source=outlook&key='.$focus->getPreference('calendar_publish_key' ));
  $sugar_smarty->assign("CALENDAR_SEARCH_URL", $sugar_config['site_url'].'/vcal_server.php/type=vfb&email=%NAME%@%SERVER%');
}

// Grouped tabs?
$useGroupTabs = $current_user->getPreference('navigation_paradigm');
if ( ! isset($useGroupTabs) ) {
    if ( ! isset($GLOBALS['sugar_config']['default_navigation_paradigm']) ) {
        $GLOBALS['sugar_config']['default_navigation_paradigm'] = 'gm';
    }
    $useGroupTabs = $GLOBALS['sugar_config']['default_navigation_paradigm'];
}
$sugar_smarty->assign("USE_GROUP_TABS",($useGroupTabs=='gm')?'checked':'');

$user_max_tabs = intval($focus->getPreference('max_tabs'));
if(isset($user_max_tabs) && $user_max_tabs > 0)
    $sugar_smarty->assign("MAX_TAB", $user_max_tabs);
elseif(isset($max_tabs) && $max_tabs > 0)
    $sugar_smarty->assign("MAX_TAB", $max_tabs);
else
    $sugar_smarty->assign("MAX_TAB", $GLOBALS['sugar_config']['default_max_tabs']);

$user_subpanel_tabs = $focus->getPreference('subpanel_tabs');
if(isset($user_subpanel_tabs)) {
    $sugar_smarty->assign("SUBPANEL_TABS", $user_subpanel_tabs?'checked':'');
} else {
    $sugar_smarty->assign("SUBPANEL_TABS", $GLOBALS['sugar_config']['default_subpanel_tabs']?'checked':'');
}

// Email Options

$sugar_smarty->assign("EMAIL_OPTIONS", $focus->emailAddress->getEmailAddressWidgetDetailView($focus));
$email_link_type = $focus->getPreference('email_link_type');
if ( !empty($email_link_type) )
    $sugar_smarty->assign('EMAIL_LINK_TYPE',$app_list_strings['dom_email_link_type'][$focus->getPreference('email_link_type')]);
if ( $focus->getPreference('email_link_type') == 'sugar' )
    $sugar_smarty->assign('SHOW_SMTP_SETTINGS',true);

//Handle outbound email templates
$oe = new OutboundEmail();
$userOverrideOE = $oe->getUsersMailerForSystemOverride($focus->id);
$mail_smtpuser = "";
$mail_smtpserver = "";

if($userOverrideOE == null)
{
    $systemOE = $oe->getSystemMailerSettings();
    $mail_smtpdisplay = $systemOE->mail_smtpdisplay;
    $mail_smtpserver = $systemOE->mail_smtpserver;
    $mail_smtptype = $systemOE->mail_smtptype;
    if( $oe->isAllowUserAccessToSystemDefaultOutbound() )
        $mail_smtpuser = $systemOE->mail_smtpuser;
}
else 
{
    $mail_smtpdisplay = $userOverrideOE->mail_smtpdisplay;
    $mail_smtpuser = $userOverrideOE->mail_smtpuser;
    $mail_smtpserver = $userOverrideOE->mail_smtpserver;
    $mail_smtptype = $userOverrideOE->mail_smtptype;
}
$sugar_smarty->assign("MAIL_SMTPUSER", $mail_smtpuser);
$sugar_smarty->assign("MAIL_SMTPDISPLAY", $mail_smtpdisplay);


$show_roles = (!($focus->is_group=='1' || $focus->portal_only=='1'));
$sugar_smarty->assign('SHOW_ROLES', $show_roles);

// User Holidays subpanel on the advanced tab
global $modules_exempt_from_availability_check;
$modules_exempt_from_availability_check=array('Holidays'=>'Holidays',);
$locked = false;
if(!empty($GLOBALS['sugar_config']['lock_subpanels'])){
	$locked = true;
}
$GLOBALS['sugar_config']['lock_subpanels'] = true;

$GLOBALS['sugar_config']['lock_subpanels'] = $locked;

$sugar_smarty->display('custom/modules/Users/DetailView.tpl');

// Roles Grid and Roles subpanel should not be displayed for group and portal users
if($show_roles){
    echo "<div>";
    require_once('modules/ACLRoles/DetailUserRole.php');
    echo "</div></div>";
}


echo "</td></tr>\n";


$savedSearch = new SavedSearch();
$json = getJSONobj();
$savedSearchSelects = $json->encode(array($GLOBALS['app_strings']['LBL_SAVED_SEARCH_SHORTCUT'] . '<br>' . $savedSearch->getSelect('Users')));
$str = "<script>
YAHOO.util.Event.addListener(window, 'load', SUGAR.util.fillShortcuts, $savedSearchSelects);
</script>";
echo $str;
echo "<script type='text/javascript'>user_status_display('$usertype') </script>";

$confirmDeleteJS = "
<script type='text/javascript'>

function confirmDelete() {
    var handleYes = function() {
        window.location=\"".$_SERVER['PHP_SELF'] ."?module=Users&action=delete&record={$focus->id}\";
    };

    var handleNo = function() {
        confirmDeletePopup.hide();
        return false;
     };
    var user_portal_group = '{$usertype}';
    var confirm_text = SUGAR.language.get('Users', 'LBL_DELETE_USER_CONFIRM');
    if(user_portal_group == 'GroupUser'){
        confirm_text = SUGAR.language.get('Users', 'LBL_DELETE_GROUP_CONFIRM');
    }
    
    var confirmDeletePopup = new YAHOO.widget.SimpleDialog(\"Confirm \", {
                width: \"400px\",
                draggable: true,
                constraintoviewport: true,
                modal: true,
                fixedcenter: true,
                text: confirm_text,
                bodyStyle: \"padding:5px\",
                buttons: [{
                        text: SUGAR.language.get('Users', 'LBL_OK'),
                        handler: handleYes,
                        isDefault:true
                }, {
                        text: SUGAR.language.get('Users', 'LBL_CANCEL'),
                        handler: handleNo
                }]
     });
    confirmDeletePopup.setHeader(SUGAR.language.get('Users', 'LBL_DELETE_USER'));
    confirmDeletePopup.render(document.body);
}
</script>";
echo $confirmDeleteJS;
?>
