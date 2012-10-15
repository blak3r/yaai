<?php
/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * Parts of this code are (c) 2012 Blake Robertson http://www.blakerobertson.com
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


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// All Sugar timestamps are UTC
date_default_timezone_set('UTC');

require_once('include/utils.php');
require_once('include/export_utils.php');

global $sugar_config;
global $locale;
global $current_user;

// TODO What are the chdir's for???
/*
chdir("../");
chdir("../");
chdir("../");
chdir("../");
*/

require_once('include/entryPoint.php');
require_once('modules/Contacts/Contact.php');

#require_once('modules/Users/User.php');
#session_start();

//include language

//$current_language = $_SESSION['authenticated_user_language'];
if(empty($current_language)) {
	$current_language = $sugar_config['default_language'];
}
require("custom/modules/Asterisk/language/" . $current_language . ".lang.php");


// Fetch Asterisk dialprefix - must strip this from inbound callerIDs if set
$calloutPrefix = $sugar_config['asterisk_prefix'];
$callinPrefix = $sugar_config['asterisk_dialinPrefix'];


//log_entry("$current_user->asterisk_ext_c is the extension...\n", "c:\callListenerLog.txt"); // FIXME remove this debug


// Original query that returns only "Active" Calls -- query below gives me ones that were updated in last hour (so user can still put notes on them).
//$query = " SELECT * FROM asterisk_log WHERE (callstate = 'Dial' OR callstate = 'Connected') AND (channel LIKE 'SIP/{$current_user->asterisk_ext_c}%')";

// NeedID is the default call state for outbound calls, an entry in asterisk_log is created before a call record is created.  See GITHUB issue #
$lastHour = date('Y-m-d H:i:s',time() - 1*60*60);
$availableExtensionsArray = explode(',', $current_user->asterisk_ext_c);

$query = " SELECT * FROM asterisk_log WHERE \"$lastHour\" < timestampCall AND (uistate IS NULL OR uistate != \"Closed\") AND (callstate != 'NeedID') AND (";
if (count($availableExtensionsArray) == 1) {
    $query .= " channel LIKE 'SIP/{$current_user->asterisk_ext_c}%' OR channel LIKE 'Local%{$current_user->asterisk_ext_c}%'";
}
else {
    $queryExtensionsArray = array();
    foreach ($availableExtensionsArray as $singleExtension) {
        array_push($queryExtensionsArray, " channel LIKE 'SIP/{$singleExtension}%' OR channel LIKE 'Local%{$singleExtension}%'");
    }
    $query .= implode(' OR ', $queryExtensionsArray);
}
$query .=")";
$resultSet = $current_user->db->query($query, false);
if($current_user->db->checkError()){
	trigger_error("checkForNewStates-Query failed: $query");
}

