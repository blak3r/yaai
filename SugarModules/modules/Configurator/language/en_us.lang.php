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

if (!isset($mod_strings)) { $mod_strings = array(); }


$mod_strings['LBL_ASTERISK_LICENSE'] = 'License Key';
$mod_strings['LBL_ASTERISK_LICENSE_DESC'] = 'Enter the key provided to you when you purchased Callinize.';

$mod_strings['LBL_ASTERISK_HOST'] = 'Asterisk Manager Host';
$mod_strings['LBL_ASTERISK_HOST_DESC'] = 'Set this to the hostname or IP address of the server that asterisk is running on.  If it is the same as your sugarcrm server, set to 127.0.0.1';
$mod_strings['LBL_ASTERISK_PORT'] = 'Asterisk Manager Port';
$mod_strings['LBL_ASTERISK_USER'] = 'Asterisk Manager Login';
$mod_strings['LBL_ASTERISK_USER_DESC'] = 'Set to the AMI user you created for YAAI.  See usermanual for more info.  Note: THIS IS NOT YOUR SUGARCRM Login';
$mod_strings['LBL_ASTERISK_SECRET'] = 'Asterisk Manager Secret';

$mod_strings['LBL_ASTERISK_PREFIX'] = 'Dialout Prefix';
$mod_strings['LBL_ASTERISK_PREFIX_DESC'] = 'If you have to dial 9 to reach an outside line, you would put 9 here';
$mod_strings['LBL_ASTERISK_DIALINPREFIX'] = 'Dialin Prefix';
$mod_strings['LBL_ASTERISK_DIALINPREFIX_DESC'] = 'Strips these numbers from the inbound call... for example if incoming calls show up as +1########## but you dont have +1 in the phone fields for contacts, you would put +1 in this field. If inbound calls are not being related to contacts in your database, you might need to tweak this setting.';
$mod_strings['LBL_ASTERISK_CONTEXT'] = 'Dial Context';
$mod_strings['LBL_ASTERISK_EXPR'] = 'Dialpattern for inbound/outbound matching';
$mod_strings['LBL_ASTERISK_EXPR_DESC'] = 'Regular expression which is applied to the Channel and Destination elements of an AMI Dial.  This regex needs to be able to detect whether the Channel or the Destination is the local side (ie none external) channel for the phone call.  This is then used to determine if the call is inbound or outbound.  This should not need to be changed unless your trunk shows up as something like SIP/ followed by 3 digits.';
$mod_strings['LBL_ASTERISK_DIALOUT_CHANNEL'] = 'Dialout Channel';
$mod_strings['LBL_ASTERISK_DIALOUT_CHANNEL_DESC'] = 'Regular expression the ### will be replaced with current users extension. (Always leave the ### as 3 digits regardless of how many actual digits your extensions are).';
$mod_strings['LBL_ASTERISK_DIALIN_EXT_MATCH'] = 'Custom User Ext Match Regex';
$mod_strings['LBL_ASTERISK_RG_DETECT_EXPR'] = 'Ring Group Detect';
$mod_strings['LBL_ASTERISK_RG_DETECT_EXPR_DESC'] = 'Advanced Setting, see manual.  This setting only comes into play when trying to get Ring Groups properly detected';
$mod_strings['LBL_ASTERISK_RG_CELL_RING_EXPR'] = 'Ring Group External Line Detect';

$mod_strings['LBL_ASTERISK_SOAPUSER'] = 'SugarCRM Soap-User';
$mod_strings['LBL_ASTERISK_SOAPUSER_DESC'] = 'Set this to your SugarCRM username that has SOAP Access, this user will also be the one that calls are assigned to by default if no other user match is found.';
$mod_strings['LBL_ASTERISK_SOAPPASS'] = 'SugarCRM Soap-Pass';

