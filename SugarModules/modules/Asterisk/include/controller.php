<?php

/* * *
* Author: Blake Robertson
*
* Author: Patrick Hogan
*
* Controller class for various AJAX things such as saving the UI state and saving the call details.
*
* TODO: createCall should be refactored into this php file and then called by specifying an appropriate action for them.
*/



if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

require_once('include/utils.php');
require_once('include/export_utils.php');
require_once('modules/Calls/Call.php');
require_once('modules/Users/User.php');
//sets $mod_strings variable

// Check to see if the language file exists, if not we load the US English one.
// TODO should we use include_once instead of require_once?
// Settled on using include as it's faster... then include_once
if(is_file("custom/modules/Asterisk/language/" . $GLOBALS['sugar_config']['default_language'] . ".lang.php")) {
    include("custom/modules/Asterisk/language/" . $GLOBALS['sugar_config']['default_language'] . ".lang.php");
}
else {
    include("custom/modules/Asterisk/language/en_us.lang.php");
}

//ACTIONS

switch ($_REQUEST['action']) {
    case "memoSave" :
        if ($_REQUEST['call_record']) {
             memoSave($_REQUEST['call_record'], $_REQUEST['sugar_user_id'], $_REQUEST['phone_number'], $_REQUEST['description'], $_REQUEST['contact_id'], $_REQUEST['direction']);
        }
        break;
    case "updateUIState" :
        updateUIState($_REQUEST['ui_state'], $_REQUEST['call_record'], $_REQUEST['id']);
        break;
    case "setContactID" :
        setContactID($_REQUEST['call_record'], $_REQUEST['contact_id']);
        break;
    case "call" :
        callCreate();
        break;
    case "transfer" :
        transferCall($_REQUEST["extension"], $_REQUEST['call_record']);
        break;
    case "block" :
        blockNumber($_REQUEST['number'], $_REQUEST['description']);
        break;
    case "get_calls" :
        getCalls($mod_strings);
        break;
    default :
        echo "undefined action";
        break;
}

// ACTION FUNCTIONS

function memoSave($call_record_id, $sugar_user_id, $phone_number, $description, $contact_id, $direction) {
    $GLOBALS['log']->fatal('memoSave' . $phone_number);
    if ($call_record_id) {
        $call = new Call();

        if (!empty($contact_id)) {
            $call->parent_id = $contact_id;
            $call->parent_type = 'Contacts';
        }

        $call->retrieve($call_record_id);
        $call->description = $description;
        //!$name ? $call->name = getMemoName($call, $direction) : $call->name = $_REQUEST["name"];
        $GLOBALS['log']->fatal('memoSave' . $phone_number);
        $call->name = getMemoName($call, $direction, $phone_number);
        $call->assigned_user_id = $sugar_user_id;
        $call->save();
        $GLOBALS['log']->fatal('callid_' . $call->id);
    }
}

function updateUIState($ui_state, $call_record, $asterisk_id) {
    $cUser = new User();
    $cUser->retrieve($_SESSION['authenticated_user_id']);

    // query log
    // Very basic santization
    $uiState = preg_replace('/[^a-z0-9\-\. ]/i', '', $ui_state); // mysql_real_escape_string($_REQUEST['ui_state']);
    $callRecord = preg_replace('/[^a-z0-9\-\. ]/i', '', $call_record); //mysql_real_escape_string($_REQUEST['call_record']);
    $asteriskID = preg_replace('/-/', '.', $asterisk_id);
    // Workaround See Discussion here: https://github.com/blak3r/yaai/pull/20
    if (isset($call_record)) {
        $query = "update asterisk_log set uistate=\"$uiState\" where call_record_id=\"$callRecord\"";
    } else {
        $query = "update asterisk_log set uistate=\"$uiState\" where asterisk_id=\"$asteriskID\"";
    }

    $cUser->db->query($query, false);
    if ($cUser->db->checkError()) {
        trigger_error("Update UIState-Query failed: $query");
    }
}