$response = array();
while($row = $current_user->db->fetchByAssoc($resultSet)){

	$item = array();
	$item['asterisk_id'] = $row['asterisk_id'];

	// All modstrings are in uppercase, so thats what toupper was added for... asterisk 1.6 returns camelcase states perhaps earlier versions didn't.
	$item['state'] = isset($mod_strings[strtoupper($row['callstate'])]) ? $mod_strings[strtoupper($row['callstate'])] : $row['callstate'];
	$item['is_hangup'] = $item['state'] == $mod_strings['HANGUP'];
    $item['state'] = "'" . $item['state'] . "'"; // Puts quotes around call state

	$item['id'] = $row['id'];
	//for opening the relevant phone record when call has been answered
	$item['call_record_id'] = $row['call_record_id'];

	if($row['direction'] == 'I'){

		// this call is coming in from a remote phone partner
		$item['call_type'] = "ASTERISKLBL_COMING_IN";
		$item['direction'] = "Inbound";
		$callPrefix = $callinPrefix;
	}

	if($row['direction'] == 'O'){

		// this call is coming in from a remote phone partner
		$item['call_type'] = "ASTERISKLBL_GOING_OUT";
		$item['direction'] = "Outbound";
		#$item['phone_number'] = $row['callerID'];
		#$item['asterisk_name'] = $row['callerName'];
		$callPrefix = $calloutPrefix;
	}

	// Remove dialout prefix if present

	$tmpCallerID = trim($row['callerID']);
	if ( (strlen($callPrefix) > 0)  && (strpos($tmpCallerID, $callPrefix) === 0) )
	{
		$tmpCallerID = substr($tmpCallerID, strlen($callPrefix));
	}
	$item['phone_number'] = $tmpCallerID;

	#$item['phone_number'] = $row['callerID'];
	$item['asterisk_name'] = $row['callerName'];
	$item['asterisk_id'] = $row['asterisk_id'];
	$item['timestampCall'] = $row['timestampCall'];


	if( !empty($row['timestampHangup']) ) {
		$to_time=strtotime($row['timestampHangup']);
	}
	else {
		$to_time = time();
	}

	$from_time=strtotime($row['timestampCall']);
	$duration = number_format(round(abs($to_time - $from_time) / 60,1),1);
	$item['duration'] = $duration;

	// prepare phone number passed in
	$phoneToFind = $item['phone_number'];

	// delete leading zeros
	$phoneToFind = ltrim($phoneToFind, '0');
    $phoneToFind = preg_replace('/\D/','',$phoneToFind); // Removes and non digits such as + chars.

	$gravEmailAddress = ""; //clear address
	$found = array();

    $isMultipleContactCase = false;
    $isNoMatchingContactCase = true;

	if(strlen($phoneToFind) > 5) {
		$sqlReplace = "
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			      %s,
			        ' ', ''),
			        '+', ''),
			        '.', ''),
			        '/', ''),
			        '(', ''),
			        ')', ''),
			        '[', ''),
			        ']', ''),
			        '-', '')
			        REGEXP '%s$' = 1
			";

		//$sqlReplace= "REGEXP '%s$' = 1";

        // TODO fix the join so that account is optional... I think just add INNER
        $selectPortion =  "SELECT c.id as contact_id, first_name,	last_name,phone_work, phone_home, phone_mobile, phone_other, a.name as account_name, account_id "
            . " FROM contacts c "
            . " left join accounts_contacts ac on (c.id=ac.contact_id) and (ac.deleted='0' OR ac.deleted is null)"
            . " left join accounts a on (ac.account_id=a.id) and (a.deleted='0' or a.deleted is null)";

        if( $row['contact_id'] ) {
            $wherePortion = " WHERE c.id='{$row['contact_id']}' and c.deleted='0'";
            //log_entry("Quick WHERE $selectPortion $wherePortion\n", "c:\callListenerLog.txt");
        }
        // We only do this expensive query if it's not already set!
        else {
            //log_entry("Expensive WHERE " . "\n", "c:\callListenerLog.txt");
            $wherePortion = " WHERE (";
            $wherePortion .= sprintf($sqlReplace, "phone_work", $phoneToFind) . " OR ";
            $wherePortion .= sprintf($sqlReplace, "phone_home", $phoneToFind) . " OR ";
            $wherePortion .= sprintf($sqlReplace, "phone_other", $phoneToFind) . " OR ";
            $wherePortion .= sprintf($sqlReplace, "assistant_phone", $phoneToFind) . " OR ";
            $wherePortion .= sprintf($sqlReplace, "phone_mobile", $phoneToFind) . ") and c.deleted='0'";
        }

        $queryContact = $selectPortion . $wherePortion;
        //log_entry($queryContacfastert . "\n","c:\callListenerLog.txt");
		$innerResultSet = $current_user->db->query($queryContact, false);

        //log_entry(printrs($innerResultSet),"c:\callListenerLog.txt");

        $isMultipleContactCase = false;
        $radioButtonCode = "";

        //log_entry("Num Rows:" . $innerResultSet->num_rows );

        if( $innerResultSet->num_rows > 1 ) {
            $isMultipleContactCase = true;
            //log_entry("multcontact case\n","c:\callListenerLog.txt");
        }

        // Once contact_id db column is set, $innerResultSet will only have a single row int it.
		while($contactRow = $current_user->db->fetchByAssoc($innerResultSet)){
            $found['contactFullName'] = $contactRow['first_name'] . " " . $contactRow['last_name'];
            //log_entry($found['contactFullName'] . "id=". $contactRow['contact_id'] . "\n","c:\callListenerLog.txt");
            $found['company'] = $contactRow['account_name'];
            $found['contactId'] = $contactRow['contact_id'];
            $cid =  $contactRow['contact_id'];
            $found['companyId'] = $contactRow['account_id'];

            // Only used in multi match case.
            $mouseOverTitle = "{$found['contactFullName']} - {$found['company']}"; // decided displaying <contact> - <account> took up too much space and 95% of the time you have multiple contacts its going to be from the same account... so we use mouse over to display account.
            $radioButtonCode .= "<input type=radio name=contactSelect onclick=\"javascript:setContactId('{$row['call_record_id']}','{$found['contactId']}')\" value={$found['contactId']}>&nbsp;&nbsp;<a id=\"astmultcontact\" title=\"$mouseOverTitle\" href=\"index.php?module=Contacts&action=DetailView&record={$found['contactId']}\">{$found['contactFullName']}</a><BR>";
            // <a id=\"astmultcompany\" href=\"index.php?module=Accounts&action=DetailView&record={$found['company_id']}\">{$found['company']}</a>

            // In the only 1 matching contact case, we can set the contact_id db column to cut down on doing expensive sql queries to find matching contact.
            if( empty( $row['contact_id'] ) && !$isMultipleContactCase ) {
                //$tempContactId = preg_replace('/[^a-z0-9\-\. ]/i', '', $contactRow['contact_id']);
                //$tempCallRecordId = preg_replace('/[^a-z0-9\-\. ]/i', '', $row['call_record_id']);
                //$insertQuery = "UPDATE asterisk_log SET contact_id='$tempContactId' WHERE call_record_id='$tempCallRecordId'";
                //$current_user->db->query($insertQuery, false);

                // This is a special case where a contact record is created after the call started.
                // Such As... Call begins, then user clicks create new contact, call ends...
                $_REQUEST['action'] = 'setContactId';
                $_REQUEST['call_record'] = $row['call_record_id'];
                $_REQUEST['contact_id'] = $contactRow['contact_id'];
                $_REQUEST['id']= $row['asterisk_id'];
                require_once("custom/modules/Asterisk/include/controller.php");
                log_entry("after_setContactId.... $isNoMatchingContactCase ... $isMultipleContactCase ... '{$row['contact_id']}' \n","C:/callListener.log");
            }
		} // End while $contactRow

        if( $isMultipleContactCase ) {
            $found['contactFullName'] = $mod_strings["ASTERISKLBL_MULTIPLE_MATCHES"];
        }

        // Check OpenCNAM if we don't already have the Company Name in Sugar.
        if( !isset($found['company']) && $sugar_config['asterisk_opencnam_enabled'] == "true"  )
        {
            //log_entry("company not set", "c:\callListenerLog.txt");
            if( $row['opencnam'] == NULL ) {
                //log_entry("Null in db\n", "c:\callListenerLog.txt");
                $tempCnamResult = opencnam_fetch($phoneToFind);
                $tempCnamResult = preg_replace('/[^a-z0-9\-\. ]/i', '', $tempCnamResult);
                $tempCallRecordId = preg_replace('/[^a-z0-9\-\. ]/i', '', $row['call_record_id']);
                $cnamUpdateQuery = "UPDATE asterisk_log SET opencnam='$tempCnamResult' WHERE call_record_id='$tempCallRecordId'";
                $current_user->db->query($cnamUpdateQuery, false);
                $row['opencnam'] = $tempCnamResult;
            }
            //log_entry($row['opencnam'], "c:\callListenerLog.txt");
            $item['callerid'] = $row['opencnam'];
        }

        $popupFormCode = <<<EOT1