$mod_strings['LBL_ASTERISK_CALL_SUBJECT_INBOUND_ABBR'] = 'Inbound Call Subject Prefix';
$mod_strings['LBL_ASTERISK_CALL_SUBJECT_OUTBOUND_ABBR'] = 'Outbound Call Subject Prefix';
$mod_strings['LBL_ASTERISK_CALL_SUBJECT_MAX_LENGTH'] = 'Max Subject Length';
$mod_strings['LBL_ASTERISK_LISTENER_POLL_RATE'] = 'AJAX Req Poll Rate in MS';
$mod_strings['LBL_ASTERISK_LISTENER_POLL_RATE_DESC'] = 'This specifies how frequently an AJAX call is performed to check for new call info.  So, if this is 5000 then there is a maximum time of 5 seconds before a new call popup will appear after its come in to asterisk.  Do not set much lower if you have a lot of CRM users as this will affect your performance.';

$mod_strings['LBL_ASTERISK_ONLY_LOG_CALLS_MATCHING_USER_EXTENSION'] = 'Only Log Calls that Match a User';
$mod_strings['LBL_ASTERISK_ONLY_LOG_CALLS_MATCHING_USER_EXTENSION_DESC'] = 'If set to true, any calls which do NOT match a user will have the Call objects deleted for.  This happens at hangup.  Use this if you do not want to log calls from the entire company and instead only log calls from the Users who have their extensions configured.';

//$mod_strings['LBL_ASTERISK_SECTIONHDR_OPENCNAM'] = "&nbsp;&nbsp;<a href='www.opencnam.com'>OpenCNAM</a> is a 3rd party api service which provides caller id.  When inbound calls do not match a contact in the CRM, we look it up in OpenCNAM to try and identify the caller.  OpenCNAM offers free and paid accounts.<BR>&nbsp;&nbsp;The free accounts return only cached results where as the paid accounts perform real time lookups.";
$mod_strings['LBL_ASTERISK_OPENCNAM_ENABLED'] = 'OpenCNAM Integration Enabled';
$mod_strings['LBL_ASTERISK_OPENCNAM_ENABLED_DESC'] = 'OpenCNAM is a webservice that can do caller id lookups.  If your country isnt supported by opencnam, you will want to keep this disabled.  USA works.  See www.OpenCNAM.com for more info.';
$mod_strings['LBL_ASTERISK_OPENCNAM_ACCOUNT_SID'] = 'OpenCNAM Account SID (Paid API)';
$mod_strings['LBL_ASTERISK_OPENCNAM_ACCOUNT_SID_DESC'] = 'Username is only required if you want to use the OpenCNAM paid API.  Leave blank otherwise.';
$mod_strings['LBL_ASTERISK_OPENCNAM_AUTH_TOKEN'] = 'OpenCNAM Auth Token (Paid API)';
$mod_strings['LBL_ASTERISK_OPENCNAM_AUTH_TOKEN_DESC'] = 'This is not required.  If you leave it blank, it\'ll use the free api which has a limitation of 60 lookups per hour.';
$mod_strings['LBL_ASTERISK_GRAVATAR_ENABLED'] = 'Gravatar Image On Call Popups:';
$mod_strings['LBL_ASTERISK_GRAVATAR_ENABLED_DESC'] = 'This was an experiment we implemented.  When a call is matched to a contact, we look to see if their email address has a gravatar icon associated with it.  The image is displayed at the bottom of chat window.  In my personal experience, we found very few business emails have gravatars so we didnt build this into the UI further.';


