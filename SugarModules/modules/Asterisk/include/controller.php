<?php

/***
 * Author: Blake Robertson
 *
 * Controller class for various AJAX things such as saving the UI state and saving the call details. 
 *
 * TOODO: callListener and createCall should be refactored into this php file and then called by specifying an appropriate action for them.
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

// These variables in a perfect world would be asterisk configuration
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

	$focus = new Call(); //create your module object wich extends SugarBean 
	$focus->retrieve( $_POST["call_record"] ); // retrieve a row by its id
	
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
	$query = "update asterisk_log set uistate=\"$uiState\" where call_record_id=\"$callRecord\"";  

	$resultSet = $cUser->db->query($query, false);
	if($cUser->db->checkError()) {
		trigger_error("Update UIState-Query failed: $query");
	}
}
else if( $_REQUEST['call'] == "call") {
	/*
		//format Phone Number
		$number = $_REQUEST['phoneNr'];
		$prefix = $sugar_config['asterisk_prefix'];
		$number = str_replace("+", "00", $number);
		$number = str_replace(array("(", ")", " ", "-", "/", "."), "", $number);
		$number = $prefix.$number;
		var_dump($number);
		
				// dial number
		fputs($socket, "Action: originate\r\n");		
		fputs($socket, "Channel: ". $channel ."\r\n");	
		fputs($socket, "Context: ". $context ."\r\n");		
		fputs($socket, "Exten: " . $number . "\r\n");		
		fputs($socket, "Priority: 1\r\n");		
		fputs($socket, "Callerid:" . $_REQUEST['phoneNr'] ."\r\n");	
		fputs($socket, "Variable: CALLERID(number)=" . $extension . "\r\n");
		*/
}

else if( $_REQUEST['action'] == "transfer" ) {

	$query = "Select remote_channel from asterisk_log where call_record_id='{$_POST["call_record"]}'";
	
	$resultSet = $current_user->db->query($query, false);
	if($current_user->db->checkError()){
		trigger_error("Find Remote Channel-Query failed: $query");
	}

	while($row = $current_user->db->fetchByAssoc($resultSet)){
		// FIXME destination extension is hardcodeded.
		$cmd ="ACTION: Redirect\r\nChannel: {$row['remote_channel']}\r\nContext: from-internal\r\nExten: 208\r\nPriority: 1\r\n\r\n";
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


function SendAMICommand( $amiCmd ) {
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
		//$result = fgets($socket,128);
		
		
		echo "Login Response: \n";
		while (($buffer = fgets($socket, 128)) !== false) {
			echo $buffer;
		}
		
		fputs($socket,$amiCmd);
		//$resultCmd = fgets($socket,128);
		
		echo "\nAMI Comand Response: \n";
		while (($buffer = fgets($socket, 128)) !== false) {
			echo $buffer;
		}
		
		fputs($socket, "Action: Logoff\r\n\r\n");
		fputs($socket, "\r\n");	
		
		echo "\nLogout Response: \n";
		while (($buffer = fgets($socket, 128)) !== false) {
			echo $buffer;
		}
			
		//echo $result;
		//var_dump($result);
		//var_dump($resultCmd);
		//var_dump($channel);
		//var_dump($context);
		//var_dump($number);
		sleep(1);
		
		// close socket
		fclose($socket);
	}
}


?>