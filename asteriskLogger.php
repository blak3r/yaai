<?php
/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * Parts of this code are (c) 2011 Blake Robertson http://www.blakerobertson.com
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
//
// Debug flags
//
$mysql_loq_queries = 1;
$mysql_log_results = 1;

//
// Say hello, setup include path(s)
//
define('sugarEntry', TRUE);
logLine( "******** Asterisk Logger Starting **************\n");

print "**** asteriskLogger ****\n";
print "# Version \$Id: asteriskLogger.php 180 2009-06-12 10:16:16Z andreas \$\n";

// Determine SugarCRM's root dir (we'll use this to find the config filez
$scriptRoot = dirname(__FILE__);
$sugarRoot  = $scriptRoot . "/../../../";
print "# Sugar root set to [$sugarRoot]\n";
//set_include_path(get_include_path() . PATH_SEPARATOR . $sugarRoot . "include");
//print "# PHP include path set to [" . get_include_path() . "]\n";

// Specify a custom sugar root on commandline when in test mode (I used this to debug from eclipse)
if( $argc > 2 && $argv[1] == "test" ) {
	$sugarRoot = $argv[2];
	print "New sugar root is: " . $sugarRoot;
}


//
// Required libraries
//
require_once($sugarRoot . "include/nusoap/nusoap.php");


// chdir is the root of all evil :-}
// chdir("../");

//class to check if a soap call failed(session time-out), if it did reconnect and execute the call again.
class SugarSoap extends nusoapclient
{
    public $sessionid;
    public $auth_array;
    
    function __construct($endpoint, $something, $auth_array)
    {
        $this->auth_array = $auth_array;
        parent::__construct($endpoint, $something);
        $this->login();
    }
    
    
    function login()
    {
        $result          = parent::call('login', $this->auth_array);
        $this->sessionid = $result['id'];
        //print_r($result);
        return ($result);
    }
    
    function call($method, $params)
    {
        $params['session'] = $this->sessionid;
        $result            = parent::call($method, $params);
        if (is_array($result) && array_key_exists("error", $result) && $result['error']['number'] != 0) {
            $this->login();
            $result = parent::call($method, $params);
            
        }
        //print_r($result);
        return ($result);
        
    }
}

//
// Pull in config file(s)
//
require_once($sugarRoot . 'config.php');
include_once($sugarRoot . 'config_override.php');

logLine( "Logging to: " . $sugar_config['asterisk_log_file'] . "\n");
$asteriskServer        = $sugar_config['asterisk_host'];
$asteriskManagerPort   = (int) $sugar_config['asterisk_port'];
$asteriskUser          = "Username: " . $sugar_config['asterisk_user'] . "\r\n";
$asteriskSecret        = "Secret: " . $sugar_config['asterisk_secret'] . "\r\n";
$asteriskMatchInternal = $sugar_config['asterisk_expr'];

// Fetch Asterisk dialprefix - must strip this from inbound callerIDs if set
$calloutPrefix = isset($sugar_config['asterisk_prefix']) ? $sugar_config['asterisk_prefix'] : "";
echo ("# Callout prefix is [$calloutPrefix]\n");

$callinPrefix = isset($sugar_config['asterisk_dialinPrefix']) ? $sugar_config['asterisk_dialinPrefix'] : "+1";
echo ("# Callin prefix is [$callinPrefix]\n");
echo ("asteriskMatchInternal = $asteriskMatchInternal\n");


print "# (Config processed)\n";


//
// Connect to mySQL DB
//
$sql_connection = mysql_connect($sugar_config['dbconfig']['db_host_name'], $sugar_config['dbconfig']['db_user_name'], $sugar_config['dbconfig']['db_password']);
$sql_db         = mysql_select_db($sugar_config['dbconfig']['db_name']);
// Prune asterisk_log
// Note only use this for development
//mysql_query('DELETE FROM asterisk_log');


// Get SOAP config
$sugarSoapEndpoint   = $sugar_config['site_url'] . "/soap.php";
$sugarSoapUser       = $sugar_config['asterisk_soapuser'];
$sugarSoapCredential = ""; {
    $sql       = "select user_hash from users where user_name='$sugarSoapUser'";
    $sqlResult = mysql_query($sql);
    if ($sqlResult) {
        $rowData             = mysql_fetch_assoc($sqlResult);
        $sugarSoapCredential = $rowData['user_hash'];
    } else {
        echo "! FATAL: Cannot find login credentials for user $sugarSoapUser\n";
        die();
    }
}


//
// And finally open a SOAP connection to SugarCRM
//
echo ("! Trying SOAP login endpoint=[$sugarSoapEndpoint] user=[$sugarSoapUser] password=[$sugarSoapCredential]\n");

$auth_array = array(
    'user_auth' => array(
        'user_name' => $sugarSoapUser,
        'password' => $sugarSoapCredential
    )
);
$soapClient = new SugarSoap($sugarSoapEndpoint . '?wsdl', true, $auth_array);

//$soapLogin = $soapClient->call('login', $auth_array);
//var_dump($soapLogin);
$soapSessionId = $soapClient->sessionid;
//var_dump($soapSessionId);
$userGUID      = $soapClient->call('get_user_id', array(
    $soapSessionId
));
//var_dump($userGUID);
print "! Successful SOAP login id=" . $soapSessionId . " user=" . $auth_array['user_auth']['user_name'] . " GUID=" . $userGUID . "\n";
//die;




if( $argc > 1 && $argv[1] == "test" ) {
	// MODIFY these variables to help diagnose misc incompatibilities between your configuration / sugarcrm edition.
	$ext1 = 51;
	$ext2 = 52;
	//$extensions = (51,52,207);
	$phones = array('4102152497','sdfasdf','(267) 222-8385','2672228385' );
	
	print "Entered test mode!";
	
	$obj = findSugarObjectByPhoneNumber("4102152497");
	print "findUserByAsteriskExtension(52) returned: " . findUserByAsteriskExtension("52") . "\n";
	print "findUserByAsteriskExtension(207) returned: " . findUserByAsteriskExtension("207") . "\n";
	print "findUserByAsteriskExtension(710) returned: " . findUserByAsteriskExtension('710') . "\n";
	findUserByAsteriskExtension('206');

	//foreach( $extensions as $ext ) {
		
//	}

exit;

	foreach( $phones as $currPhone ) {
		$obj = findSugarObjectByPhoneNumber($currPhone);
		if( $obj === FALSE ) {
			print "!!! DIDN'T FIND OBJ for $currPhone\n";
		}
		else {
			print "  For $currPhone I found: ". $obj['values']['id'] . ' ' . $obj['values']['first_name'] . ' '. $obj['values']['last_name'] . "\n";
		}
	}
	
	
	exit;
}