$mod_strings['LBL_ASTERISK_SHORT_CALL_STATUS'] = 'Short Call Status';
$mod_strings['LBL_ASTERISK_SHORT_CALL_STATUS_DESC'] = 'This defines what to do when a short call is received.  A short call could be a call that was missed (incoming call that was never answered).  Could also be a very short outbound call such as getting a busy signal.  The two options for this are either: Held or Missed.  Setting it to Missed will make the call record appear as an open task in the activities subpanel.  Setting it to held will not.';
$mod_strings['LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS'] = 'Hide Call Popups After X Mins';
$mod_strings['LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS_DESC'] = 'Specifies how long the user has to take notes in call popups before they disappear.  Default is 60 minutes.';
$mod_strings['LBL_ASTERISK_DIGITS_TO_MATCH'] = 'Digits to Match';
$mod_strings['LBL_ASTERISK_DIGITS_TO_MATCH_DESC'] = 'Specifies the number of digits to match in a phone number.  For example, if only 8 is specified just the last 8 digits are matched.  This can be helpful if you have local numbers come in with less digits then international or long distance numbers.  Setting this number too low you run the risk of matching multiple contacts for the same phone number.';
$mod_strings['LBL_ASTERISK_LOGGER_SQL_MODE'] = 'SQL Performance Mode (Experimental)';
$mod_strings['LBL_ASTERISK_LOGGER_SQL_MODE_DESC'] = 'When checked, the logger script will do direct SQL queries to the Calls module instead of making SOAP calls whenever possible.  The performance of this mode is better and should be used on very active asterisk installations to prevent popup delay.  The downside of this mode is if you use any workflow rules that get triggered on the Calls object they will not be fired.';


$mod_strings['LBL_ASTERISK_LOG_FILE'] = 'Log File Path for asteriskLogger';
$mod_strings['LBL_ASTERISK_LOG_FILE_DESC'] = 'Enter a file path to enable logging.  Important: make sure asterisk logger has permissions to write to this file.  If it does not, asterisk logger will crash.';
$mod_strings['LBL_ASTERISK_EVENT_LOG_FILE'] = 'Dial Event Log File Path';
$mod_strings['LBL_ASTERISK_EVENT_LOG_FILE_DESC'] = 'Creates a condensed event log which is html formatted, color coded, 1 line per event.  Makes it easier to see the big picture as the asterisk log is quite verbose.  Was originally developed to facilitate understanding how asterisk generates ids in ring groups / call queue cases.  Should be left blank in production.  This log file is not rotated. Make sure the process asteriskLogger is running as has appropriate permissions to write to this file.';


$mod_strings['LBL_ASTERISK_RECORDINGS_ENABLED'] = "Enable Call Recording Downloads (WIP)";
$mod_strings['LBL_ASTERISK_RECORDINGS_ENABLED_DESC'] = 'This feature will allow playback of call recordings.  You will need to configure asterisk to recording calls automatically for the appropriate extensions.  You will also need to have file system access to recordings folder.  See the user guide for more info';
$mod_strings['LBL_ASTERISK_RECORDINGS_PATH'] = 'Asterisk Call Recordings Path (WIP)';
$mod_strings['LBL_ASTERISK_RECORDINGS_PATH_DESC'] = 'Set this to the path where recordings can be found. If asterisk is on a different machine from your Sugar Instance you will need to setup a network file share or some type of syncing solution.  See the user guide for more info on this feature.';

$mod_strings['LBL_ASTERISK_FOP_URL'] = 'Flash Operator Panel URL';
$mod_strings['LBL_ASTERISK_FOP_URL_DESC'] = 'If you have the FOP2 extension installed on your asterisk box, specify the URL to use here.  Leave it blank to leave it disabled.';