<form name="{$item['call_record_id']}">
    <input type='hidden' size="" name="relateContactId"/>
    <input type='hidden' name="relateContactName"/>
</form>
EOT1;
        $createNewContactLink = '<a class="asteriskActionsLinks"  href="index.php?module=Contacts&action=EditView&phone_work=' . $phoneToFind .'" title="Create a new contact with this phone number">Create New</a>';
        $selectContactOpenPopupJs= 'open_popup( "Contacts", 600, 400, "", true, true, {"call_back_function":"relate_popup_callback","form_name":"' . $item['call_record_id'] . '","field_to_name_array":{"id":"relateContactId","last_name":"relateContactName"}},"single",true);';

        // TODO replace english sentences with entries in modstrings
        $selectContactIconCode = '<a href="#" onclick=\'' . $selectContactOpenPopupJs . '\' title="Click to relate this call with a different contact"><img width="12" src="custom/modules/Asterisk/include/close_16.png"></a>';
        $selectContactTextCode = '<a class="asteriskActionsLinks" href="#" onclick=\'' . $selectContactOpenPopupJs . '\' title="Relate this call with an existing contact.">Relate To</a>';
        /*
        if( !empty($cid) && $sugar_config['asterisk_gravatar_enabled'])
        {
            // TODO optimize this... can I grab this some other way? This is just to get the primary email address... might be a faster way to do this?
            $bean = new Contact();
            $bean->retrieve( $cid );
            $gravEmailAddress = $bean->emailAddress->getPrimaryAddress($bean);
            //log_entry(printrs($bean), "c:\callListenerLog.txt");
        }
        */
    }

    $isNoMatchingContactCase = !isset($found['contactFullName']);

    $item['full_name'] = isset($found['contactFullName']) ? $found['contactFullName'] : "";
	$item['company'] = isset($found['company']) ? $found['company'] : "";
	$item['contact_id'] = isset($found['contactId']) ? $found['contactId'] : "";
	$item['company_id'] = isset($found['companyId']) ? $found['companyId'] : "";

	//$item['sqlQuery'] = $queryContact; // Uncomment if you want to debug the query.	y

	$response[] = $item;

	} // end of while row