// BR: Added this while loop to keep loging in to AMI if asterisk goes down.
while (true) {
    // connect to Asterisk server
    $amiSocket = fsockopen($asteriskServer, $asteriskManagerPort, $errno, $errstr, 5);
    if (!$amiSocket) {
        echo "! Error with socket";
        die("Error connecting $errno $errstr\r\n");
    } else {
        echo "# Successfully opened socket connection to $asteriskServer:$asteriskManagerPort\n";
    }
    
    
    fputs($amiSocket, "Action: Login\r\n");
    fputs($amiSocket, $asteriskUser);
    fputs($amiSocket, $asteriskSecret);
    fputs($amiSocket, "Events: call,hud\r\n\r\n"); // to monitor just call data, for Asterisk Manager 1.0 remove hud
    $result = fgets($amiSocket, 4096);
    echo ("! Login action returned with rc=$result\n");
    $event = '';
    $stack = 0;
    
    $event_started = false;

	$start = NULL;
	$timeout = ini_get('default_socket_timeout');

    // Keep a loop going to read the socket and parse the resulting commands.
    while (!safe_feof($amiSocket, $start) && (microtime(true) - $start) < $timeout) {
        $buffer = fgets($amiSocket, 4096);
        // echo("# Read " . strlen($buffer) . " "  . $buffer . "\n");
        
        if ($buffer == "\r\n") { // handle partial packets
            
            $event_started = false;
            // parse the event and get the result hashtable
            $e             = getEvent($event);
            dumpEvent($e);
            
            
            //
            // Call Event
            //
            if ($e['Event'] == 'Dial' && $e['SubEvent'] != 'End') //Asterisk Manager 1.1 returns 2 dial events with a begin and an end subevent, by excluding the subevent end we won't get multiple records for the same call.
                {
                logLine("! Dial Event src=" . $e['Channel'] . " dest=" . $e['Destination'] . "\n"); //Asterisk Manager 1.1
                //print "! Dial Event src=" . $e['Source'] . " dest=" . $e['Destination'] . "\n"; 	//Asterisk Manager 1.0
                
                //
                // Before we log this Dial event, we create a corresponding object in Calls module.
                // We'll need this later to record the call when finished, but create it right here
                // to get the ID
                //
                
                $set_entry_params = array(
                    'session' => $soapSessionId,
                    'module_name' => 'Calls',
                    'name_value_list' => array(
                        array(
                            'name' => 'name',
                            'value' => '** Automatic record **'
                        ),
                        array(
                            'name' => 'status',
                            'value' => 'In Limbo'
                        ),
                        array(
                            'name' => 'assigned_user_id',
                            'value' => $userGUID
                        )
                    )
                );
                
               
                $soapResult = $soapClient->call('set_entry', $set_entry_params);
                
                $callRecordId = $soapResult['id'];
                logLine("! Successfully created CALL record with id=" . $callRecordId . "\n");
                
                $call = NULL;
                
                $tmpCallerID = trim($e['CallerIDNum']); //Asterisk Manager 1.0 $e['CallerID']
                
                
                // echo("* CallerID is: $tmpCallerID\n");
                if ((strlen($calloutPrefix) > 0) && (strpos($tmpCallerID, $calloutPrefix) === 0)) {
                    echo ("* Stripping callout prefix: $calloutPrefix");
                    $tmpCallerID = substr($tmpCallerID, strlen($calloutPrefix));
                }
                logLine("* CallerID is: $tmpCallerID\n");
                
                if ((strlen($callinPrefix) > 0) && (strpos($tmpCallerID, $callinPrefix) === 0)) {
                    echo ("* Stripping callin prefix: $calloutPrefix");
                    $tmpCallerID = substr($tmpCallerID, strlen($callinPrefix));
                }
                logLine("* CallerID is: $tmpCallerID\n");
                
                
				// TODO for us, Channel --> SIP/209-00000e36 & Destination --> Local/207@sugarsip-1fcd;
		        // Check if both ends of the call are internal (then delete created (** Automatic record **) record)
                if (eregi($asteriskMatchInternal, $e['Channel']) && eregi($asteriskMatchInternal, $e['Destination'])) {
                    $query = "DELETE FROM calls WHERE id='$callRecordId'";
                    mysql_checked_query($query);
                }
                
                //Asterisk Manager 1.1 (If the call is internal, this will be skipped)
                if (eregi($asteriskMatchInternal, $e['Channel']) && !eregi($asteriskMatchInternal, $e['Destination'])) {
                    $query         = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, callstate, direction, CallerID, timestampCall) VALUES('%s','%s','%s','%s','%s','%s',%s)", $e['DestUniqueID'], $callRecordId, $e['Channel'], 'NeedID', 'O', $tmpCallerID, 'NOW()');
                    $callDirection = 'Outbound';
                    logLine("OUTBOUND state detected... $asteriskMatchInternal is astMatchInternal eChannel= " . $e['Channel'] . ' eDestination=' . $e['Destination'] . "\n");
                    
                } else if (!eregi($asteriskMatchInternal, $e['Channel'])) {
                    $query         = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, callstate, direction, CallerID, timestampCall, asterisk_dest_id) VALUES('%s','%s','%s','%s','%s','%s',%s,'%s')", $e['UniqueID'], $callRecordId, $e['Destination'], 'Dial', 'I', $tmpCallerID, 'NOW()', $e['DestUniqueID']);
                    $callDirection = 'Inbound';
                    
                    logLine("Inbound state detected... $astMatchInternal is astMatchInternal eChannel= " . $e['Channel'] . ' eDestination=' . $e['Destination'] . "\n");
                    
                }
                mysql_checked_query($query);
				
				
                //Asterisk Manager 1.0
                
                /*if(eregi($asteriskMatchInternal, $e['Source']))
                {
                $query = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, callstate, direction, CallerID, timestampCall) VALUES('%s','%s','%s','%s','%s','%s',%s)",
                $e['DestUniqueID'],
                $callRecordId,
                $e['Source'],
                'NeedID',
                'O',
                $tmpCallerID,
                'NOW()'
                );
                $callDirection = 'Outbound';
                }
                else{
                $query = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, callstate, direction, CallerID, timestampCall) VALUES('%s','%s','%s','%s','%s','%s',%s)",
                $e['SrcUniqueID'],
                $callRecordId,
                $e['Destination'],
                'Dial',
                'I',
                $tmpCallerID,
                'NOW()'
                );
                $callDirection = 'Inbound';
                }
                mysql_checked_query($query);*/
                
                //
                // Update CALL record with direction...
                //
                $set_entry_params = array(
                    'session' => $soapSessionId,
                    'module_name' => 'Calls',
                    'name_value_list' => array(
                        array(
                            'name' => 'id',
                            'value' => $callRecordId
                        ),
                        array(
                            'name' => 'direction',
                            'value' => $callDirection
                        )
                    )
                );
                
                $soapResult = $soapClient->call('set_entry', $set_entry_params);
                
                echo "Direction is $callDirection \n";
                // TODO: Error checking!
                
            }
            //
            // NewCallerID for Outgoing Call
            //
            //Asterisk Manager 1.1
            if ($e['Event'] == 'NewCallerid') {
                $id          = $e['Uniqueid'];
                $tmpCallerID = trim($e['CallerIDNum']);
                echo ("* CallerID is: $tmpCallerID\n");
                if ((strlen($calloutPrefix) > 0) && (strpos($tmpCallerID, $calloutPrefix) === 0)) {
                    echo ("* Stripping prefix: $calloutPrefix");
                    $tmpCallerID = substr($tmpCallerID, strlen($calloutPrefix));
                }
                echo ("* CallerID is: $tmpCallerID\n");
                // Fetch associated call record
                //$callRecord = findCallByAsteriskId($id);
                $query = "UPDATE asterisk_log SET CallerID='" . $tmpCallerID . "', callstate='Dial' WHERE asterisk_id='" . $id . "'";
                mysql_checked_query($query);
            }
            //Asterisk Manager 1.0
            
            /* if($e['Event'] == 'NewCallerid')
            {
            $id = $e['Uniqueid'];
            $tmpCallerID = trim($e['CallerID']);
            echo("* CallerID is: $tmpCallerID\n");
            if ( (strlen($calloutPrefix) > 0)  && (strpos($tmpCallerID, $calloutPrefix) === 0) )
            {
            echo("* Stripping prefix: $calloutPrefix");
            $tmpCallerID = substr($tmpCallerID, strlen($calloutPrefix));
            }
            echo("* CallerID is: $tmpCallerID\n");
            // Fetch associated call record
            //$callRecord = findCallByAsteriskId($id);
            $query = "UPDATE asterisk_log SET CallerID='" . $tmpCallerID . "', callstate='Dial' WHERE asterisk_id='" . $id . "'";
            mysql_checked_query($query);
            };*/
            
            //
            // Process "Hangup" events
            // Yup, we really get TWO hangup events from Asterisk!
            // Obviously, we need to take only one of them....
            //
            // Asterisk Manager 1.1
            // I didn't get the correct results from inbound calling in relation to the channel that answered, this solves that.
            
            if ($e['Event'] == 'Hangup') {
                $id        = $e['Uniqueid'];
                $query     = "SELECT direction FROM asterisk_log WHERE asterisk_dest_id = '$id' OR asterisk_id = '$id'";
                $result    = mysql_checked_query($query);
                $direction = mysql_fetch_array($result);
                var_dump($direction);
                if ($direction['direction'] == "I") {
                    $callDirection = "Inbound";
                } else {
                    $callDirection = "Outbound";
                }
                if ($callDirection == "Outbound") { //Outbound callhandling                    
                    //
                    // Fetch associated call record
                    //
                    $callRecord = findCallByAsteriskId($id);
                    if ($callRecord) {
					    logLine("FOUND outbound CALL for asterisk id $id\n");
                        //
                        // update entry in asterisk_log...
                        //
                        $rawData      = $callRecord['bitter']; // raw data from asterisk_log																				
                        $query        = sprintf("UPDATE asterisk_log SET callstate='%s', timestampHangup=%s, hangup_cause=%d, hangup_cause_txt='%s' WHERE asterisk_id='%s'", //asterisk_dest_id was asterisk_id
                            'Hangup', 'NOW()', $e['Cause'], $e['Cause-txt'], $id);
                        $updateResult = mysql_checked_query($query);
                        if ($updateResult) {
						    //
                            // Attempt to find assigned user by asterisk ext
                            //
                            $assignedUser = $userGUID; // Use logged in user as fallback
                            $channel      = $rawData['channel'];
                            var_dump($channel);
                            $channelSplit = array();
                            echo "# Channel was $channel\n";
                            {
								// KEEP THIS BLOCK OF CODE IN SYNC WITH OUTBOUND 
								// BR: This cases matches things like Local/LC-52@from-internal-4bbbb
								$pattern = '/Local\/.*(\d\d\d?\d?\d?)@/i';
								if( preg_match($pattern, $channel, $regs)) {
									logLine("Matched User REGEX.  Regex: " . $regs[1] . "\n");
									$asteriskExt = $regs[1];
								}							
                                else if (eregi('^([[:alpha:]]+)/([[:alnum:]]+)-', $channel, $channelSplit) > 0){
                                    $asteriskExt = $channelSplit[2];
									logLine("Channel Matched SIP/### style regex.  Ext is:" . $asteriskExt . "\n");
                                }
								$maybeAssignedUser = findUserByAsteriskExtension($asteriskExt);
                                if ($maybeAssignedUser) {
                                    $assignedUser = $maybeAssignedUser;
                                    logLine("! Assigned user id set to $assignedUser\n");
                                }
                            }
						
                           //
                            // ... on success also update entry in Calls module
                            //
                            
                            //
                            // Calculate call duration...
                            //
                            $hangupTime      = time();
                            $callDurationRaw = 0; // call duration in seconds, only matters if timestampLink != NULL
                            if ($rawData['timestampLink'] != NULL) {
                                $callStartLink   = strtotime($rawData['timestampLink']);
                                $callDurationRaw = $hangupTime - $callStartLink;
                            }
                            $callStart = strtotime($rawData['timestampCall']);
                            
                            echo ("# Measured call duration is $callDurationRaw seconds\n");
                            
                            // Recalculate call direction in minutes
                            $callDuration        = (int) ($callDurationRaw / 60);
                            $callDurationHours   = (int) ($callDuration / 60);
                            //$callDurationMinutes = ceil($callDuration / 60); //voor afronden naar boven.
                            $callDurationMinutes = ($callDuration % 60);
                            
                            //
                            // Calculate final call state
                            //
                            $callStatus      = NULL;
                            $callName        = NULL;
                            $callDescription = "";
							
							// BR: 3/16/2012 I originally had this check to make sure call was longer then 5 seconds... I don't know why. Whey you have callStatus of Missed it creates a task which is undesirable.
							// So i'm commenting it out.  If it's April and I still haven't deleted this comment it's safe to delete this code.
                            //if ($callDurationRaw > 02) {
                                $callStatus = "Held";
                                $callName   = $callDirection . " Call";
                                
                                // This means call description was updated through AJAX so lets not overwrite the subject/description already assigned to the call.
                                if (!empty($callRecord['sweet']['description'])) {
                                    $callName        = $callRecord['sweet']['name'];
                                    $callDescription = $callRecord['sweet']['description'];
                                }
                            //} 
							//else {
                            //    $callStatus      = "Missed";
                            //    $callName        = sprintf("Failed call (%s) ", $e['Cause-txt']);
                            //    $callDescription = "Missed/failed call\n";
                            //    $callDescription .= "------------------\n";
                            //    $callDescription .= sprintf(" %-20s : %-40s\n", "Caller ID", $rawData['callerID']);
                            //    
                            //}
                            
                            // ** EXPERIMENTAL **
                            $assoSugarObject = findSugarObjectByPhoneNumber($rawData['callerID']);
                            $parentID        = NULL;
                            $parentType      = NULL;
                            if ($assoSugarObject && ($assoSugarObject['type'] == 'Contacts')) {
                                $assocAccount = findAccountForContact($assoSugarObject['values']['id']);
                                if ($assocAccount) {
                                    $parentType = 'Accounts';
                                    $parentID   = $assocAccount;
                                }
                            }
                            //var_dump($parentType);
                            //var_dump($parentID);
                            echo ("! Call start was " . gmdate('Y-m-d H:i:s', $callStart) . "\n");
                            
                            //
                            // ... on success also update entry in Calls module
                            //
                            logLine( "# SQL update successfull, now updating record in /Calls/ id=" . $callRecord['sweet']['id'] . "...\n");
                            
                            print_r($callRecord);
                            logLine("NAME: " . $callRecord['sweet']['name'] . "\n");
                            logLine("DESCRIPTION: " . $callRecord['sweet']['description'] . "\n");
                            
                            
                            $soapResult = $soapClient->call('set_entry', array(
                                'session' => $soapSessionId,
                                'module_name' => 'Calls',
                                'name_value_list' => array(
                                    array(
                                        'name' => 'id',
                                        'value' => $callRecord['sweet']['id']
                                    ),
                                    array(
                                        'name' => 'name',
                                        'value' => $callName
                                    ),
                                    array(
                                        'name' => 'duration_hours',
                                        'value' => $callDurationHours
                                    ),
                                    array(
                                        'name' => 'duration_minutes',
                                        'value' => $callDurationMinutes
                                    ),
                                    array(
                                        'name' => 'status',
                                        'value' => $callStatus
                                    ),
                                    array(
                                        'name' => 'description',
                                        'value' => $callDescription
                                    ),
                                    array(
                                        'name' => 'asterisk_caller_id_c',
                                        'value' => $rawData['callerID']
                                    ),
                                    array(
                                        'name' => 'date_start',
                                        'value' => gmdate('Y-m-d H:i:s', $callStart)
                                    ),
                                    array(
                                        'name' => 'parent_type',
                                        'value' => $parentType
                                    ),
                                    array(
                                        'name' => 'parent_id',
                                        'value' => $parentID
                                    ),
                                    array(
                                        'name' => 'assigned_user_id',
                                        'value' => $assignedUser
                                    )
                                )
                            ));
                            
                            //
                            // Establish Relationship if possible
                            //
                            if ($assoSugarObject) {
                                if ($assoSugarObject['type'] == 'Contacts') {
                                    echo "# Establishing relation to contact...\n";
                                    $soapArgs   = array(
                                        'session' => $soapSessionId,
                                        'set_relationship_value' => array(
                                            'module1' => 'Calls',
                                            'module1_id' => $callRecord['sweet']['id'],
                                            'module2' => 'Contacts',
                                            'module2_id' => $assoSugarObject['values']['id'],
                                        )
                                    );
                                    // var_dump($soapArgs);
                                    $soapResult = $soapClient->call('set_relationship', $soapArgs);
                                    // var_dump($soapResult);
                                }
                            }
                        }
                    } else {
                        logLine("FAILED TO FIND THE CALL for asterisk id: $id (note: there are two hangups per call, so this might not be an error)\n");
                    }
                    
                    
                } else {
                    //inbound call handling
                    
                    $id         = $e['Uniqueid'];
                    //
                    // Fetch associated call record
                    //
                    $callRecord = findCallByAsteriskDestId($id);
                    if ($callRecord) {
                        //
                        // update entry in asterisk_log...
                        //
                        $rawData      = $callRecord['bitter']; // raw data from asterisk_log																				
                        $query        = sprintf("UPDATE asterisk_log SET callstate='%s', timestampHangup=%s, hangup_cause=%d, hangup_cause_txt='%s' WHERE asterisk_dest_id='%s'", //asterisk_dest_id was asterisk_id
                            'Hangup', 'NOW()', $e['Cause'], $e['Cause-txt'], $id);
                        $updateResult = mysql_checked_query($query);
                        if ($updateResult) {
                        //
                            // Attempt to find assigned user by asterisk ext
                            //
                            $assignedUser = $userGUID; // Use logged in user as fallback
                            $channel      = $rawData['channel'];
                            var_dump($channel);
                            $channelSplit = array();
                            echo "# Channel was $channel\n";
                            {
								// KEEP THIS BLOCK OF CODE IN SYNC WITH OUTBOUND 
								// BR: This cases matches things like Local/LC-52@from-internal-4bbbb
								$pattern = '/Local\/.*(\d\d\d?\d?\d?)@/i';
								if( preg_match($pattern, $channel, $regs)) {
									logLine("Matched User REGEX.  Regex: " . $regs[1] . "\n");
									$asteriskExt = $regs[1];
								}							
                                else if (eregi('^([[:alpha:]]+)/([[:alnum:]]+)-', $channel, $channelSplit) > 0){
                                    $asteriskExt = $channelSplit[2];
									logLine("Channel Matched SIP/### style regex.  Ext is:" . $asteriskExt . "\n");
                                }
								$maybeAssignedUser = findUserByAsteriskExtension($asteriskExt);
                                if ($maybeAssignedUser) {
                                    $assignedUser = $maybeAssignedUser;
                                    logLine("! Assigned user id set to $assignedUser\n");
                                }
                            }
                            //
                            // ... on success also update entry in Calls module
                            //
                            
                            //
                            // Calculate call duration...
                            //
                            $hangupTime      = time();
                            $callDurationRaw = 0; // call duration in seconds, only matters if timestampLink != NULL
                            if ($rawData['timestampLink'] != NULL) {
                                $callStartLink   = strtotime($rawData['timestampLink']);
                                $callDurationRaw = $hangupTime - $callStartLink;
                            }
                            $callStart = strtotime($rawData['timestampCall']);
                            
                            echo ("# Measured call duration is $callDurationRaw seconds\n");
                            
                            // Recalculate call direction in minutes
                            $callDuration        = (int) ($callDurationRaw / 60);
                            $callDurationHours   = (int) ($callDuration / 60);
                            //$callDurationMinutes = ceil($callDuration / 60); //voor afronden naar boven.
                            $callDurationMinutes = ($callDuration % 60);
                            
                            //
                            // Calculate final call state
                            //
                            $callStatus      = NULL;
                            $callName        = NULL;
                            $callDescription = "";
                            if ($callDurationRaw > 05) {
                                $callStatus = "Held";
                                //$callName = "Successfull call";
                                
                                $callName = $callDirection . " Call";
                                
                                // This means call description was updated through AJAX so lets not overwrite the subject/description already assigned to the call.
                                if (!empty($callRecord['sweet']['description'])) {
                                    $callName        = $callRecord['sweet']['name'];
                                    $callDescription = $callRecord['sweet']['description'];
                                }
                            } else {
                                $callStatus      = "Missed";
                                $callName        = sprintf("Failed call (%s) ", $e['Cause-txt']);
                                $callDescription = "Missed/failed call\n";
                                $callDescription .= "------------------\n";
                                $callDescription .= sprintf(" %-20s : %-40s\n", "Caller ID", $rawData['callerID']);
								logLine("Adding INBOUND Failed Call, id=$id, call_id = " . $callRecord['sweet']['id'] . "\n");
                            }
                            
                            // ** EXPERIMENTAL **
                            $assoSugarObject = findSugarObjectByPhoneNumber($rawData['callerID']);
                            $parentID        = NULL;
                            $parentType      = NULL;
                            if ($assoSugarObject && ($assoSugarObject['type'] == 'Contacts')) {
                                $assocAccount = findAccountForContact($assoSugarObject['values']['id']);
                                if ($assocAccount) {
                                    $parentType = 'Accounts';
                                    $parentID   = $assocAccount;
                                }
                            }
                            //var_dump($parentType);
                            //var_dump($parentID);
                            echo ("! Call start was " . gmdate('Y-m-d H:i:s', $callStart) . "\n");
                            
                            //
                            // ... on success also update entry in Calls module
                            //
                            logLine( "# SQL update successfull, now updating record in /Calls/ id=" . $callRecord['sweet']['id'] . "...\n");
                            
                            print_r($callRecord);
                            logLine("NAME: " . $callRecord['sweet']['name'] . "\n");
                            logLine("DESCRIPTION: " . $callRecord['sweet']['description'] . "\n");
                            
                            
                            $soapResult = $soapClient->call('set_entry', array(
                                'session' => $soapSessionId,
                                'module_name' => 'Calls',
                                'name_value_list' => array(
                                    array(
                                        'name' => 'id',
                                        'value' => $callRecord['sweet']['id']
                                    ),
                                    array(
                                        'name' => 'name',
                                        'value' => $callName
                                    ),
                                    array(
                                        'name' => 'duration_hours',
                                        'value' => $callDurationHours
                                    ),
                                    array(
                                        'name' => 'duration_minutes',
                                        'value' => $callDurationMinutes
                                    ),
                                    array(
                                        'name' => 'status',
                                        'value' => $callStatus
                                    ),
                                    array(
                                        'name' => 'description',
                                        'value' => $callDescription
                                    ),
                                    array(
                                        'name' => 'asterisk_caller_id_c',
                                        'value' => $rawData['callerID']
                                    ),
                                    array(
                                        'name' => 'date_start',
                                        'value' => gmdate('Y-m-d H:i:s', $callStart)
                                    ),
                                    array(
                                        'name' => 'parent_type',
                                        'value' => $parentType
                                    ),
                                    array(
                                        'name' => 'parent_id',
                                        'value' => $parentID
                                    ),
                                    array(
                                        'name' => 'assigned_user_id',
                                        'value' => $assignedUser
                                    )
                                )
                            ));
                            
                            //
                            // Establish Relationship if possible
                            //
                            if ($assoSugarObject) {
                                if ($assoSugarObject['type'] == 'Contacts') {
                                    logLine("# Establishing relation to contact...\n");
                                    $soapArgs = array(
                                        'session' => $soapSessionId,
                                        'set_relationship_value' => array(
                                            'module1' => 'Calls',
                                            'module1_id' => $callRecord['sweet']['id'],
                                            'module2' => 'Contacts',
                                            'module2_id' => $assoSugarObject['values']['id']
                                        )
                                    );
                                    var_dump($soapArgs);
                                    $soapResult = $soapClient->call('set_relationship', $soapArgs);
                                    echo "\n\nPrinting Soap REsult\n\n";
                                    var_dump($soapResult);
                                    echo "\n\n";
                                }
                            }
                        }
                    }
                    // In case of multiple extensions when a call is not answered, every extensions produces a failed call record, this will keep the first of those records but delete the rest.
                    $query     = "SELECT asterisk_id FROM asterisk_log WHERE asterisk_dest_id='$id'";
                    $result    = mysql_checked_query($query);
                    $result_id = mysql_fetch_array($result);
					logLine("Cleaning up Failed Calls part1, asterisk_id = ".$result_id['asterisk_id']."\n");
					
                    $query     = "SELECT call_record_id FROM asterisk_log WHERE asterisk_id='" . $result_id['asterisk_id'] . "' ORDER BY id ASC LIMIT 1, 999999999999";
                    $result    = mysql_checked_query($query);
					logLine("Cleaning up Failed Calls part2, call_record_id = ".$result['call_record_id']."\n");
					
					
                    while ($call_record_id = mysql_fetch_array($result)) {
                        //For testing purposes
                        //$query = "SELECT id FROM calls WHERE id='" . $call_record_id['call_record_id'] . "' AND name LIKE 'Failed call%'";
                        $query = "DELETE FROM calls WHERE id='" . $call_record_id['call_record_id'] . "' AND name LIKE 'Failed call%'";
                        $rq    = mysql_checked_query($query);
                        //$total_result = mysql_fetch_array($rq);
                        //var_dump($total_result);
                    }
                }
                
            }
            ;
            
             
            // success
            //Asterisk Manager 1.1
            if ($e['Event'] == 'Bridge') {
                $query     = "SELECT direction FROM asterisk_log WHERE asterisk_id='" . $e['Uniqueid2'] . "' OR asterisk_dest_id='" . $e['Uniqueid2'] . "'";
                $result    = mysql_checked_query($query);
                $direction = mysql_fetch_array($result);
                if ($direction['direction'] == "I") {
                    $callDirection = "Inbound";
                } else {
                    $callDirection = "Outbound";
                }
                if ($callDirection == "Inbound") {
                    // Inbound bridge event
                    $query  = "UPDATE asterisk_log SET callstate='Connected', timestampLink=NOW() WHERE asterisk_dest_id='" . $e['Uniqueid1'] . "' OR asterisk_dest_id='" . $e['Uniqueid2'] . "'";
                    $rc     = mysql_checked_query($query);
                    // und vice versa .. woher immer der call kam
                    // $query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=NOW() WHERE asterisk_id='" . $e['Uniqueid2'] . "'";
                    // $record = mysql_query($query);
                    // to delete all the extra inbound records created by the hangup event.
                    $id1    = $e['Uniqueid1'];
                    $id2    = $e['Uniqueid2'];
                    $query  = "SELECT call_record_id FROM asterisk_log WHERE asterisk_id='" . $id1 . "' AND asterisk_dest_id!='" . $id2 . "'";
                    $result = mysql_checked_query($query);
                    //for error logging
                    //$call_rec_id = mysql_fetch_array($result);
                    //var_dump($call_rec_id);
                    while ($call_rec_id = mysql_fetch_array($result)) {
                        $query = "DELETE FROM calls WHERE id='" . $call_rec_id['call_record_id'] . "'";
                        $rc    = mysql_checked_query($query);
                    }
                    
                } else {
                    if ($e['Event'] == 'Bridge') //Outbound bridge event
                        {
                        $query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=NOW() WHERE asterisk_id='" . $e['Uniqueid1'] . "' OR asterisk_id='" . $e['Uniqueid2'] . "'";
                        $rc    = mysql_checked_query($query);
                        
                    }
                }
            }
            //Asterisk Manager 1.0
            
            /*if($e['Event'] == 'Link')
            {
            $query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=NOW() WHERE asterisk_id='" . $e['Uniqueid1'] . "' OR asterisk_id='" . $e['Uniqueid2'] . "'";
            $rc = mysql_checked_query($query);
            
            // und vice versa .. woher immer der call kam
            // $query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=NOW() WHERE asterisk_id='" . $e['Uniqueid2'] . "'";
            // $record = mysql_query($query);
            };*/
            
            // Reset ebent buffer
            $event = '';
            
            
        }
        
        // handle partial packets
        if ($event_started) {
            $event .= $buffer;
        } else if (strstr($buffer, 'Event:')) {
            $event         = $buffer;
            $event_started = true;
        }
        // for if the connection to the sql database gives out.
        if (!mysql_ping($sql_connection)) {
            //here is the major trick, you have to close the connection (even though its not currently working) for it to recreate properly.
            logLine("MySQL connection lost, reconnecting\n");
            mysql_close($sql_connection);
            $sql_connection = mysql_connect($sugar_config['dbconfig']['db_host_name'], $sugar_config['dbconfig']['db_user_name'], $sugar_config['dbconfig']['db_password']);
            $sql_db         = mysql_select_db($sugar_config['dbconfig']['db_name']);
            
        }
        
    }
    
    
    logLine(getTimestamp() . " # Event loop terminated, attempting to login again\n");
    sleep(1);
}


