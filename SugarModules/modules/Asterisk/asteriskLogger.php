<?php
/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 *
 * Project Home: www.github.com/blak3r/yaai
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * Parts of this code are (c) 2011 Blake Robertson http://www.blakerobertson.com
 * Sugarforge: http://www.sugarforge.org/projects/yaai/
 * GitHub: http://www.github.com/blak3r/yaai
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
$mysql_loq_queries = 0;
$mysql_log_results = 0;
$verbose_log = 0;

// All Sugar timestamps are UTC
date_default_timezone_set('UTC');

//
// Say hello, setup include path(s)
//
define('sugarEntry', TRUE);
logLine( "\n\n\n******** Asterisk Logger Starting ". getTimestamp() . "**************\n",true);


// Determine SugarCRM's root dir (we'll use this to find the config filez
$scriptRoot = dirname(__FILE__);
$sugarRoot  = $scriptRoot . "/../../../";
print "[Config Settings]\n";
print "  Sugar root set to [$sugarRoot]\n";
//set_include_path(get_include_path() . PATH_SEPARATOR . $sugarRoot . "include");
//print "# PHP include path set to [" . get_include_path() . "]\n";

// Specify a custom sugar root on commandline when in test mode (I used this to debug from eclipse)
if( $argc > 2 ) {
	$sugarRoot = $argv[2] ;
    if( !endsWith($sugarRoot,"/")){
        $sugarRoot .= "/";
    }
	print "  New sugar root is: " . $sugarRoot;
}


//
// Required libraries
//
require_once($sugarRoot . "include/nusoap/nusoap.php");

// Wrapper class auto logins in if session expires.
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
        if( $this->sessionid == -1 ) {
            logLine("! Soap login failed!\n");
            print_r($result);
        }
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

logLine( "  Logging to: " . $sugar_config['asterisk_log_file'] . "\n");
$asteriskServer        = $sugar_config['asterisk_host'];
$asteriskManagerPort   = (int) $sugar_config['asterisk_port'];
$asteriskUser          = "Username: " . $sugar_config['asterisk_user'] . "\r\n";
$asteriskSecret        = "Secret: " . $sugar_config['asterisk_secret'] . "\r\n";
$asteriskMatchInternal = $sugar_config['asterisk_expr'];

// Load localization if available
$locale_path = $sugarRoot . 'custom/modules/Asterisk/language/' . $sugar_config['default_language'] . '.lang.php';
if (file_exists($locale_path)) include_once $locale_path;

// Make regex pattern compatible with preg_match
if( !startsWith($asteriskMatchInternal, '/' ) ) {
		$asteriskMatchInternal = '/' . $asteriskMatchInternal . '/i';
}

// Fetch Asterisk dialprefix - must strip this from inbound callerIDs if set
$calloutPrefix = isset($sugar_config['asterisk_prefix']) ? $sugar_config['asterisk_prefix'] : "";
echo ("  Callout prefix is [$calloutPrefix]\n");

$callinPrefix = isset($sugar_config['asterisk_dialinPrefix']) ? $sugar_config['asterisk_dialinPrefix'] : "";
echo ("  Callin prefix is [$callinPrefix]\n");
echo ("  Match Internal Regex = $asteriskMatchInternal\n");




//
// Connect to mySQL DB
//
logLine("[Database Connection]\n");
logLine("  Selecting DB Name: {$sugar_config['dbconfig']['db_name']}\n");
$sql_connection = mysql_connect($sugar_config['dbconfig']['db_host_name'], $sugar_config['dbconfig']['db_user_name'], $sugar_config['dbconfig']['db_password']);
$sql_db         = mysql_select_db($sugar_config['dbconfig']['db_name']);
// Prune asterisk_log
// Note only use this for development
//mysql_query('DELETE FROM asterisk_log');

// Set all MySQL dates to UTC
mysql_query("SET time_zone='+00:00'");

// Get SOAP config
$sugarSoapEndpoint   = $sugar_config['site_url'] . "/soap.php";//"/soap.php";
$sugarSoapUser       = $sugar_config['asterisk_soapuser'];
$sugarSoapCredential = md5($sugar_config['asterisk_soappass']);

// Here we check if LDAP Authentication is used, if so we must build credential differently
$q = mysql_query('select value from config where category=\'system\' and name=\'ldap_enabled\'');
$r = mysql_fetch_assoc($q);
if($r['value'] != 1){
    $sugarSoapCredential = md5($sugar_config['asterisk_soappass']);
}
else{
    $q = mysql_query('select value from config where category=\'ldap\' and name=\'enc_key\'');
    $r = mysql_fetch_assoc($q);
    $ldap_enc_key = substr(md5($r['value']),0,24);
    $sugarSoapCredential = bin2hex(mcrypt_cbc(MCRYPT_3DES, $ldap_enc_key, $sugar_config['asterisk_soappass'], MCRYPT_ENCRYPT, 'password'));
}

// Added this while loop to keep retrying the soap login b/c I found when I started it through daemon script...
// despite specifying apache as a dependency... it was failing to connect...  (this will keep retrying login until it's successful).
// Probably should have this script reload the SOAP config from config.php.  Wasn't sure how to do that since it's already been
// included at top of file in require_once.
$successfullyLoggedInThroughSoap = false;
while( !$successfullyLoggedInThroughSoap ) {
    //
    // And finally open a SOAP connection to SugarCRM
    //
    logLine("[SOAP Connection]\n");
    logLine("  Trying SOAP login endpoint=[$sugarSoapEndpoint] user=[$sugarSoapUser] password=[$sugarSoapCredential]\n");

    $auth_array = array(
        'user_auth' => array(
            'user_name' => $sugarSoapUser,
            'password' => $sugarSoapCredential
        )
    );
    $soapClient = new SugarSoap($sugarSoapEndpoint . '?wsdl', true, $auth_array); // This method logs in also
    $soapSessionId = $soapClient->sessionid;
    $userGUID      = $soapClient->call('get_user_id', array(
        $soapSessionId
    ));

    if (is_array($userGUID) && array_key_exists("error", $userGUID) && $userGUID['error']['number'] != 0) {
        logLine("  **WARNING Unable to make initial SOAP Call " . $userGUID['error']['number'] . " " . $userGUID['error']['name'] . " // " . $userGUID['error']['description'] . "**\n");
        logLine("  Retrying in 5 seconds...\n\n");
        sleep(5);
    }
    // This case might be obsolete at this point...
    else if( empty($userGUID) || empty($soapSessionId) || $userGUID == -1 ) {
        logLine( "  __FATAL: SOAP login failed, something didn't get set by login... check your site_url, and make sure sugarcrm is running.  SessionID:" . $soapSessionId . " user=" . $auth_array['user_auth']['user_name'] . " GUID=" . $userGUID . "__\n");
        logLine( "  Sleeping for 5 seconds then retrying...\n\n");
        sleep(5);
    }
    else {
        logLine( "  Successfully logged into Sugar via SOAP!  SessionId=" . $soapSessionId . " user=" . $auth_array['user_auth']['user_name'] . " GUID=" . $userGUID . "\n");
        $successfullyLoggedInThroughSoap = true;
    }
}