function setContactID($call_record, $contact_id) {
    //wrapped the entire action to require a call_record - if this is not being passed then there is no point for this action - PJH
    if (is_string($call_record) && is_string($contact_id) ) {
        // Very basic sanitization
        $contactId = preg_replace('/[^a-z0-9\-\. ]/i', '', $contact_id);  
        $callRecord = preg_replace('/[^a-z0-9\-\. ]/i', '', $call_record); 
        // Workaround See Discussion here: https://github.com/blak3r/yaai/pull/20

        $query = "update asterisk_log set contact_id=\"$contactId\" where call_record_id=\"$callRecord\"";

        $GLOBALS['current_user']->db->query($query, false);
        if ($GLOBALS['current_user']->db->checkError()) {
            trigger_error("Update setContactId-Query failed: $query");
        }

        // Adds the new relationship! (This must be done here in case the call has already been hungup as that's when asteriskLogger sets relations)
        $focus = new Call();
        $focus->retrieve($callRecord);
        $focus->load_relationship('contacts');
        // Remove any contacts already associated with call (if there are any)
        foreach ($focus->contacts->getBeans() as $contact) {
            $focus->contacts->delete($callRecord, $contact->id);
        }
        $focus->contacts->add($contactId); // Add the new one!
        $contactBean = new Contact();
        $contactBean->retrieve($contactId);
        $focus->parent_id = $contactBean->account_id;
        $focus->parent_type = "Accounts";
        $focus->save();
    }
}

function callCreate() {
    // TODO: For some reason this code isn't working... I think it's getting the extension.
// For the time being, callCreate is still being used.

    /*
$cUser = new User();
$cUser->retrieve($_SESSION['authenticated_user_id']);
$extension = $cUser->asterisk_ext_c;

//$extension = $current_user->asterisk_ext_c;
$context = $GLOBALS['sugar_config']['asterisk_context'];

// Take the user supplied pattern, we find the part with the #'s (which are the ext)... then we get something like
// asterisk_dialout_channel == "SIP/###" --> $matches[1] == SIP/, $matches[2] == "###", $matches[3] is "".
// asterisk_dialout_channel == "Local/###@sugarsip/n" --> $matches[1] == Local/, $matches[2] == "###", $matches[3] is "@sugarsip/n".
preg_match('/([^#]*)(#+)([^#]*)/',$GLOBALS['sugar_config']['asterisk_dialout_channel'],$matches);
$channel = $matches[1] . $extension . $matches[3];

//format Phone Number
$number = $_REQUEST['phoneNr'];
$prefix = $GLOBALS['sugar_config']['asterisk_prefix'];
$number = str_replace("+", "00", $number);
$number = str_replace(array("(", ")", " ", "-", "/", "."), "", $number);
$number = $prefix.$number;


// dial number
$cmd = "";
$cmd .= "Action: originate\r\n";
$cmd .= "Channel: ". $channel ."\r\n";
$cmd .= "Context: ". $context ."\r\n";
$cmd .= "Exten: " . $number . "\r\n";
$cmd .= "Priority: 1\r\n";
$cmd .= "Callerid:" . $_REQUEST['phoneNr'] ."\r\n";
$cmd .= "Variable: CALLERID(number)=" . $extension . "\r\n\r\n";

SendAMICommand($cmd);
*/
}

function transferCall($extension, $call_record) {
    $exten = preg_replace('/\D/', '', $extension); // removes anything that isn't a digit.
    if (empty($exten)) {
        echo "ERROR: Invalid extension";
    }

    $callRecord = preg_replace('/[^a-z0-9\-\. ]/i', '', $call_record);
    $query = "Select remote_channel from asterisk_log where call_record_id='$callRecord'";

    $resultSet = $GLOBALS['current_user']->db->query($query, false);
    if ($GLOBALS['current_user']->db->checkError()) {
        trigger_error("Find Remote Channel-Query failed: $query");
    }

    while ($row = $GLOBALS['current_user']->db->fetchByAssoc($resultSet)) {
        $context = $GLOBALS['sugar_config']['asterisk_context'];
        $cmd = "ACTION: Redirect\r\nChannel: {$row['remote_channel']}\r\nContext: $context\r\nExten: $exten\r\nPriority: 1\r\n\r\n";
        SendAMICommand($cmd);
    }


    // Inbound call trying, THIS WORKED!!!
    // 174-37-247-84*CLI> core show channels concise
    // SIP/207-00000f5a!from-internal!!1!Up!AppDial!(Outgoing Line)!207!!3!209!Local/207@sugarsip-ca35;2!1333295931.5214
    // Local/207@sugarsip-ca35;2!sugarsip!207!3!Up!Dial!SIP/207,,t!+14102152497!!3!214!SIP/207-00000f5a!1333295927.5213
    // Local/207@sugarsip-ca35;1!sugarsip!!1!Up!AppDial!(Outgoing Line)!207!!3!214!SIP/Flowroute-00000f59!1333295927.5212
    // SIP/Flowroute-00000f59!macro-dial!s!7!Up!Dial!Local/207@sugarsip/n,"",tr!+14102152497!!3!223!Local/207@sugarsip-ca35;1!1333295918.5211
    //$cmd ="ACTION: Redirect\r\nChannel: SIP/Flowroute-00000f59\r\nContext: from-internal\r\nExten: 208\r\nPriority: 1\r\n\r\n";
    //SendAMICommand($cmd);
    // At this point we should also update the channel in database
}