exit(0);



// ******************
// Helper functions *
// ******************

// go through and parse the event
function getEvent($event)
{
    $e          = array();
    $e['Event'] = '';
    
    $event_params = explode("\n", $event);
    
    foreach ($event_params as $event) {
        if (strpos($event, ": ") > 0) {
            list($key, $val) = explode(": ", $event);
            //		$values = explode(": ", $event);
            $key = trim($key);
            $val = trim($val);
            
            if ($key) {
                $e[$key] = $val;
            }
        }
    }
    return ($e);
}


function getTimestamp()
{
    return date('[Y-m-d H:i:s]');
}


function dumpEvent(&$event)
{
    // Skip 'Newexten' events - there just toooo many of 'em || For Asterisk manager 1.1 i choose to ignore another stack of events cause the log is populated with useless events
    if ($event['Event'] === 'Newexten' || $event['Event'] == 'UserEvent' || $event['Event'] == 'AGIExec' || $event['Event'] == 'Newchannel' || $event['Event'] == 'Newstate' || $event['Event'] == 'ExtensionStatus') {
        return;
    }
    
    $eventType = $event['Event'];
    
    logLine(getTimeStamp() . "\n");
    logLine("! --- Event -----------------------------------------------------------\n");
    foreach ($event as $eventKey => $eventValue) {
        logLine(sprintf("! %20s --> %-50s\n", $eventKey, $eventValue));
    }
    logLine("! ---------------------------------------------------------------------\n");
}


