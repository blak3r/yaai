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
else {
	echo "Undefined Action";
}


?>