function blockNumber($number, $description) {
    $e164_number = formatPhoneNumberToE164($number);
    $description = trim($description);
    $cmd = "ACTION: DBPut\r\nFamily: blacklist\r\nKey: {$e164_number}\r\nValue: {$description}\r\n\r\n";
    SendAMICommand($cmd);
}

function getCalls($mod_strings) {
    $result_set = get_calls();
    $response = build_item_list($result_set, $GLOBALS['current_user'], $mod_strings);
    // print out json
    $response_array = array();
    if (count($response) == 0) {
        print json_encode(array("."));
    } else {
        foreach ($response as $call) {

            $response_array[] = $call;
        }
        print json_encode($response_array);
    }
    sugar_cleanup();
}

// HELPER FUNCTIONS

/**
* Logs in, Sends the AMI Command Payload passed as a parameter, then logs out.
* results of the command are "echo"ed and show up in ajax response for debugging.
*
* @param string $response AMI Command
*
* @param string $status
*
*/
function SendAMICommand($amiCmd, &$status = true) {
    $server = $GLOBALS['sugar_config']['asterisk_host'];
    $port = (int) $GLOBALS['sugar_config']['asterisk_port'];
    $Username = "Username: " . $GLOBALS['sugar_config']['asterisk_user'] . "\r\n";
    $Secret = "Secret: " . $GLOBALS['sugar_config']['asterisk_secret'] . "\r\n";

    $socket = fsockopen($server, $port, $errno, $errstr, 20);

    if (!$socket) {
        echo "couldn't connect ($errno): $errstr <br>\n";
    } else {
        // log on to Asterisk
        fputs($socket, "Action: Login\r\n");
        fputs($socket, $Username);
        fputs($socket, $Secret);
        fputs($socket, "\r\n");

        $response = ReadResponse($socket);
        echo "Login Response: \n";
        echo $response;
        $status = $status && WasAmiCmdSuccessful($response);

        if ($status) {
            fputs($socket, $amiCmd);
            $response = ReadResponse($socket);
            echo "\nAMI Comand Response: \n";
            echo $response;
            $status = $status && WasAmiCmdSuccessful($response);

            fputs($socket, "Action: Logoff\r\n\r\n");
            fputs($socket, "\r\n");

            $response = ReadResponse($socket);
            echo "\nLogout Response: \n";
            echo $response;
            // Don't really care if logoff was successful;
            //$status = $status && WasAmiCmdSuccessful( $response );
        }
        sleep(1);
        fclose($socket);
    }
}

/**
* Check if AMI Command Was Successful
*
* @param object $response AMI Response
*
* @return string Success resonse
*/
function WasAmiCmdSuccessful($response) {
    return preg_match('/.*Success.*/s', $response);
}

/**
* Read the socket response
*
* @param object $socket Socket
*
* @return array Array of socket responses
*/
function ReadResponse($socket) {
    $retVal = '';

    // Sets timeout to 1/2 a second
    stream_set_timeout($socket, 0, 500000);
    while (($buffer = fgets($socket, 20)) !== false) {
        $retVal .= $buffer;
    }
    return $retVal;
}

/**
* GET the description to save to the memo box
*
* @param call - call object
* @param $direction - call direction (Should be either "Outbound" or "Inbound"
*
* @return array Array of socket responses
*/
function getMemoName($call, $direction, $phone_number) {

    //set the proper abbreviation
    if ($direction == "Outbound") {
        $directionAbbr = $GLOBALS['sugar_config']['asterisk_call_subject_outbound_abbr'];
    }

    if ($direction == "Inbound") {
        $directionAbbr = $GLOBALS['sugar_config']['asterisk_call_subject_inbound_abbr'];
    }

    //set the description
    if (strlen($call->description) > 0) {
        $name = $directionAbbr . $call->description;
    } else {
        $name = $directionAbbr . ": " . $phone_number; // default subject =  IBC: <Phone Number>
    }

    //check the length of the description
    if (strlen($name) > $GLOBALS['sugar_config']['asterisk_call_subject_max_length']) {
        $substrLen = $GLOBALS['sugar_config']['asterisk_call_subject_max_length'] - (strlen($directionAbbr) + strlen("...") + 1);
        $name = $directionAbbr . substr($call->description, 0, $substrLen) . "...";
    }

    return $name;
}