//
// Locate associated record in "Calls" module
//
function findCallByAsteriskId($asteriskId)
{
    global $soapClient, $soapSessionId;
    logLine("# +++ findCallByAsteriskId($asteriskId)\n");
    
    //
    // First, fetch row in asterisk_log...
    //
    
    $sql         = sprintf("SELECT * from asterisk_log WHERE asterisk_id='$asteriskId'", $asteriskId);
    $queryResult = mysql_checked_query($sql);
    if ($queryResult === FALSE) {
        logLine("Asterisk ID NOT FOUND in asterisk_log (db query returned FALSE)");
        return FALSE;
    }
    
    while ($row = mysql_fetch_assoc($queryResult)) {
        $callRecId = $row['call_record_id'];
        logLine("! Found entry in asterisk_log recordId=$callRecId\n");
        
        //
        // ... then locate Object in Calls module:
        //
        $soapResult    = $soapClient->call('get_entry', array(
            'session' => $soapSessionId,
            'module_name' => 'Calls',
            'id' => $callRecId
        ));
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
        // echo ("# ** Soap call successfull, dumping result ******************************\n");
        // var_dump($soapResult);
        // var_dump($resultDecoded);
        // var_dump($row);
        // echo ("# ***********************************************************************\n");
        
        //
        // also store raw sql data in case we need it later...
        //
        return array(
            'bitter' => $row,
            'sweet' => $resultDecoded
        );
    }
    logLine("! Warning, results set was empty!\n");
    return FALSE;
}
// AsteriskManager 1.1 for inbound calling
function findCallByAsteriskDestId($asteriskDestId)
{
    global $soapClient, $soapSessionId;
    logLine("# +++ findCallByAsteriskDestId($asteriskDestId)\n");
    
    //
    // First, fetch row in asterisk_log...
    //
    
    $sql         = sprintf("SELECT * from asterisk_log WHERE asterisk_dest_id='$asteriskDestId'", $asteriskDestId);
    $queryResult = mysql_checked_query($sql);
    if ($queryResult === FALSE) {
        return FALSE;
    }
    
    while ($row = mysql_fetch_assoc($queryResult)) {
        $callRecId = $row['call_record_id'];
        echo "! Found entry in asterisk_log recordId=$callRecId\n";
        
        //
        // ... then locate Object in Calls module:
        //
        $soapResult    = $soapClient->call('get_entry', array(
            'session' => $soapSessionId,
            'module_name' => 'Calls',
            'id' => $callRecId
        ));
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
        // echo ("# ** Soap call successfull, dumping result ******************************\n");
        // var_dump($soapResult);
        var_dump($resultDecoded);
        // var_dump($row);
        // echo ("# ***********************************************************************\n");
        
        //
        // also store raw sql data in case we need it later...
        //
        return array(
            'bitter' => $row,
            'sweet' => $resultDecoded
        );
    }
    echo "! Warning, results set was empty!\n";
    return FALSE;
}

