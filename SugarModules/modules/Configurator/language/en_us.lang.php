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

$mod_strings['LBL_MANAGE_ASTERISK'] = 'ASTERISK Configuration';
$mod_strings['LBL_ASTERISK_DONATE'] = 'Donate';
$mod_strings['LBL_ASTERISK_DONATE_DESC'] = 'Please help support this project by making a donation.';
$mod_strings['LBL_ASTERISK_USERMANUAL'] = 'User Manual';
$mod_strings['LBL_ASTERISK_USERMANUAL'] = 'Links to the online documentation for this project';


$mod_strings['LBL_ASTERISK_HOST'] = 'Asterisk Manager Host';
$mod_strings['LBL_ASTERISK_PORT'] = 'Asterisk Manager Port';
$mod_strings['LBL_ASTERISK_USER'] = 'Asterisk Manager Login';
$mod_strings['LBL_ASTERISK_SECRET'] = 'Asterisk Manager Secret';

$mod_strings['LBL_ASTERISK_PREFIX'] = 'Dialout Prefix';
$mod_strings['LBL_ASTERISK_DIALINPREFIX'] = 'Dialin Prefix';
$mod_strings['LBL_ASTERISK_CONTEXT'] = 'Dial Context';
$mod_strings['LBL_ASTERISK_EXPR'] = 'Dialpattern for inbound/outbound matching';
$mod_strings['LBL_ASTERISK_EXPR_DESC'] = 'Regular expression to match incoming calls';
$mod_strings['LBL_ASTERISK_DIALOUT_CHANNEL'] = 'Dialout Channel';
$mod_strings['LBL_ASTERISK_DIALOUT_CHANNEL_DESC'] = 'Regular expression the ### will be replaced with current users extension';
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
$mod_strings['LBL_ASTERISK_OPENCNAM_ENABLED'] = 'OpenCNAM Integration Enabled (USA Only)';
$mod_strings['LBL_ASTERISK_OPENCNAM_ENABLED_DESC'] = 'OpenCNAM is a webservice that can do caller id lookups.  It is only useful in the USA at the moment.';
$mod_strings['LBL_ASTERISK_OPENCNAM_USERNAME'] = 'OpenCNAM Username (Paid API)';
$mod_strings['LBL_ASTERISK_OPENCNAM_RETRIES'] = 'OpenCNAM Retry Attempts';
$mod_strings['LBL_ASTERISK_OPENCNAM_RETRIES'] = 'When a phone nubmer is not in the OpenCNAM database it can take up to 20 seconds for the initial lookup.  OpenCNAM returns immediately with a 404 code if it is not already cached in it the database.  This makes callListner block for about 1000ms * number of retry attempts in an attempt to try and wait for the data to come back in a reasonable time frame. As a result, the more retry attempts the longer it will take for the first call popup to appear on a users screen.  Recommended this is not raised higher then 4.';
$mod_strings['LBL_ASTERISK_OPENCNAM_APIKEY'] = 'OpenCNAM Integration API Key (If using PAID API)';
$mod_strings['LBL_ASTERISK_OPENCNAM_APIKEY_DESC'] = 'An API Key is not required.  If you leave it blank, itll use the free api which has a limitation of 60 lookups per hour.  The free API responses are slower.  Its fairly common for this to fail the first time someone calls.';
$mod_strings['LBL_ASTERISK_GRAVATAR_ENABLED'] = 'Gravatar Image On Call Popups:';
$mod_strings['LBL_ASTERISK_GRAVATAR_ENABLED_DESC'] = 'This was an experiment we implemented.  When a call is matched to a contact, we look to see if their email address has a gravatar icon associated with it.  The image is displayed at the bottom of chat window.  In my personal experience, we found very few business emails have gravatars so we didnt build this into the UI further.';
$mod_strings['LBL_ASTERISK_SHORT_CALL_STATUS'] = 'Short Call Status';
$mod_strings['LBL_ASTERISK_SHORT_CALL_STATUS_DESC'] = 'This defines what to do when a short call is received.  A short call could be a call that was missed (incoming call that was never answered).  Could also be a very short outbound call such as getting a busy signal.  The two options for this are either: Held or Missed.  Setting it to Missed will make the call record appear as an open task in the activities subpanel.  Setting it to held will not.';
$mod_strings['LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS'] = 'Hide Call Popups After X Mins';
$mod_strings['LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS_DESC'] = 'Specifies how long the user has to take notes in call popups before they disappear.  Default is 60 minutes.';

$mod_strings['LBL_ASTERISK_LOG_FILE'] = 'Log File Path for asteriskLogger.';

$mod_strings['LBL_ASTERISK_RECORDINGS_ENABLE'] = "Enable Call Recording Downloads";
$mod_strings['LBL_ASTERISK_RECORDINGS_PATH'] = 'Asterisk Call Recordings Path';
$mod_strings['LBL_ASTERISK_RECORDINGS_PATH_DESC'] = 'This is an advanced feature which requires asterisk dialplan modifications in order to get working.  You will also need to have file system access to recordings folder.  So, if asterisk is on a different machine from your Sugar Instance you will need to map it somehow.';

?>