/**
* GET list of calls from the database
*
* @param object $current_user SugarCRM current_user object allows DB access
*
* @return array Array of calls from database
*/
function get_calls() {
    $last_hour = date('Y-m-d H:i:s', time() - 1 * 60 * 30);
    $current_users_ext = $GLOBALS['current_user']->asterisk_ext_c;

    $availableExtensionsArray = explode(',', $current_users_ext);

    // Old version of query, V3 optimized performance by removing everything from asterisk_log that was expired.
    //    $query = " SELECT * FROM asterisk_log WHERE \"$last_hour\" < timestamp_call AND (uistate IS NULL OR uistate != \"Closed\") AND (callstate != 'NeedID') AND (";
    $query = " SELECT * FROM asterisk_log WHERE (";

    if (count($availableExtensionsArray) == 1) {
        $query .= " user_extension = '$current_users_ext'";
    } else {
        $queryExtensionsArray = array();
        foreach ($availableExtensionsArray as $singleExtension) {
            array_push($queryExtensionsArray, " user_extension = '$singleExtension'");
        }
        $query .= implode(' OR ', $queryExtensionsArray);
    }
    $query .=") AND (uistate IS NULL OR uistate != \"Closed\")";


   // log_entry("getCalls Query: " . $query . "\n\n", "c:/callListener.txt");

    $result_set = $GLOBALS['current_user']->db->query($query, false);
    if ($GLOBALS['current_user']->db->checkError()) {
        trigger_error("checkForNewStates-Query failed: $query");
    }
    return $result_set;
}

/**
* Build the item list
*
* @param array $result_set Array of calls from database
* @param object $current_user SugarCRM current_user object allows DB access
* @param array $mod_strings SugarCRM module strings
*
*/
function build_item_list($result_set, $current_user, $mod_strings) {

    $response = array();
    while ($row = $current_user->db->fetchByAssoc($result_set)) {

        $state = get_call_state($row, $mod_strings);
        $phone_number = get_callerid($row);
        $call_direction = get_call_direction($row, $mod_strings);
        $contacts = get_contact_information($phone_number, $row, $current_user);

        // If only one contact is returned, we set db column so we don't reperform expensive phone number lookup qry anymore
        if( empty( $row['contact_id'] ) && count($contacts) == 1 ) {
            // log_entry("Updating db, " . $row['call_record_id'] . "  contact:" . $contacts[0]['contact_id'] . "\n", "c:/callListener.txt");
            setContactID($row['call_record_id'], $contacts[0]['contact_id'] );
        }

        $call = array(
            'id' => $row['id'],
            'asterisk_id' => $row['asterisk_id'],
            'state' => $state,
            'is_hangup' => $state == $mod_strings['YAAI']['HANGUP'],
            'call_record_id' => $row['call_record_id'],
            'phone_number' => $phone_number,
            'asterisk_name' => $row['callerName'],
            'timestamp_call' => $row['timestamp_call'],
            'title' => get_title($contacts, $phone_number, $state, $mod_strings),
            'contacts' => $contacts,
            'call_type' => $call_direction['call_type'],
            'direction' => $call_direction['direction'],
            'duration' => get_duration($row),
            'mod_strings' => $mod_strings['YAAI']
        );

        $response[] = $call;
    }

    return $response;
}

/**
* GET the call state
*
* @param array $row Results from database call in build_item_list
*
* @return string state of call
*/
function get_call_state($row, $mod_strings) {
    $state = isset($mod_strings[strtoupper($row['callstate'])]) ? $mod_strings[strtoupper($row['callstate'])] : $row['callstate'];

    return $state;
}

/**
* GET the callerid
*
* @param array $row Results from database call in build_item_list
*
* @return array Returns the whole item array
*/
function get_callerid($row) {
    $callPrefix = get_call_prefix($row);

    $tmpCallerID = trim($row['callerID']);
    if ((strlen($callPrefix) > 0) && (strpos($tmpCallerID, $callPrefix) === 0)) {
        $tmpCallerID = substr($tmpCallerID, strlen($callPrefix));
    }

    return $tmpCallerID;
}