//
// Repacks a name_value_list eg returned by get_entry() into a hash (aka associative array in PHP speak)
//
function decode_name_value_list(&$nvl)
{
    $result = array();
    
    foreach ($nvl as $nvlEntry) {
        $key          = $nvlEntry['name'];
        $val          = $nvlEntry['value'];
        $result[$key] = $val;
    }
    return $result;
}


//
// Attempt to find a Sugar object (Contact,..) by phone number
//
//
function findSugarObjectByPhoneNumber($aPhoneNumber)
{
    global $soapClient, $soapSessionId;
    logLine("# +++ findSugarObjectByPhoneNumber($aPhoneNumber)\n");
    
    // Add if phonenumber .length == 10
    $searchPattern = $aPhoneNumber;
    //$searchPattern = regexify($aPhoneNumber);
    
    //
    // Plan A: Attempt to locate an object in Contacts
    //        $soapResult = $soapClient->call('get_entry' , array('session' => $soapSessionId, 'module_name' => 'Calls', 'id' => $callRecId));
    //
    
    
    //************ CID.agi
    // =~ <--- use the left side as input string to regex.
    // s -- replace.
    //
    //$number =~ s/\D//g;
    //$number =~ m/([0-9]{7})$/;
    //$number = $1 if ($1);
    //
    //if ($1) {
    //  $regje = $number;
    //  $regje =~ s/(\d)/$1\[^\\d\]*/g;
    //  $regje = '(' . $regje . ')' . '$';
    //} elsif($number) {
    //  $regje = '^' . $number . '$';
    //} else {
    //  debugAndQuit("No caller ID found for this call");
    //}
    //
    //debug("Searching for regexp $regje",5);  
    //
    //# lookup the number @ contacts
    //$result = $service->get_entry_list($sid,"Contacts","contacts.phone_home REGEXP '$regje' OR contacts.phone_mobile REGEXP '$regje' OR contacts.phone_work REGEXP '$regje' OR contacts.phone_other REGEXP '$regje' OR contacts.phone_fax REGEXP '$regje'","",0,{a=>"first_name",b=>"last_name",c=>"account_name"},1,0)->result;
    //$id = $result->{entry_list}[0]{id};
    
    // TODO figure out what that 2nd case could be the elseif part... 
    
    $aPhoneNumber = preg_replace( '/\D/', '', $aPhoneNumber); // removes everything that isn't a digit.
	if( preg_match('/([0-9]{7})$/',$aPhoneNumber,$matches) ){
		$aPhoneNumber = $matches[1];
	}
	
	$regje = preg_replace( '/(\d)/', '$1\[^\\d\]*',$aPhoneNumber);
	$regje = '(' . $regje . ')$';    
    
    logLine("  Contact query components- Phone: $aPhoneNumber   RegEx: $regje\n");
    //*******/
    
    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Contacts',
        // the original query made matches to anything that had parts of the searchpattern in it, this one looks for the exact phonenumber
        
        // 2nd version 'query' => "((contacts.phone_work = '$searchPattern') OR (contacts.phone_mobile = '$searchPattern') OR (contacts.phone_home = '$searchPattern') OR (contacts.phone_other = '$searchPattern'))", );
        // Original... 
		//'query' => "((contacts.phone_work LIKE '$searchPattern') OR (contacts.phone_mobile LIKE '$searchPattern') OR (contacts.phone_home LIKE '$searchPattern') OR (contacts.phone_other LIKE '$searchPattern'))"
     
		// Liz Version: Only works on mysql
		'query' => "contacts.phone_home REGEXP '$regje' OR contacts.phone_mobile REGEXP '$regje' OR contacts.phone_work REGEXP '$regje' OR contacts.phone_other REGEXP '$regje' OR contacts.phone_fax REGEXP '$regje'",
   
    );
    
    // print "--- SOAP get_entry_list() ----- ARGS ----------------------------------------\n";
    // var_dump($soapArgs);
    // print "-----------------------------------------------------------------------------\n";
    
    $soapResult = $soapClient->call('get_entry_list', $soapArgs);
    
    //print "--- SOAP get_entry_list() FOR GET CONTACT ------------------------------------\n";
    //var_dump($soapResult);
    //print "-----------------------------------------------------------------------------\n";
    
    if ($soapResult['error']['number'] != 0) {
        logLine("! Warning: SOAP error " . $soapResult['error']['number'] . " " . $soapResult['error']['string'] . "\n");
    } 
	else if( $soapResult['result_count'] == 0 ) {
		logLine("! No results returned\n");
	}
	else {
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
        //print "--- Decoded get_entry_list() FOR GET CONTACT --------------------------------------\n";
        //var_dump($resultDecoded);
        //print "-----------------------------------------------------------------------------\n";
        return array(
            'type' => 'Contacts',
            'values' => $resultDecoded
        );
    }
    
    // Oops nothing found :-(
    return FALSE;
}