if( $argc > 1 && $argv[1] == "test" ) {
	// MODIFY these variables to help diagnose misc incompatibilities between your configuration / sugarcrm edition.
	$ext1 = 51;
	$ext2 = 52;
	//$extensions = (51,52,207);
	$phones = array('4102152497','sdfasdf','(267) 222-8385','2672228385' );

	print "Entered test mode!";



	$obj = findSugarObjectByPhoneNumber("4102152497");
	print "findUserByAsteriskExtension(51) returned: " . findUserByAsteriskExtension("51") . "\n";
	print "findUserByAsteriskExtension(207) returned: " . findUserByAsteriskExtension("207") . "\n";
	print "findUserByAsteriskExtension(710) returned: " . findUserByAsteriskExtension('710') . "\n";
	findUserByAsteriskExtension('206');

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
    logLine("[Asterisk Manager Interface (AMI) Connection]\n");
    // connect to Asterisk server
    $amiSocket = fsockopen($asteriskServer, $asteriskManagerPort, $errno, $errstr, 5);
    if (!$amiSocket) {
        logLine( "  __ ERROR $errno connecting to Asterisk: $errstr __");
		sleep(5); // retry connecting
		continue;
    } else {
        logLine( "  Successfully opened socket connection to $asteriskServer:$asteriskManagerPort\n");
    }

    // Here we extract the AMI Version Number and set a varible with it for creating special if cases for special versions of AMI in the future.
    $result = AMI_ReadResponse($amiSocket,2000000);
    logLine("  AMI Version Info:\n" . markdown_indent($result) ); // Prints the AMI Version
    if( preg_match("/Call Manager\/(\d\.\d)/",$result,$ver_matches)) {
        $managerVersion = $ver_matches[1];
        if( !$managerVersion == "1.1" ) {
            logLine("  **ERROR: AMI v$managerVersion is not supported by this version of asteriskLogger.  Please see issues section on github site.  Several people have gotten it working in 1.0 but changes haven't been merged yet.");
        }
        else {
            logLine("  Supported AMI version: $managerVersion Detected");
        }
    }
    else {
        logLine("  __WARNING: Unable to detect the manager version.  Setting to 1.1 and hoping for the best.  But, this is probably an error__");
        $managerVersion = "1.1";
    }

    // Perform AMI Login command
    fputs($amiSocket, "Action: Login\r\n");
    fputs($amiSocket, $asteriskUser);
    fputs($amiSocket, $asteriskSecret);
    fputs($amiSocket, "Events: call,hud\r\n\r\n"); // to monitor just call data, for Asterisk Manager 1.0 remove hud
    $result = AMI_ReadResponse($amiSocket,2000000);
    logLine("  AMI Login action raw response:\n" . markdown_indent($result) );
    if( AMI_WasCmdSuccessful($result) ) {
        logLine("  AMI Login was a *success!*");
        logLine("Waiting for call events...");
    }
    else {
        logLine("  __ERROR: AMI Login FAILED__, Depending on your asterisk version helpful info may be above.\n  **Check Asterisk Username / Password in config, then verify AMI user has proper permissions in manager.conf**\n\n");
        sleep(5);
        continue;
    }

    $event = '';
    $stack = 0;

    $event_started = false;

	$start = NULL;
	$timeout = ini_get('default_socket_timeout');

	stream_set_timeout($amiSocket,60); // sets timeout to 60 seconds.
	$consecutiveFailures = 0;
    $queueChannels = array();

    // Keep a loop going to read the socket and parse the resulting commands.
	// Apparently there is no good way to detect if socket is still alive???
	// This is my hack... if we fail 60 times in a row we reconnect to manager...
    // I suspect that fgets will return very quickly if socket error has occurrs
	// So, it'll reach 60 very quickly and then force relogin.
    // Otherwise, every hour it'll just relogin.
    // Perhaps you can check socket error some other way then socket_read?
    // All I know is this reconnect method has made my asteriskLogger a lot more stable.
    while ($consecutiveFailures < 60 && !safe_feof($amiSocket, $start) && (microtime(true) - $start) < $timeout) {
        $buffer = fgets($amiSocket, 4096);
        // echo("# Read " . strlen($buffer) . " "  . $buffer . "\n");

       if( $buffer === FALSE )
		{
			logLine(getTimestamp() . " Patiently Waiting...!\n");
			$consecutiveFailures++;
		}
		else {
			$consecutiveFailures = 0;
			if ($buffer == "\r\n") { // handle partial packets

				$event_started = false;
				// parse the event and get the result hashtable
				$e             = getEvent($event);
				dumpEvent($e); // prints to screen

                if ($e['Event'] == 'Join' && !empty($e['Queue']) /*&& in_array($e['Queue'], $allowedQueueIds)*/ )
                {
                    $queueChannels[ $e['UniqueID'] ] = $e['Channel']; // TODO: This array will grow indefinitely... the data put into it is pretty small so probably fine for now but would be best to have a expiration policy.
                                // Easy solution would be to test during the hangup event... IF( isset($queueChannels[ $e['UniqueID'] ] ) remove the index for $e['UniqueID']
                }

				//
				// Call Event
				//
				if ($e['Event'] == 'Dial' && $e['SubEvent'] != 'End') //Asterisk Manager 1.1 returns 2 dial events with a begin and an end subevent, by excluding the subevent end we won't get multiple records for the same call.
					{
					logLine("! Dial Event src=" . $e['Channel'] . " dest=" . $e['Destination'] . "\n"); //Asterisk Manager 1.1
					//print "! Dial Event src=" . $e['Source'] . " dest=" . $e['Destination'] . "\n"; 	//Asterisk Manager 1.0

                    $eChannel = $e['Channel'];

                    // Attempt to make compatible with AMI 1.0
                    if( !empty($e['Source']) ) {
                        $eChannel = $e['Source'];
                    }

                    $eDestination = $e['Destination'];

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
								'value' => $mod_strings['CALL_AUTOMATIC_RECORD']
							),
							array(
								'name' => 'status',
								'value' => $mod_strings['CALL_IN_LIMBO']
							),
							array(
								'name' => 'assigned_user_id',
								'value' => $userGUID
							)
						)
					);
					$soapResult = $soapClient->call('set_entry', $set_entry_params);
                    //print_r( $soapResult );
					$callRecordId = $soapResult['id'];
					logLine("! Successfully created CALL record with id=" . $callRecordId . "\n");

					$call = NULL;

					$tmpCallerID = trim($e['CallerIDNum']); //Asterisk Manager 1.0 $e['CallerID']

                    // Typically for outbound calls there are NewCallerID events which contain the phone number dialed.
                    // This isn't the case on POTS lines.
                    // The dialstring will be like g0/14101234567 for outbound calls and 14101234567 for inbound
                    // Regex only matches the outbound case since in the inbound case the CallerIDNum variable is set properly.
                    // Note: this cases also seems to happen on the INTERNAL inbound call events to Ring Groups which is harmless.
                    if( !empty($e['Dialstring']) ) {
                        if( preg_match("/(.*?\/)(\d+)/",$e['Dialstring'], $ds_matches) ) {
                            $tmpCallerID = $ds_matches[2];
                            logLine(" CallerID set from Dialstring to: " . $tmpCallerID );
                        }
                    }

                    // Fix for issue on some asterisk 1.8 boxes where CallerId on click to dial is not set.  See https://github.com/blak3r/yaai/issues/75
                    if ($tmpCallerID == '<unknown>' && !empty($e['ConnectedLineNum'])) {
                        $tmpCallerID = trim($e['ConnectedLineNum']);
                        logLine( " CallerID set from ConnectedLineNum to $tmpCallerID");
                    }

					if (startsWith($tmpCallerID,$calloutPrefix)) {
						logLine ("  Stripping callout prefix: $calloutPrefix\n");
						$tmpCallerID = substr($tmpCallerID, strlen($calloutPrefix));
					}

					if (startsWith($tmpCallerID,$callinPrefix)) {
						logLine ("  Stripping callin prefix: $calloutPrefix\n");
						$tmpCallerID = substr($tmpCallerID, strlen($callinPrefix));
					}

					logLine("  CallerID is: $tmpCallerID\n");

                    // Check to see if this Dial Event is coming off a Queue.  If so we override the channel with the one we saved previously in Join Event.
                    if (!empty($e['ConnectedLineNum']) &&
                       isset($queueChannels[ $e['UniqueID'] ]) )
                    {
                        // TODO: This code needs to be verified... author didn't have queues.
                        // The idea here is to use the channel from the Queue Join event to find the true source channel.  Otherwise queue calls would get detected as internal calls.
                        logLine("Inbound From QUEUE detected, overriding: {$e['Channel']} with $channel");
                        $eChannel = $queueChannels[ $e['UniqueID'] ];
                    }

					$rgDetectRegex = "/" . $sugar_config['asterisk_rg_detect_expr'] . "/i";
					$rgCellRingRegex = "/" . $sugar_config['asterisk_rg_cell_ring_expr'] . "/i";// This detects in a RG when an outside line is called (usually for a cellphone)... for some reason the cell shows up as the Channel (aka the source)... We detect this by finding a number thats at least 7-10 characters long..

					// Check if both ends of the call are internal (then delete created (** Automatic record **) record)
					// 2nd condition looks for Local/RG-52-4102152497
					if ( (preg_match($asteriskMatchInternal, $eChannel) && preg_match($asteriskMatchInternal, $eDestination)) ||
						 preg_match($rgDetectRegex, $eDestination ) ||
						 preg_match($rgCellRingRegex, $eChannel) )
					{
						deleteCall($callRecordId);
						logLine("INTERNAL call detected, Deleting Call Record $callRecordId\n");
					}
					else
					{
						//Asterisk Manager 1.1 (If the call is internal, this will be skipped)
						if (preg_match($asteriskMatchInternal, $eChannel) && !preg_match($asteriskMatchInternal, $eDestination)) {
							$query         = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, remote_channel, callstate, direction, CallerID, timestampCall) VALUES('%s','%s','%s','%s','%s','%s','%s',%s)", $e['DestUniqueID'], $callRecordId, $eChannel, $eDestination, 'NeedID', 'O', $tmpCallerID, 'FROM_UNIXTIME('.time().')');
							$callDirection = 'Outbound';
							logLine("OUTBOUND state detected... $asteriskMatchInternal is astMatchInternal eChannel= " . $eChannel . ' eDestination=' . $eDestination . "\n");
						} else if (!preg_match($asteriskMatchInternal, $eChannel)) {
							$query         = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, remote_channel, callstate, direction, CallerID, timestampCall, asterisk_dest_id) VALUES('%s','%s','%s','%s','%s','%s','%s',%s,'%s')", $e['UniqueID'], $callRecordId, $eDestination, $eChannel, 'Dial', 'I', $tmpCallerID, 'FROM_UNIXTIME('.time().')', $e['DestUniqueID']);
							$callDirection = 'Inbound';
							logLine("Inbound state detected... $asteriskMatchInternal is astMatchInternal eChannel= " . $eChannel . ' eDestination=' . $eDestination . "\n");
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
						FROM_UNIXTIME('.time().')
						);
						$callDirection = 'Outbound';
						}
						else{
						$query = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, callstate, direction, CallerID, timestampCall) VALUES('%s','%s','%s','%s','%s','%s',%s)",
						$e['SrcUniqueID'],
						$callRecordId,
						$eDestination,
						'Dial',
						'I',
						$tmpCallerID,
						FROM_UNIXTIME('.time().')
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
					}
				}

				//
				// NewCallerID for Outgoing Call
				//
				//Asterisk Manager 1.1
				if ($e['Event'] == 'NewCallerid') {
					$id          = $e['Uniqueid'];   // <-- GRRRRRRR AMI inconsistency in every other event it's "UniqueID"
					$tmpCallerID = trim($e['CallerIDNum']);   // TODO For AMI v1.0 Support, add condition to look in $e['CallerId'] instead.
					if ((strlen($calloutPrefix) > 0) && (strpos($tmpCallerID, $calloutPrefix) === 0)) {
						logLine ("* Stripping prefix: $calloutPrefix");
						$tmpCallerID = substr($tmpCallerID, strlen($calloutPrefix));
					}
					logLine("  {$e['Uniqueid']} CallerID  Changed to: $tmpCallerID\n");
					$query = "UPDATE asterisk_log SET CallerID='" . $tmpCallerID . "', callstate='Dial' WHERE asterisk_id='" . $id . "'";
					mysql_checked_query($query);
				}

				//
				// Process "Hangup" events
				// Yup, we really get TWO hangup events from Asterisk!  (Even more with Ringgroups)
				// Obviously, we need to take only one of them....
				//
				// Asterisk Manager 1.1
				if ($e['Event'] == 'Hangup') {
					$id        = $e['Uniqueid'];
					$query     = "SELECT direction,contact_id FROM asterisk_log WHERE asterisk_dest_id = '$id' OR asterisk_id = '$id'";
					$result    = mysql_checked_query($query);
					$direction = mysql_fetch_array($result);
					//var_dump($direction);
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
							logLine("### [$id] FOUND outbound CALL\n");
							//
							// update entry in asterisk_log...
							//
							$rawData      = $callRecord['bitter']; // raw data from asterisk_log
							$query        = sprintf("UPDATE asterisk_log SET callstate='%s', timestampHangup=%s, hangup_cause=%d, hangup_cause_txt='%s' WHERE asterisk_id='%s'", //asterisk_dest_id was asterisk_id
								'Hangup', 'FROM_UNIXTIME('.time().')', $e['Cause'], $e['Cause-txt'], $id);
							$updateResult = mysql_checked_query($query);
							if ($updateResult) {
								$assignedUser = findUserIdFromChannel( $rawData['channel'] );

								//
								// ... on success also update entry in Calls module
								//

								//
								// Calculate call duration...
								//
								$failedCall      = FALSE;
								$hangupTime      = time();
								$callDurationRaw = 0; // call duration in seconds, only matters if timestampLink != NULL
								if ($rawData['timestampLink'] != NULL) {
									$callStartLink   = strtotime($rawData['timestampLink']);
									$callDurationRaw = $hangupTime - $callStartLink;
								} else {
									$failedCall = TRUE;
								}
								$callStart = strtotime($rawData['timestampCall']);

								logLine("  [$id] Measured call duration is $callDurationRaw seconds\n");

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
								//if (!$failedCall) {
									$callStatus = 'Held';
									$callName   = $mod_strings['ASTERISKLBL_GOING_OUT'];

									// This means call description was updated through AJAX so lets not overwrite the subject/description already assigned to the call.
									if (!empty($callRecord['sweet']['description'])) {
										$callName        = $callRecord['sweet']['name'];
										$callDescription = $callRecord['sweet']['description'];
									}
								// } else {
								//     $callStatus      = 'Missed';
								// 	$callName        = $mod_strings['CALL_NAME_MISSED'];
        //                             $callDescription = "{$mod_strings['CALL_DESCRIPTION_MISSED']} ({$e['Cause-txt']}\n";
								// 	$callDescription .= "------------------\n";
								// 	$callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['CALL_DESCRIPTION_PHONE_NUMBER'], $rawData['callerID']);
        //                             if( $rawData['opencnam'] ) {
        //                                 $callName .= " - " . $rawData['opencnam'];
        //                                 $callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['CALL_DESCRIPTION_CALLER_ID'], $rawData['opencnam']);
        //                             }

								// 	logLine("Adding OUTBOUND Failed Call, id=$id, call_id = " . $callRecord['sweet']['id'] . "\n");
								// }

                                // Establish Relationships with the Call and Contact/Account
                                $beanID = NULL;
                                $beanType = NULL;
                                $parentID        = NULL;
                                $parentType      = NULL;
                                if( !empty($direction['contact_id']) ){
                                    logLine("Contact Id already set by callListener to: " . $direction['contact_id'] . "\n");
                                    $beanID = $direction['contact_id'];
                                    $beanType = "Contacts";
                                }
                                else {
                                    $assocAccount = findSugarAccountByPhoneNumber($rawData['callerID']);
                                    if( $assocAccount != FALSE ) {
                                        logLine("Found a matching account, relating to account instead of contact\n");
                                        $beanID = $assocAccount['values']['id'];
                                        $beanType = $assocAccount['type'];
                                        $parentType = 'Accounts';
                                        $parentID   = $beanID;
                                    }
                                    else {
                                        $assoSugarObject = findSugarObjectByPhoneNumber($rawData['callerID']);
                                        $beanID = $assoSugarObject['values']['id'];
                                        $beanType = $assoSugarObject['type'];
                                    }
                                }
                                setRelationshipBetweenCallAndBean($callRecord['sweet']['id'],$beanType,$beanID);

                                if( $beanType == "Contacts" && !empty($beanID) ) {
                                    $assocAccount = findAccountForContact($beanID);
                                    if ($assocAccount) {
                                        $parentType = 'Accounts';
                                        $parentID   = $assocAccount;
                                    }
                                }

								//var_dump($parentType);
								//var_dump($parentID);
								logLine ("  Call start was " . gmdate('Y-m-d H:i:s', $callStart) . "\n");

								//
								// ... on success also update entry in Calls module
								//
								logLine( "  [$id] (OUTBOUND) Now updating record in /Calls/ id=" . $callRecord['sweet']['id'] . "...\n");

								//print_r($callRecord);
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
							}
						} else {
							logLine("[$id] FAILED TO FIND A CALL (note: there are two hangups per call, so this might not be an error)\n");
						}
					}
					else {
						//-----------------[ INBOUND HANGUP HANDLING ]----------------------

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
								'Hangup', 'FROM_UNIXTIME('.time().')', $e['Cause'], $e['Cause-txt'], $id);
							$updateResult = mysql_checked_query($query);
							if ($updateResult) {
								$assignedUser = findUserIdFromChannel( $rawData['channel'] );


								//
								// ... on success also update entry in Calls module
								//

								//
								// Calculate call duration...
								//
								$failedCall      = FALSE;
								$hangupTime      = time();
								$callDurationRaw = 0; // call duration in seconds, only matters if timestampLink != NULL
								if ($rawData['timestampLink'] != NULL) {
									$callStartLink   = strtotime($rawData['timestampLink']);
									$callDurationRaw = $hangupTime - $callStartLink;
								} else {
									$failedCall = TRUE;
								}
								$callStart = strtotime($rawData['timestampCall']);

								logLine ("  Measured call duration is $callDurationRaw seconds\n");

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
								if (!$failedCall) {
									$callStatus = 'Held';
									$callName = $mod_strings['ASTERISKLBL_COMING_IN'];

									// This means call description was updated through AJAX so lets not overwrite the subject/description already assigned to the call.
									if (!empty($callRecord['sweet']['description'])) {
										$callName        = $callRecord['sweet']['name'];
										$callDescription = $callRecord['sweet']['description'];
									}
								} else {
									$callStatus      = $sugar_config['asterisk_short_call_status'];  // User gets to choose if they should be Missed or Held, if Missed then it leaves an open activity which has to be closed.
									$callName        = $mod_strings['CALL_NAME_MISSED'];
                                    $callDescription = "{$mod_strings['CALL_DESCRIPTION_MISSED']} ({$e['Cause-txt']}\n";
									$callDescription .= "------------------\n";
									$callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['CALL_DESCRIPTION_PHONE_NUMBER'], $rawData['callerID']);
                                    if( $rawData['opencnam'] ) {
                                        $callName .= " - " . $rawData['opencnam'];
                                        $callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['CALL_DESCRIPTION_CALLER_ID'], $rawData['opencnam']);
                                    }

									logLine("  Adding INBOUND Missed (or Failed) Call, id=$id, call_id = " . $callRecord['sweet']['id'] . "\n");
								}


                                // Establish Relationships with the Call and Contact/Account
                                $beanID = NULL;
                                $beanType = NULL;
                                $parentID        = NULL;
                                $parentType      = NULL;
                                if( !empty($direction['contact_id']) ){
                                    logLine("Contact Id already set by callListener to: " . $direction['contact_id'] . "\n");
                                    $beanID = $direction['contact_id'];
                                    $beanType = "Contacts";
                                }
                                else {
                                    $assocAccount = findSugarAccountByPhoneNumber($rawData['callerID']);
                                    if( $assocAccount != FALSE ) {
                                        logLine("Found a matching account, relating to account instead of contact\n");
                                        $beanID = $assocAccount['values']['id'];
                                        $beanType = $assocAccount['type'];
                                        $parentType = 'Accounts';
                                        $parentID   = $beanID;
                                    }
                                    else {
                                        $assoSugarObject = findSugarObjectByPhoneNumber($rawData['callerID']);
                                        $beanID = $assoSugarObject['values']['id'];
                                        $beanType = $assoSugarObject['type'];
                                    }
                                }
                                setRelationshipBetweenCallAndBean($callRecord['sweet']['id'],$beanType,$beanID);

                                if( $beanType == "Contacts" && !empty($beanID) ) {
                                    $assocAccount = findAccountForContact($beanID);
                                    if ($assocAccount) {
                                        $parentType = 'Accounts';
                                        $parentID   = $assocAccount;
                                    }
                                }
                                else if( $beanType == "Accounts") {
                                    $parentType="Accounts";
                                    $parentID=$beanID;
                                }

								echo ("! Call start was " . gmdate('Y-m-d H:i:s', $callStart) . "\n");

								//
								// ... on success also update entry in Calls module
								//
								logLine( " Updating record in /Calls/ id=" . $callRecord['sweet']['id'] . "...\n");

								//print_r($callRecord);
								logLine("  NAME: " . $callRecord['sweet']['name'] . "\n");
								logLine("  DESCRIPTION: " . $callRecord['sweet']['description'] . "\n");


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
							} // End Inbound Case

							// In case of multiple extensions when a call is not answered, every extensions produces a failed call record,
                            // this will keep the first of those records but delete the rest. (LIMIT 1,999999999999 in query returns all but first match.)
                           	$query     = "SELECT asterisk_id FROM asterisk_log WHERE asterisk_dest_id='$id'";
							$result    = mysql_checked_query($query);
							$result_id = mysql_fetch_array($result);
							logLine("Cleaning up Failed Calls part1, asterisk_id = ".$result_id['asterisk_id']."\n");

							$query     = "SELECT call_record_id FROM asterisk_log WHERE asterisk_id='" . $result_id['asterisk_id'] . "' ORDER BY id ASC LIMIT 1, 999999999999";
							$result    = mysql_checked_query($query);

							while ($call_record_id = mysql_fetch_array($result)) {
                                $query = "DELETE FROM calls WHERE id='" . $call_record_id['call_record_id'] . "' AND name LIKE '{$mod_strings['CALL_NAME_MISSED']}%'";
								$rq    = mysql_checked_query($query);

								if( mysql_affected_rows() > 0 ) {
									logLine("Cleaning up Failed Calls part2, DELETED call_record_id = {$call_record_id['call_record_id']}\n");
                                    $query = "DELETE FROM calls_cstm WHERE id_c='{$call_record_id['call_record_id']}'";
                                     mysql_checked_query($query);
                                }
								//$total_result = mysql_fetch_array($rq);
								//var_dump($total_result);
							}
						}

					} // End if INBOUND hangup event
				}// End of HangupEvent.



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
						$query  = "UPDATE asterisk_log SET callstate='Connected', timestampLink=FROM_UNIXTIME(".time().") WHERE asterisk_dest_id='" . $e['Uniqueid1'] . "' OR asterisk_dest_id='" . $e['Uniqueid2'] . "'";
						$rc     = mysql_checked_query($query);
						// und vice versa .. woher immer der call kam
						// $query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=FROM_UNIXTIME(".time().") WHERE asterisk_id='" . $e['Uniqueid2'] . "'";
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
                            logLine("Deleting Call Record: " . $call_rec_id['call_record_id'] );
                            deleteCall( $call_rec_id['call_record_id'] );
						}

					} else {
						if ($e['Event'] == 'Bridge') //Outbound bridge event
						{
							$query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=FROM_UNIXTIME(".time().") WHERE asterisk_id='" . $e['Uniqueid1'] . "' OR asterisk_id='" . $e['Uniqueid2'] . "'";
							$rc    = mysql_checked_query($query);
						}
					}

                    // Here we add support for complicated Ring Groups such as x1 ---> 615  ---> 710,722,735
                    //                                                            \--> 620  ---> 810,811,812
                    // Check if both channels are internal... Then, check the asterisk_log table to see if an entry exists where Channel matches one of them... if so then change it out.
                    // TBD: does answering on a cell phone and not pressing 1 to accept cause a bridge event that messes this up?
                    if( isCallInternal($e['Channel1'],$e['Channel2'] )) {
                        logLine("Internatl Bridge Event Detected\n");
                        if( preg_match('/(.*);(.*)/',$e['Channel1'],$matches) ) {
                            $chanToFind = $matches[1] . '%';
                            $query     = "SELECT id FROM asterisk_log WHERE channel like '$chanToFind' and direction='I' ";
                            logLine("Internal: $query\n");
                            $result    = mysql_checked_query($query);
                            // TODO clean up all these logLines.
                            if( mysql_num_rows( $result) > 1){
                                logLine("RG-Bridge ERROR: MULTIPLE MATCHING LINES IN ASTERISK LOG... BRIDGE LOGIC ISN'T BULLETPROOF\n");
                            }
                            else if( mysql_num_rows($result) == 1 ) {
                                logLine(" RG-Bridge Detected changing the channel to: {$e['Channel2']}\n" );
                                $result_id = mysql_fetch_array($result);
                                $chan2 = $e['Channel2'];
                                $theId = $result_id['id'];
                                $query = "UPDATE asterisk_log SET channel='$chan2' WHERE id='$theId'";
                                logLine("UPDATE QUERY: $query\n");
                                mysql_checked_query($query);
                            }
                        }
                        else {
                            logLine("RG-Bridge didn't match regex.\n");
                        }
                    }
				}
				//Asterisk Manager 1.0

				/*if($e['Event'] == 'Link')
				{
				$query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=FROM_UNIXTIME(".time().") WHERE asterisk_id='" . $e['Uniqueid1'] . "' OR asterisk_id='" . $e['Uniqueid2'] . "'";
				$rc = mysql_checked_query($query);

				// und vice versa .. woher immer der call kam
				// $query = "UPDATE asterisk_log SET callstate='Connected', timestampLink=FROM_UNIXTIME(".time().") WHERE asterisk_id='" . $e['Uniqueid2'] . "'";
				// $record = mysql_query($query);
				};*/

				// Reset event buffer
				$event = '';
			}
        }

        // handle partial packets
        if ($event_started)
		{
            $event .= $buffer;
        }
		else if (strstr($buffer, 'Event:'))
		{
            $event         = $buffer;
            $event_started = true;
        }

        // for if the connection to the sql database gives out.
        // TODO Find a better way to check the connection.  I think on Shared Hosting Servers mysql_ping might be disabled which causes this to always reconnect.
        if (!mysql_ping($sql_connection)) {
            //here is the major trick, you have to close the connection (even though its not currently working) for it to recreate properly.
            logLine("__MySQL connection lost, reconnecting__\n");
            mysql_close($sql_connection);
            $sql_connection = mysql_connect($sugar_config['dbconfig']['db_host_name'], $sugar_config['dbconfig']['db_user_name'], $sugar_config['dbconfig']['db_password']);
            $sql_db         = mysql_select_db($sugar_config['dbconfig']['db_name']);
        }

    }

    logLine(getTimestamp() . "Event loop terminated, attempting to login again\n");
    sleep(1);
}