$mod_strings['LBL_ASTERISK_FOP_MASTER_PASSWORD'] = 'FOP2 Master Pass (optional)';
$mod_strings['LBL_ASTERISK_BLOCK_BUTTON_ENABLED'] = 'Show Block Call';
$mod_strings['LBL_ASTERISK_FOP_BUTTON_ENABLED'] = 'Show FOP2 Button';
$mod_strings['LBL_ASTERISK_TRANSFER_BUTTON_ENABLED'] = 'Show Transfer Button';
$mod_strings['LBL_ASTERISK_RELATE_TO_ACCOUNT_ENABLED'] = 'Show Relate to Account';
$mod_strings['LBL_ASTERISK_RELATE_TO_CONTACT_ENABLED'] = 'Show Relate To Contact';
$mod_strings['LBL_ASTERISK_RELATE_TO_LEAD_ENABLED'] = 'Show Relate To Lead';
$mod_strings['LBL_ASTERISK_CREATE_NEW_CONTACT_ENABLED'] = 'Show Create New Contact';
$mod_strings['LBL_ASTERISK_CREATE_NEW_LEAD_ENABLED'] = 'Show Create New Lead';
$mod_strings['LBL_ASTERISK_MAX_POPUPS'] = 'Sets the Max Call Popups';
$mod_strings['LBL_ASTERISK_MAX_POPUPS_DESC'] = 'Sets the Max Number of Popups that will appear on users screen.  This is good for users who get a lot of calls.  They might return to desk and have to X out of a lot of call popups.';
$mod_strings['LBL_ASTERISK_FILTERED_CALL_STATES'] = 'Filtered Call States';
$mod_strings['LBL_ASTERISK_WINDOW_HEIGHT'] = 'Custom Window Height';

//$mod_strings['LBL_ASTERISK_SECTIONHDR_CALLINIZE'] = '&nbsp;&nbsp;Callinize is a mobile phone app which provides caller information even when on your mobile phone.  See <a href="www.callinize.com">www.callinize.com</a> for more information.';
$mod_strings['LBL_ASTERISK_CALLINIZE_API_ORGANIZATIONID'] = 'Callinize API Organization Id';
$mod_strings['LBL_ASTERISK_CALLINIZE_API_ORGANIZATIONSECRET'] = 'Callinize API Organization Secret';

$mod_strings['LBL_ASTERISK_CONTACT_PHONE_FIELDS'] = "Contact Phone Fields";
$mod_strings['LBL_ASTERISK_CONTACT_PHONE_FIELDS_DESC'] = "These are the phone fields that will be searched when trying to find matching contacts.  If you've created your own custom fields, you can add them to the list here.  Be sure to add: Comma separated field names ending with _c and no spaces.";
$mod_strings['LBL_ASTERISK_ACCOUNT_PHONE_FIELDS'] = "Account Phone Fields";
$mod_strings['LBL_ASTERISK_CONTACT_PHONE_FIELDS_DESC'] = "These are the phone fields that will be searched when trying to find matching accounts.  If you've created your own custom fields, you can add them to the list here.  Be sure to add: Comma separated field names ending with _c and no spaces.";
$mod_strings['LBL_ASTERISK_LEAD_PHONE_FIELDS'] = "Lead Phone Fields";
$mod_strings['LBL_ASTERISK_LEAD_PHONE_FIELDS_DESC'] = "These are the phone fields that will be searched when trying to find matching leads.  If you've created your own custom fields, you can add them to the list here.  Be sure to add: Comma separated field names ending with _c and no spaces.";

$mod_strings['LBL_ASTERISK_CALLINIZE_DEV'] = "Dev Mode Enabled";
$mod_strings['LBL_ASTERISK_CALLINIZE_DEV_DESC'] = "Enabled developer mode, handlebars template is compiled in browser (rather then pre-compiled).  Set to 0 unless you have a reason to.";
$mod_strings['LBL_ASTERISK_CALLINIZE_DEBUG'] = "Debug Mode Enabled";
$mod_strings['LBL_ASTERISK_CALLINIZE_DEBUG_DESC'] = "Provides debug information about call popups in the Console.log (should be 0 in production).";

$mod_strings['LBL_ASTERISK_CALLINIZE_DEBUG'] = "Debug Mode Enabled";
$mod_strings['LBL_ASTERISK_CALLINIZE_DEBUG_DESC'] = "Provides debug information about call popups in the Console.log (should be 0 in production).";

$mod_strings['LBL_ASTERISK_JQUERY_OVERRIDE'] = "Jquery Override";
$mod_strings['LBL_ASTERISK_JQUERY_OVERRIDE'] = "If you have conflicts with multiple jquery libraries being loaded, set this override";


?>