// Function only necessary in case of the original query used.
// Replace a phone number to search with a universal-match-anyway(tm) expression to be used
// in a SQL 'LIKE' condition - eg 1234 --> %1%2%3%4%
//

/*function regexify($aPhoneNumber)
{
return '%' . join('%', str_split($aPhoneNumber)) . '%';
}
*/
//
// Finds related account for given contact id
//
function findAccountForContact($aContactId)
{
    global $soapClient, $soapSessionId;
    logLine("# +++ findAccountForContact($aContactId)\n");
    
    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Contacts',
        'module_id' => $aContactId,
        'related_module' => 'Accounts',
        'related_module_query' => '',
        'deleted' => 0
    );
    
    $soapResult = $soapClient->call('get_relationships', $soapArgs);
    
    if ($soapResult['error']['number'] != '0') {
        logLine("! WARNING Soap called returned with error " . $soapResult['error']['number'] . " " . $soapResult['error']['name'] . " // " . $soapResult['error']['description'] . "\n");
        return FALSE;
    } else {
        // var_dump($soapResult);
        
        $assocCount = count($soapResult['ids']);
        
        if ($assocCount == 0) {
            logLine("# No associated account found\n");
            return FALSE;
        } else {
            if ($assocCount > 1) {
                logLine("! WARNING: More than one associated account found, using first one.\n");
            }
            
            $assoAccountID = $soapResult['ids'][0]['id'];
            logLine("# Associated account is $assoAccountID\n");
            return $assoAccountID;
        }
    }
}