// TODO i've seen asteriskLogger crash due to a script executing too long error...  It was on a pretty budget shared hosting server.
// Hasn't ever happened to me personally... but still something noteworthy.

exit(0);



// ******************
// Helper functions *
// ******************

function isCallInternal($chan1, $chan2) {
   global $asteriskMatchInternal;
   return (preg_match($asteriskMatchInternal,$chan1) && preg_match($asteriskMatchInternal, $chan2));
}

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
        LogLine("! AMI Event '". $event['Event']. " suppressed.\n");
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

/**
 * Removes a call record from the database.
 * @param $callRecordId - Call Record ID.  Note: param is assumed to be sanitized.
 */
function deleteCall( $callRecordId ) {
    // NOTE: there is one other place in this file that Delete's a call, so if this code is ever refactored
    //       to use SOAP, be sure to refactor that one.
    $query = "DELETE FROM calls WHERE id='$callRecordId'";
    $rc = mysql_checked_query($query);
    $query = "DELETE FROM calls_cstm WHERE id='$callRecordId'";
    mysql_checked_query($query);
    return $rc;
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
    global $soapClient, $soapSessionId, $verbose_logging;
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
        logLine("! FindCallByAsteriskDestId - Found entry in asterisk_log recordId=$callRecId\n");

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
        if( $verbose_logging ) {
			var_dump($resultDecoded);
		}
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
    logLine( "! Warning, FindCallByAsteriskDestId results set was empty!\n");
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
// Attempt to find a Sugar Account with a matching phone number.
//
function findSugarAccountByPhoneNumber($aPhoneNumber)
{
    global $soapClient, $soapSessionId;
    logLine("# +++ find AccountByPhoneNumber($aPhoneNumber)\n");

    // Add if phonenumber .length == 10
    $searchPattern = $aPhoneNumber;

    $aPhoneNumber = preg_replace( '/\D/', '', $aPhoneNumber); // removes everything that isn't a digit.
    if( preg_match('/([0-9]{10})$/',$aPhoneNumber,$matches) ){
        $aPhoneNumber = $matches[1];
    }

    $regje = preg_replace( '/(\d)/', '$1\[^\\d\]*',$aPhoneNumber);
    $regje = '(' . $regje . ')$';

    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Accounts',
        'query' => "accounts.phone_office REGEXP '$regje' OR accounts.phone_alternate REGEXP '$regje'",
    );

    // print "--- SOAP get_entry_list() ----- ARGS ----------------------------------------\n";
    // var_dump($soapArgs);
    // print "-----------------------------------------------------------------------------\n";

    $soapResult = $soapClient->call('get_entry_list', $soapArgs);

    //print "--- SOAP get_entry_list() FOR GET CONTACT ------------------------------------\n";
    //var_dump($soapResult);
    //print "-----------------------------------------------------------------------------\n";

    if( !isSoapResultAnError($soapResult))
    {
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
        //print "--- Decoded get_entry_list() FOR GET CONTACT --------------------------------------\n";
        //var_dump($resultDecoded);
        //print "-----------------------------------------------------------------------------\n";
        return array(
            'type' => 'Accounts',
            'values' => $resultDecoded
        );
    }

    return FALSE;
}


