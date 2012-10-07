<?php

/***
 * Author: Blake Robertson
 *
 * Controller class for various AJAX things such as saving the UI state and saving the call details. 
 *
 * TODO: callListener and createCall should be refactored into this php file and then called by specifying an appropriate action for them.
 */



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/utils.php');
require_once('include/export_utils.php');

global $sugar_config;
global $locale;
global $current_user;


if(!defined('sugarEntry'))define('sugarEntry', true);

require_once('modules/Calls/Call.php');
require_once('modules/Users/User.php');

$INBOUND_CALL_ABBR = $sugar_config['asterisk_call_subject_inbound_abbr']; //"IBC"; // Inbound calls will be prefixed with this in Call Record
$OUTBOUND_CALL_ABBR = $sugar_config['asterisk_call_subject_outbound_abbr']; //"OBC";
$MORE_INDICATOR = "...";	// When memo notes are longer then max length it displays this at the end to indicate the user should open the record for the rest of the notes.
$MAX_CALL_SUBJECT_LENGTH = $sugar_config['asterisk_call_subject_max_length'];; // Set this to the max length you want the subject to be.  MUST BE SMALLER THEN DATABASE COLUMN SIZE which is 50 by default

if( $_REQUEST['action'] == "memoSave") {
	
	/** 
	// DEBUG Stuff
	echo 'description:'. $_POST["description"]; //assuming you defined the column "name" in vardefs.php 
	if( array_key_exists("message",$_POST) )
		echo ', message:'. $_POST["message"]; //assuming you defined the column "name" in vardefs.php 
	echo ', call record id:'. $_POST["call_record"]; //assuming you defined the column "name" in vardefs.php 
	**/

    // Workaround See Discussion here: https://github.com/blak3r/yaai/pull/20
    if( isset( $_POST["call_record"])) {
        $callRecord = $_POST["call_record"];
    }
    else {
        $asteriskID = preg_replace('/-/','.',$_POST['id']);
        $query = " SELECT call_record_id FROM asterisk_log WHERE asterisk_id=\"$asteriskID\"";
        $resultSet = $current_user->db->query($query, false);
        if($current_user->db->checkError()){
            trigger_error("RetrieveCallRecord-Query failed: $query");
        }
        while($row = $current_user->db->fetchByAssoc($resultSet)){
            $callRecord = $row['call_record_id'];
        }
        //log_entry2("Set ID by fetching from db: " . $callRecord, "c:/debug.txt");
    }

	$focus = new Call(); //create your module object wich extends SugarBean
    $focus->retrieve( $_POST["call_record"] ); // retrieve a row by its id

    // TODO there are going to be language issues in this file... replace all strings with modstring equivalents.

	if( array_key_exists("name",$_POST) ) 
		$focus->name=$_POST["name"]; 
	
	$focus->description= $_POST["description"];
	
	$direction = "Outbound";
	if( !empty($_POST['direction']) ) {
		$direction = $_POST['direction'];
	}
	
	$directionAbbr = $OUTBOUND_CALL_ABBR;
	if( $direction == "Inbound" ) {
		$directionAbbr = $INBOUND_CALL_ABBR;
	}
	
	$subject = "$direction Call"; // default subject
	
	// Set subject to include part of memo if notes were left.
	if( strlen($focus->description) > 0 ) {
		$subject = $directionAbbr . $focus->description;
		if( strlen($subject) > $MAX_CALL_SUBJECT_LENGTH ) {
			//$subject = $direction . " Call (w/ notes attached)";
			$substrLen = $MAX_CALL_SUBJECT_LENGTH - (strlen($directionAbbr) + strlen($MORE_INDICATOR) + 1);
			$subject = $directionAbbr . substr($focus->description,0,$substrLen) . $MORE_INDICATOR;		
		}
	}	
	
	$focus->name = $subject;		
	$focus->save();
}
else if( $_REQUEST['action'] == "updateUIState" ) {
	$current_language = $_SESSION['authenticated_user_language'];
	if(empty($current_language)) {
		$current_language = $sugar_config['default_language'];
	}
	require("custom/modules/Asterisk/language/" . $current_language . ".lang.php");
	$cUser = new User();
	$cUser->retrieve($_SESSION['authenticated_user_id']);

	// query log
	// Very basic santization
	$uiState = preg_replace('/[^a-z0-9\-\. ]/i', '', $_REQUEST['ui_state']); //  mysql_real_escape_string($_REQUEST['ui_state']);
    $callRecord = preg_replace('/[^a-z0-9\-\. ]/i', '', $_REQUEST['call_record']); //mysql_real_escape_string($_REQUEST['call_record']);
    $asteriskID = preg_replace('/-/','.',$_REQUEST['id']);
    // Workaround See Discussion here: https://github.com/blak3r/yaai/pull/20
    if (isset($_REQUEST['call_record'])){
        $query = "update asterisk_log set uistate=\"$uiState\" where call_record_id=\"$callRecord\"";
    }
    else  {
        $query = "update asterisk_log set uistate=\"$uiState\" where asterisk_id=\"$asteriskID\"";
    }

	$resultSet = $cUser->db->query($query, false);
	if($cUser->db->checkError()) {
		trigger_error("Update UIState-Query failed: $query");
	}
}
else if( $_REQUEST['action'] == "setContactId" ) {
    $current_language = $_SESSION['authenticated_user_language'];
    if(empty($current_language)) {
        $current_language = $sugar_config['default_language'];
    }
    require("custom/modules/Asterisk/language/" . $current_language . ".lang.php");
    $cUser = new User();
    $cUser->retrieve($_SESSION['authenticated_user_id']);

    // Very basic sanitization
    $contactId = preg_replace('/[^a-z0-9\-\. ]/i', '', $_REQUEST['contact_id']);   // mysql_real_escape_string($_REQUEST['ui_state']);
    $callRecord = preg_replace('/[^a-z0-9\-\. ]/i', '', $_REQUEST['call_record']); // mysql_real_escape_string($_REQUEST['call_record']);
    $asteriskID = preg_replace('/-/','.',$_REQUEST['id']);
    // Workaround See Discussion here: https://github.com/blak3r/yaai/pull/20
    if (isset($_REQUEST['call_record'])){
        $query = "update asterisk_log set contact_id=\"$contactId\" where call_record_id=\"$callRecord\"";
    }
    else  {
        $query = "update asterisk_log set contact_id=\"$contactId\" where asterisk_id=\"$asteriskID\"";
    }

    $resultSet = $cUser->db->query($query, false);
    if($cUser->db->checkError()) {
        trigger_error("Update setContactId-Query failed: $query");
    }

    // Adds the new relationship!  (This must be done here in case the call has already been hungup as that's when asteriskLogger sets relations)
    $focus = new Call();
    $focus->retrieve($callRecord);
    $focus->load_relationship('contacts');
    // Remove any contacts already associated with call (if there are any)
    foreach ($focus->contacts->getBeans() as $contact) {
        $focus->contacts->delete($callRecord,$contact->id);
    }
    $focus->contacts->add($contactId); // Add the new one!
    $contactBean = new Contact();
    $contactBean->retrieve($contactId);
    $focus->parent_id = $contactBean->account_id;
    $focus->parent_type = "Accounts";
    $focus->save();
}
else if( $_REQUEST['action'] == "call") {

// TODO: For some reason this code isn't working... I think it's getting the extension.
// For the time being, callCreate is still being used.	

/*
	$cUser = new User();
	$cUser->retrieve($_SESSION['authenticated_user_id']);
	$extension = $cUser->asterisk_ext_c;
	
	//$extension = $current_user->asterisk_ext_c;
	$context = $sugar_config['asterisk_context'];

	// Take the user supplied pattern, we find the part with the #'s (which are the ext)... then we get something like 
	// asterisk_dialout_channel == "SIP/###"   --> $matches[1] == SIP/, $matches[2] == "###", $matches[3] is "".
	// asterisk_dialout_channel == "Local/###@sugarsip/n"   --> $matches[1] == Local/, $matches[2] == "###", $matches[3] is "@sugarsip/n".
	preg_match('/([^#]*)(#+)([^#]*)/',$sugar_config['asterisk_dialout_channel'],$matches);
	$channel = $matches[1] . $extension . $matches[3];
	
	//format Phone Number
	$number = $_REQUEST['phoneNr'];
	$prefix = $sugar_config['asterisk_prefix'];
	$number = str_replace("+", "00", $number);
	$number = str_replace(array("(", ")", " ", "-", "/", "."), "", $number);
	$number = $prefix.$number;


	// dial number
	$cmd = "";
	$cmd .=  "Action: originate\r\n";		
	$cmd .=  "Channel: ". $channel ."\r\n";	
	$cmd .=  "Context: ". $context ."\r\n";		
	$cmd .=  "Exten: " . $number . "\r\n";		
	$cmd .=  "Priority: 1\r\n";		
	$cmd .=  "Callerid:" . $_REQUEST['phoneNr'] ."\r\n";	
	$cmd .=  "Variable: CALLERID(number)=" . $extension . "\r\n\r\n";
	
	SendAMICommand($cmd);
*/
		
}
else if( $_REQUEST['action'] == "transfer" ) {
	
	$exten = preg_replace( '/\D/', '', $_POST["extension"]); // removes anything that isn't a digit.
	if( empty($exten) ) {
		echo "ERROR: Invalid extension";
	}
	
	$callRecord = preg_replace('/[^a-z0-9\-\. ]/i', '', $_POST["call_record"]);
	$query = "Select remote_channel from asterisk_log where call_record_id='$callRecord'";
	
	$resultSet = $current_user->db->query($query, false);
	if($current_user->db->checkError()){
		trigger_error("Find Remote Channel-Query failed: $query");
	}

	while($row = $current_user->db->fetchByAssoc($resultSet)){
		$context = $sugar_config['asterisk_context'];
		$cmd ="ACTION: Redirect\r\nChannel: {$row['remote_channel']}\r\nContext: $context\r\nExten: $exten\r\nPriority: 1\r\n\r\n";
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
else {
	echo "Undefined Action";
}


/// Logs in, Sends the AMI Command Payload passed as a parameter, then logs out.
/// results of the command are "echo"ed and get show up in ajax response for debugging.
function SendAMICommand( $amiCmd, &$status=true) {
	global $sugar_config;
	$server = $sugar_config['asterisk_host'];
	$port = (int)$sugar_config['asterisk_port'];
	$Username = "Username: " . $sugar_config['asterisk_user'] . "\r\n";
	$Secret = "Secret: " . $sugar_config['asterisk_secret'] . "\r\n";
	$context = $sugar_config['asterisk_context'];

	$socket = fsockopen($server, $port, $errno, $errstr, 20);
	
	if (!$socket) {
		echo "couldn't connect ($errno): $errstr <br>\n";		
	} 
	else {
		// log on to Asterisk
		fputs($socket, "Action: Login\r\n"); 
		fputs($socket, $Username); 
		fputs($socket, $Secret);
		fputs($socket, "\r\n");	

		$response = ReadResponse($socket);
		echo "Login Response: \n";
		echo $response;
		$status = $status && WasAmiCmdSuccessful( $response );
		
		if( $status ) {
			fputs($socket,$amiCmd);
			$response = ReadResponse($socket);			
			echo "\nAMI Comand Response: \n";
			echo $response;
			$status = $status && WasAmiCmdSuccessful( $response );			
		
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

function WasAmiCmdSuccessful($response) {
	return preg_match('/.*Success.*/s',$response);
}

function ReadResponse($socket) {
	$retVal = '';
	
	// Sets timeout to 1/2 a second
	stream_set_timeout($socket,0,500000);
	while (($buffer = fgets($socket, 20)) !== false) {
		$retVal .= $buffer;
	}
	return $retVal;
}

// just for debugging purposes
function log_entry2( $str, $file = "default" ) {
    $handle = fopen($file, 'a');
    fwrite($handle, "[" . date('Y-m-j H:i:s') . "] " . $str );
    fclose($handle);
}


?>