//
// Locate user by asterisk extension
// NOTE: THIS RETURNS JUST AN ID
//
function findUserByAsteriskExtension($aExtension)
{
    logLine("# +++ findUserByAsteriskExtension($aExtension)\n");

	$qry = "select id from users join users_cstm on users.id = users_cstm.id_c where users_cstm.asterisk_ext_c=$aExtension and status='Active'";
	$result = mysql_checked_query($qry);
	if( $result ) {
		$row = mysql_fetch_array($result);
		return $row['id'];
	}
	
	return FALSE;
	


	///// OLD WAY OF DOING IT IS WITH SOAP... DIDN"T WORK FOR ME... so reverted to db query.
/*
    global $soapClient, $soapSessionId;
    print("# +++ findUserByAsteriskExtension($aExtension)\n");

			//'select_fields'=>array('id', 'first_name', 'last_name'), 
		//'deleted' => 0,
    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Users',
		'query' => '(users_cstm.asterisk_ext_c=\'710\')',
   // 	'query' => sprintf("(users_cstm.asterisk_ext_c='%s')", $aExtension),
'select_fields'=>array('id', 'first_name', 'last_name'), 
    );
	//var_dump($soapArgs);
	
    $soapResult = $soapClient->call('get_entry_list', $soapArgs);
    
     var_dump($soapResult);
    
    if ($soapResult['error']['number'] != 0) {
        logLine("! Warning: SOAP error " . $soapResult['error']['number'] . " " . $soapResult['error']['string'] . "\n");
    } 
	else if( $soapResult['result_count'] == 0 ) {
		logLine("! No results returned\n");
	}
	else {
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
        // print "--- SOAP get_entry_list() ----- RESULT --------------------------------------\n";
         var_dump($resultDecoded);
        // print "-----------------------------------------------------------------------------\n";
        return $resultDecoded['id'];
    }
    
    return FALSE;
	*/
}