//
// Attempt to find a Sugar object (Contact,..) by phone number
//
// NOTE: As of v2.2, callListener now updates a column in asterisk_log table with contact_id so it doesn't have to perform
// a complex query each time.  But, since callListener only works when you're logged into sugar and have "Call Notification" on...
// we still have to try and find object related to phone number here for the other cases.
//
//
function findSugarObjectByPhoneNumber($aPhoneNumber)
{
    global $soapClient, $soapSessionId;
    logLine("### +++ find ContactByPhoneNumber($aPhoneNumber)\n");

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
    // TODO make the '7' below a configurable parameter... some may prefer to match on 10.
	if( preg_match('/([0-9]{7})$/',$aPhoneNumber,$matches) ){
		$aPhoneNumber = $matches[1];
	}

	$regje = preg_replace( '/(\d)/', '$1\[^\\d\]*',$aPhoneNumber);
	$regje = '(' . $regje . ')$';

    logLine("  findSugarObjectByPhoneNumber: Contact query components- Phone: $aPhoneNumber   RegEx: $regje\n");
    //*******/



    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Contacts',
        'select_fields' => array( 'id','account_id','last_name' ),
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

    if( !isSoapResultAnError($soapResult))
    {
        // TODO implement a working array_unique
        //$uniqueEntryList = array_unique($soapResult['entry_list'] );
        $uniqueEntryList = $soapResult['entry_list'];
        $resultDecoded = decode_name_value_list($uniqueEntryList[0]['name_value_list']);

        if( count($uniqueEntryList) > 1 ) {
            $foundMultipleAccounts = FALSE;
            $account_id = $resultDecoded['account_id'];  // TODO Possible Undefined index Notice could result here...
            //logLine(print_r($resultDecoded,true));
            // TODO I had 43 entries returned for 2 contacts with matching number... need better distinct support.  Apparently, no way to do this via soap... probably need to create a new service endpoint.
            // TODO (continued) if you have a contact with no account associated... this will not associate it with the contact b/c it thinks there are 43 unique contacts being returned.
            // Perhaps we should just do a database call instead and use the same logic in place in callListener.
            for($i=1; $i<count($uniqueEntryList); $i++ ) {
                $resultDecoded = decode_name_value_list($uniqueEntryList[$i]['name_value_list']);
               // logLine(print_r($resultDecoded,true));
                if( isset($resultsDecoded['account_id']) &&
                    $account_id != $resultDecoded['account_id'] ) {
                    $foundMultipleAccounts = TRUE;
                }
            }
            if( !$foundMultipleAccounts )
            {
                $result = array();
                $result['id'] = $account_id;
                // TODO there is an error case here where you could have multiple contacts with same number and none of them are assigned to ANY account.
                logLine("Found multiple contacts (" . count($uniqueEntryList) . ") -- all belong to same account, associating call with account {$result['id']}\n");
                return array( 'type' => 'Accounts', 'values' => $result );
            }
            else {
                logLine("Multiple contacts matched multiple accounts, Not associating\n");
                return FALSE;
            }
        }

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
    logLine("### +++ findAccountForContact($aContactId)\n");

    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Contacts',
        'module_id' => $aContactId,
        'related_module' => 'Accounts',
        'related_module_query' => '',
        'deleted' => 0
    );

    $soapResult = $soapClient->call('get_relationships', $soapArgs);

    // TODO check if error exists first to prevent Notice about index not existing in log.
    if( !isSoapResultAnError($soapResult) ) {
        // var_dump($soapResult);

        $assocCount = count($soapResult['ids']);

        if ($assocCount == 0) {
            logLine(" + No associated account found\n");
            return FALSE;
        } else {
            if ($assocCount > 1) {
                logLine("! WARNING: More than one associated account found, using first one.\n");
            }
            $assoAccountID = $soapResult['ids'][0]['id'];
            logLine(" + Associated account is $assoAccountID\n");
            return $assoAccountID;
        }
    }
}