/**
* GET the prefix of the call
*
* @param array $row Results from database call in build_item_list
*
* @return array Returns the call prefix
*/
function get_call_prefix($row) {
    $calloutPrefix = $GLOBALS['sugar_config']['asterisk_prefix'];
    $callinPrefix = $GLOBALS['sugar_config']['asterisk_dialinPrefix'];

    if ($row['direction'] == 'I') {
        $callPrefix = $callinPrefix;
    }
    if ($row['direction'] == 'O') {
        $callPrefix = $calloutPrefix;
    }

    return $callPrefix;
}

/**
* GET the call direction
*
* @param array $row Results from database call in build_item_list
*
* @return array Returns the whole item array
*/
function get_call_direction($row, $mod_strings) {
    $result = array();

    if ($row['direction'] == 'I') {
        $result['call_type'] = $mod_strings['YAAI']['ASTERISKLBL_COMING_IN'];
        $result['direction'] = "Inbound";
    }

    if ($row['direction'] == 'O') {
        $result['call_type'] = $mod_strings['YAAI']['ASTERISKLBL_GOING_OUT'];
        $result['direction'] = "Outbound";
    }

    return $result;
}

function gmstrtotime($my_time_string) {
    return(strtotime($my_time_string . " UTC"));
}

/**
* GET the call duration
*
* @param array $row Results from database call in build_item_list
*
* @return array Returns the whole item array
*/
function get_duration($row) {
    if (!empty($row['timestamp_hangup'])) {
        $to_time = gmstrtotime($row['timestamp_hangup']);
    } else {
        $to_time = time();
    }

    if( !empty($row['timestamp_link'])) {
        $from_time = gmstrtotime($row['timestamp_link']);
    }
    else {
        $from_time = gmstrtotime($row['timestamp_call']);
    }

    $duration = number_format(round(abs($to_time - $from_time) / 60, 1), 1);

    return $duration;
}

/**
* GET contacts array
*
* @param array $row Results from database call in build_item_list
*
* @return array Returns the whole item array
*/
function get_contact_information($phone_number, $row, $current_user) {
    $innerResultSet = fetch_contacts_associated_to_phone_number($phone_number, $row, $current_user);

    $contacts = get_contacts($innerResultSet, $current_user, $row);

    return $contacts;
}

/**
* GET contacts from database
*
* @param array $innerResultSet Results from function fetch_contacts_associated_to_phone_number
*
* @param object $current_user Global current_user object - allows db access
*
* @param array $row Results from database call in build_item_list
*
* @return array Returns contacts
*/
function get_contacts($innerResultSet, $current_user, $row) {
    $contacts = array();

    while ($contactRow = $current_user->db->fetchByAssoc($innerResultSet)) {
        $contact = array(
            'contact_id' => $contactRow['contact_id'],
            'contact_full_name' => $contactRow['first_name'] . " " . $contactRow['last_name'],
            'company' => $contactRow['account_name'],
            'company_id' => $contactRow['account_id']
        );

        $contacts[] = $contact;
    }



    return $contacts;
}

function fetch_contacts_associated_to_phone_number($phoneToFind, $row, $current_user) {
    global $sugar_config;

    $phoneToFind = ltrim($phoneToFind, '0');
    $phoneToFind = preg_replace('/\D/', '', $phoneToFind); // Removes and non digits such as + chars.

    // TODO make the '7' below a configurable parameter... some may prefer to match on 10.
    if (preg_match('/([0-9]{7})$/', $phoneToFind, $matches)) {
        $phoneToFind = $matches[1];
    }

    if (strlen($phoneToFind) > 5) {

       // TODO fix the join so that account is optional... I think just add INNER
        // REMOVED: phone_work, phone_home, phone_mobile, phone_other,
        $selectPortion = "SELECT c.id as contact_id, first_name, last_name, a.name as account_name, account_id "
                . " FROM contacts c "
                . " join contacts_cstm on c.id = contacts_cstm.id_c"
                . " left join accounts_contacts ac on (c.id=ac.contact_id) and (ac.deleted='0' OR ac.deleted is null)"
                . " left join accounts a on (ac.account_id=a.id) and (a.deleted='0' or a.deleted is null)";

        if ($row['contact_id']) {
            // log_entry("Quick where query\n", "c:/callListener.txt");
            $wherePortion = " WHERE c.id='{$row['contact_id']}' and c.deleted='0'";
        }
        // We only do this expensive query if it's not already set!
        else {
            log_entry("Performing Expensive where query\n", "c:/callListener.txt");

            $phoneFields = array();
            // Here we add any custom contact fields.
            if( !empty($sugar_config['asterisk_contact_phone_fields']) ) {
                $customPhoneFields = explode(',', $sugar_config['asterisk_contact_phone_fields'] );
                foreach ($customPhoneFields as $currCol) {
                    array_push($phoneFields, sql_replace_phone_number($currCol, $phoneToFind) );
                }
            }

            $phoneFieldsWherePortion = implode(' OR ', $phoneFields);

            $wherePortion = " WHERE (" . $phoneFieldsWherePortion . ") and c.deleted='0'";
           // log_entry("Where == " . $wherePortion, "c:/callListener.txt");

        }

        $queryContact = $selectPortion . $wherePortion;
        //log_entry("QUERY: $queryContact\n","c:/callListener.txt");
        return $current_user->db->query($queryContact, false);
    }
}