//
// Checked execution of a MySQL query
//
// This function provides a wrapper around mysql_query(), providing SQL and error loggin
//
function mysql_checked_query($aQuery)
{
    global $mysql_loq_queries;
    global $mysql_log_results;
    
    logLine("# +++ mysql_checked_query()\n");
    $query = trim($aQuery);
    if ($mysql_loq_queries) {
        print "! SQL: $query\n";
    }
    
    // Is this is a SELECT ?
    $isSelect = eregi("^select", $query);
    
    $sqlResult = mysql_query($query);
    
    if ($mysql_log_results) {
        if (!$sqlResult) {
            // Error occured
            print("! SQL error " . mysql_errno() . " (" . mysql_error() . ")\n");
        } else {
            // SQL succeeded
            if ($isSelect) {
                logLine("# Rows in result set: " . mysql_num_rows($sqlResult) . "\n");
            } else {
                logLine("# Rows affected: " . mysql_affected_rows() . "\n");
            }
        }
    }
    
    // Pass original result to caller
    logLine("# --- mysql_checked_query()\n");
    return $sqlResult;
}

function logLine($str)
{
	global $sugar_config;
    print($str); 
	
	// if logging is enabled.
	if( !empty($sugar_config['asterisk_log_file']) ) 
	{
		$myFile = $sugar_config['asterisk_log_file']; // Might be a performance issue being here... 
		$fh = fopen($myFile, 'a') or die("can't open file");
		fwrite($fh, $str);
		fclose($fh);
	}
}

// Theoretically safe method, feof will block indefinitely.
function safe_feof($fp, &$start = NULL) {
 $start = microtime(true);
 return feof($fp);
}


?>