/**
 * prints soap result info
 * Returns true if results were returned, FALSE if an error or no results are returned.
 *
 * @param $soapResult
 */
function isSoapResultAnError($soapResult) {
    $retVal = FALSE;
    if ( isset($soapResult['error']['number']) && $soapResult['error']['number'] != 0) {
        logLine("! ***Warning: SOAP error*** " . $soapResult['error']['number'] . " " . $soapResult['error']['string'] . "\n");
        $retVal = TRUE;
    }
    else if( !isset($soapResult['result_count']) || $soapResult['result_count'] == 0 ) {
        logLine("! No results returned\n");
        $retVal = TRUE;
    }
    return $retVal;
}

/**
 * Performs a soap call to set a relationship between a call record and a bean (contact)
 * @param $callRecordId the call record id.
 * @param $beanType usually "Contacts"
 * @param $beanId
 */
function setRelationshipBetweenCallAndBean($callRecordId,$beanType, $beanId) {
    global $soapSessionId, $soapClient,$verbose_logging;

    if( !empty($callRecordId) && !empty($beanId) && !empty($beanType)  ) {
        $soapArgs   = array(
            'session' => $soapSessionId,
            'set_relationship_value' => array(
                'module1' => 'Calls',
                'module1_id' => $callRecordId,
                'module2' => $beanType,
                'module2_id' => $beanId
            )
        );

        logLine("  Establishing relation to $beanType... Call ID: $callRecordId to Bean ID: $beanId\n");
        if( $verbose_logging ) {
            var_dump($soapArgs);
        }
        $soapResult = $soapClient->call('set_relationship', $soapArgs);
        isSoapResultAnError($soapResult);
    }
    else {
        logLine("! Invalid Arguments passed to setRelationshipBetweenCallAndBean callRecordId=$callRecordId, beanId=$beanId, beanType=$beanType\n");
    }
}