$responseArray = array();
if(count($response) == 0){
	print json_encode(array("."));
}else{
	foreach($response as $item){

		ob_start();
		require("custom/modules/Asterisk/include/ShowCall.html");
		$item['html'] = ob_get_contents();
		$item['html'] = str_replace("\n", "", $item['html']);
		$item['html'] = str_replace("\t", "", $item['html']);
		$item['html'] = str_replace("\r", "", $item['html']);
		ob_clean();

/*
        if( $sugar_config['asterisk_gravatar_integration_enabled'] ) {
		    if( !empty($gravEmailAddress) ) {
			    $gravHash = md5( strtolower( trim( $gravEmailAddress ) ) );
		    	$item['html'] .= '<img src="http://www.gravatar.com/avatar/'. $gravHash . '?s=160">';
		    }
		    $item['html'] .= '<a onclick="if ( DCMenu.menu ) DCMenu.menu(\'Contacts\',\'Create Contact\', true); return false;" href="#">Create Contact</a><BR>';
		    $item['html'] .= '<a href="index.php?module=Contacts&action=EditView&phone_work=' . $phoneToFind .'">Number2</a>';
        }
*/
		$responseArray[] = $item;
	}
	print json_encode($responseArray);
	ob_flush();
}

sugar_cleanup();

// Retrieves caller ID information using the opencnam rest service.
function opencnam_fetch( $phoneNumber ) {
    global $sugar_config;


    $request_url = "https://api.opencnam.com/v1/phone/" . $phoneNumber . "?format=pbx";
    // If using the PAID api
    if( !empty($sugar_config['asterisk_opencnam_apikey']) && !empty($sugar_config['asterisk_opencnam_username'])) {
        $request_url .= "&username={$sugar_config['asterisk_opencnam_username']}&api_key={$sugar_config['asterisk_opencnam_apikey']}";
    }

    $retries = isset($sugar_config['asterisk_opencnam_retries']) ? isset($sugar_config['asterisk_opencnam_retries']) : 4;
    $found = false;
    $i=0;
    do {
        $response = file_get_contents($request_url); // First call returns with 404 immediately with free api, 2nd call will succeed. See https://github.com/blak3r/yaai/issues/5
        // "Currently running a lookup for phone '7858647222'. Please check back in a few seconds."
        if( empty($response) || strpos($response, "running a lookup") !== false) {
            usleep(1000000*($i)); // wait 500ms, 1000ms, then 1500ms, etc.
            // 2:25pm uped to 500,000 8x
        }
        else {
            $found = true;
        }
    } while($i++ < $retries && $found == false );
    //log_entry("Open_CNAM for $phoneNumber took: $i attempts (8max) and returned: " . $response . "\n", "c:\opencnam_log.txt"); // TODO remove in production code.
    if( empty($response) ){
        $response = " "; // return a space character so it doesn't keep attempting to lookup number next time callListener is called.
    }
    return $response;
}

// just for debugging purposes
function log_entry( $str, $file = "default" ) {
	$handle = fopen($file, 'a');
	fwrite($handle, "[" . date('Y-m-j H:i:s') . "] " . $str );
	fclose($handle);
}

/// printr to string
function printrs($data) {
  $str = "";
  if ($data) {
    $str = '<pre>\n';
    $str .= print_r($data, TRUE);
    $str .= '</pre>\n';
  }
  return $str;
}

?>