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

/*
$mod_strings['LBL_ASTERISK'] = 'Set thedfhhhhhhhh Asterisk connection parameters and other preferences';
$mod_strings['LBL_ASTERISK_DESC'] = 'Ssdffset the Asterisk connection parameters and other preferences';
$mod_strings['LBL_ASTERISK_TITLE'] = 'YAAI - LBL_ASTERISK_TITLE SugarCRM Integration';

$mod_strings['LBL_MANAGE_ASTERISK'] = 'YAAI';
$mod_strings['LBL_MANAGE_ASTERISK_DESC'] = 'Module Configuration';
$mod_strings['LBL_ASTERISK_DONATE'] = 'Donate';
$mod_strings['LBL_ASTERISK_DONATE_DESC'] = 'Please help support this project by making a donation.';
$mod_strings['LBL_ASTERISK_USERMANUAL'] = 'User Manual';
$mod_strings['LBL_ASTERISK_USERMANUAL_DESC'] = 'Links to the online documentation for this project';
*/

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
$mod_strings['LBL_ASTERISK_EXPR_DESC'] = 'Regular expression to match incoming calls';
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

$mod_strings['LBL_ASTERISK_OPENCNAM_ENABLED'] = 'OpenCNAM Integration Enabled';
$mod_strings['LBL_ASTERISK_OPENCNAM_ENABLED_DESC'] = 'OpenCNAM is a webservice that can do caller id lookups.  If your country isnt supported by opencnam, you will want to keep this disabled.  USA works.  See www.OpenCNAM.com for more info.';
$mod_strings['LBL_ASTERISK_OPENCNAM_USERNAME'] = 'OpenCNAM Username (Paid API)';
$mod_strings['LBL_ASTERISK_OPENCNAM_USERNAME_DESC'] = 'Username is only required if you want to use the OpenCNAM paid API.  Leave blank otherwise.';
$mod_strings['LBL_ASTERISK_OPENCNAM_RETRIES'] = 'OpenCNAM Retry Attempts';
$mod_strings['LBL_ASTERISK_OPENCNAM_RETRIES_DESC'] = 'When a phone number is not in the OpenCNAM database it can take up to 20 seconds for the initial lookup.  OpenCNAM returns immediately with a 404 code if it is not already cached in it the database.  This makes callListner block for about 1000ms * number of retry attempts in an attempt to try and wait for the data to come back in a reasonable time frame. As a result, the more retry attempts the longer it will take for the first call popup to appear on a users screen.  Recommended this is not raised higher then 4.';
$mod_strings['LBL_ASTERISK_OPENCNAM_APIKEY'] = 'OpenCNAM Integration API Key (Paid API)';
$mod_strings['LBL_ASTERISK_OPENCNAM_APIKEY_DESC'] = 'An API Key is not required.  If you leave it blank, it\'ll use the free api which has a limitation of 60 lookups per hour.  The free API responses are slower.  Its fairly common for this to fail the first time someone calls.';
$mod_strings['LBL_ASTERISK_GRAVATAR_ENABLED'] = 'Gravatar Image On Call Popups:';
$mod_strings['LBL_ASTERISK_GRAVATAR_ENABLED_DESC'] = 'This was an experiment we implemented.  When a call is matched to a contact, we look to see if their email address has a gravatar icon associated with it.  The image is displayed at the bottom of chat window.  In my personal experience, we found very few business emails have gravatars so we didnt build this into the UI further.';
$mod_strings['LBL_ASTERISK_SHORT_CALL_STATUS'] = 'Short Call Status';
$mod_strings['LBL_ASTERISK_SHORT_CALL_STATUS_DESC'] = 'This defines what to do when a short call is received.  A short call could be a call that was missed (incoming call that was never answered).  Could also be a very short outbound call such as getting a busy signal.  The two options for this are either: Held or Missed.  Setting it to Missed will make the call record appear as an open task in the activities subpanel.  Setting it to held will not.';
$mod_strings['LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS'] = 'Hide Call Popups After X Mins';
$mod_strings['LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS_DESC'] = 'Specifies how long the user has to take notes in call popups before they disappear.  Default is 60 minutes.';

$mod_strings['LBL_ASTERISK_LOG_FILE'] = 'Log File Path for asteriskLogger';
$mod_strings['LBL_ASTERISK_LOG_FILE_DESC'] = 'Enter a file path to enable logging.  Important: make sure asterisk logger has permissions to write to this file.  If it does not, asterisk logger will crash.';

$mod_strings['LBL_ASTERISK_RECORDINGS_ENABLED'] = "Enable Call Recording Downloads (WIP)";
$mod_strings['LBL_ASTERISK_RECORDINGS_ENABLED_DESC'] = 'NOT YET RELEASED - This is an advanced feature which will require asterisk dialplan modifications in order to get working.  You will also need to have file system access to recordings folder.  So, if asterisk is on a different machine from your Sugar Instance you will need to map it somehow.';
$mod_strings['LBL_ASTERISK_RECORDINGS_PATH'] = 'Asterisk Call Recordings Path (WIP)';
$mod_strings['LBL_ASTERISK_RECORDINGS_PATH_DESC'] = 'NOT YET RELEASED - This is an advanced feature which requires asterisk dialplan modifications in order to get working.  You will also need to have file system access to recordings folder.  So, if asterisk is on a different machine from your Sugar Instance you will need to map it somehow.';

?>