///
/// Given the channel ($rawData['channel']) from the AMI Event, this returns the user ID the call should be assigned to.
/// If a suitable user extension cannot be found, Admin is returned
///
function findUserIdFromChannel( $channel )
{
	global $userGUID;
	$assignedUser = $userGUID; // Use logged in user as fallback

	$asteriskExt = extractExtensionNumberFromChannel($channel);

	$maybeAssignedUser = findUserByAsteriskExtension($asteriskExt);
	if ($maybeAssignedUser) {
		$assignedUser = $maybeAssignedUser;
		logLine("! Assigned user id set to $assignedUser\n");
	}
	else {
		$assignedUser = $userGUID;
		logLine(" ! Assigned user will be set to Administrator.\n");
	}

	return $assignedUser;
}

//
// Attempt to find assigned user by asterisk ext
// PRIVATE METHOD: See findUserIdFromChannel
//
function extractExtensionNumberFromChannel( $channel )
{
	global $sugar_config;
	$asteriskExt = FALSE;
	$channelSplit = array();
	logLine("Looking for user extension number in: $channel\n");

	// KEEP THIS BLOCK OF CODE IN SYNC WITH OUTBOUND
	// BR: This cases matches things like Local/LC-52@from-internal-4bbbb
	$pattern = $sugar_config['asterisk_dialin_ext_match'];
	if( !startsWith($pattern,'/') ) {
		$pattern = '/' . $pattern . '/i';
	}
	if( !empty($sugar_config['asterisk_dialin_ext_match']) && preg_match($pattern, $channel, $regs)) {
		logLine("Matched User REGEX.  Regex: " . $regs[1] . "\n");
		$asteriskExt = $regs[1];
	}
	// This matches the standard cases such as SIP/### or IAX/###
	else if (eregi('^([[:alpha:]]+)/([[:alnum:]]+)-', $channel, $channelSplit) > 0){
		$asteriskExt = $channelSplit[2];
		logLine("Channel Matched SIP/### style regex.  Ext is:" . $asteriskExt . "\n");
	}
	else {
		$asteriskExt = FALSE;
	}

	return $asteriskExt;
}