/**
 * returns a formatted string for inserting into a WHERE clause.
 *
 * @param $column_name
 * @param $phoneNumber
 * @return string
 */
function sql_replace_phone_number($column_name, $phoneNumber) {
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
    return sprintf($sqlReplace, $column_name, $phoneNumber);
}

/**
* GET the opencnam callerid information
*
* @param array $row Results from database call in build_item_list
*
* @param object $current_user Global current_user object - allows db access
*
* @return array $callerid Returns the callerid information
*
* @todo implement a number cleaner that always formats input into 10 digits
*/
function get_open_cnam_result($row, $current_user) {
    require_once 'opencnam.php';
    // Check OpenCNAM if we don't already have the Company Name in Sugar.
    if (!isset($found['company']) && $GLOBALS['sugar_config']['asterisk_opencnam_enabled'] == "true") {
        if ($row['opencnam'] == NULL) {
            $opencnam = new opencnam();
            $tempCnamResult = $opencnam->fetch(get_callerid($row));
            $tempCnamResult = preg_replace('/[^a-z0-9\-\. ]/i', '', $tempCnamResult);
            $tempCallRecordId = preg_replace('/[^a-z0-9\-\. ]/i', '', $row['call_record_id']);
            $cnamUpdateQuery = "UPDATE asterisk_log SET opencnam='$tempCnamResult' WHERE call_record_id='$tempCallRecordId'";
            $current_user->db->query($cnamUpdateQuery, false);
            $callerid = $tempCnamResult;
        }
    }
    return $callerid;
}

/**
* GET the title of the call
*
* @param string $full_name Full name of
*
* @param string $phoneNumber 10 digit US telephone number
*
* @return string title
*
* title changes based on whether there are 1) multiple matches found 2) single match found 3) no matches found
*/
function get_title($contacts, $phone_number, $state, $mod_strings) {

    switch (count($contacts)) {
        case 0:
            $title = $phone_number;
            break;

        case 1:
            $title = $contacts[0]['contact_full_name'];
            break;

        default:
            $title = $mod_strings['YAAI']['ASTERISKLBL_MULTIPLE_MATCHES'];
            break;
    }
    $title = $title . " - " . $state;

    // Limit title length (to prevent X from overflowing)
    // TODO: handle this with CSS instead
    if(strlen($title) > 24) {
        $title = substr($title,0,24) . "...";
    }


    return $title;
}

/**
* Helper method for turning any number into an e164 number
*
* @param string $number The number you want to convert
*/
function formatPhoneNumberToE164($number) {

    // get rid of any non (digit, + character)
    $phone = preg_replace('/[^0-9+]/', '', $number);

    // validate intl 10
    if (preg_match('/^\+([2-9][0-9]{9})$/', $phone, $matches)) {
        return "+{$matches[1]}";
    }

    // validate US DID
    if (preg_match('/^\+?1?([2-9][0-9]{9})$/', $phone, $matches)) {
        return "+1{$matches[1]}";
    }

    // validate INTL DID
    if (preg_match('/^\+?([2-9][0-9]{8,14})$/', $phone, $matches)) {
        return "+{$matches[1]}";
    }

    // premium US DID
    if (preg_match('/^\+?1?([2-9]11)$/', $phone, $matches)) {
        return "+1{$matches[1]}";
    }
}

/**
* Helper method for logging
*
* @param string $str The string you want to log
* @param string $file The log file you want to log to
*/
function log_entry($str, $file = "default") {
    $handle = fopen($file, 'a');
    fwrite($handle, "[" . date('Y-m-j H:i:s') . "] " . $str);
    fclose($handle);
}

/**
* Helper method for converting print_r into a nicely formated string for logging
*
* @param string $str The string you want to log
*
* @return string The string of the array data you want to print
*/
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