//
// Locate user by asterisk extension
// NOTE: THIS RETURNS JUST AN ID
// PRIVATE METHOD: See findUserIdFromChannel
//
function findUserByAsteriskExtension($aExtension)
{
    logLine("### +++ findUserByAsteriskExtension($aExtension)\n");
    // The query below is actually pretty clever.  Recall that user extensions can be a comma seperated list.
    // The 4 conditions are necessary 1) To match single extension case, 2) to match first extension in the list
    // 3) to match one in the middle of list, 4) matches one at the end of a list.
    $qry = "select id,user_name from users join users_cstm on users.id = users_cstm.id_c where ".
           "(users_cstm.asterisk_ext_c='$aExtension' or users_cstm.asterisk_ext_c LIKE '$aExtension,%' ".
           "OR users_cstm.asterisk_ext_c LIKE '%,$aExtension,%' OR users_cstm.asterisk_ext_c LIKE '%,$aExtension') and status='Active'";

	$result = mysql_checked_query($qry);
	if( $result ) {
		$row = mysql_fetch_array($result);

        // All this if statement does is detect if multiple users were returned and if so display warning.
        if( mysql_num_rows($result) > 1 ) {
            $firstUser = $row['user_name'];
            $usernames = $row['user_name'];
            while( $row2 = mysql_fetch_array($result) ) {
                $usernames .= ", " . $row2['user_name'];
            }
            logLine("### __WARNING__ Extension $aExtension matches the following users: $usernames!  Call will be assigned to: $firstUser!");
        }

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

	if( $mysql_loq_queries || $mysql_log_results )
	{
		logLine(" +++ mysql_checked_query()\n");
	}

    $query = trim($aQuery);
    if ($mysql_loq_queries) {
		logLine("  ! SQL: $query\n");
    }

    // Is this is a SELECT ?
    $isSelect = eregi("^select", $query);

    $sqlResult = mysql_query($query);

    if ($mysql_log_results) {
        if (!$sqlResult) {
            // Error occured
            logLine("! SQL error " . mysql_errno() . " (" . mysql_error() . ")\n");
        } else {
            // SQL succeeded
            if ($isSelect) {
                logLine("  --> Rows in result set: " . mysql_num_rows($sqlResult) . "\n");
            } else {
                logLine("  --> Rows affected: " . mysql_affected_rows() . "\n");
            }
        }
    }


    return $sqlResult;
}

function logLine($str)
{
	global $sugar_config;

    if( !endsWith($str,"\n") ){
        $str = $str . "\n";
    }

    print($str);

	// if logging is enabled.
	if( !empty($sugar_config['asterisk_log_file']) )
	{
		$myFile = $sugar_config['asterisk_log_file'];
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

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

/**
 * Reads all lines from the socket until timeout occurs.
 * @param $socket
 * @param $timeout OPTIONAL (default is 500000 == 1/2 sec)
 * @return string
 */
function AMI_ReadResponse($socket,$timeout=500000) {
    $retVal = '';
   // Sets timeout to 1/2 a second
    stream_set_timeout($socket,0,$timeout);
    while (($buffer = fgets($socket, 20)) !== false) {
        $retVal .= $buffer;
    }
    return $retVal;
}

function AMI_WasCmdSuccessful($response) {
    return preg_match('/.*Success.*/s',$response);
}


/**
 * formats the string in a markdown ``` code block indented by 4 spaces
 * @param $str
 * @param $indent - OPTIONAL by default it's "    " (4 spaces)
 * @return string
 */
function markdown_indent($str,$indent="    ") {
    $str = preg_replace("/(\r?\n)/i","$1$indent",$str);
    $str = trim($str);
    $str = "$indent```\n$indent$str\n$indent```";
    return $str;
}


?>
