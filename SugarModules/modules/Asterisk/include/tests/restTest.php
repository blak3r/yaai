
<?php

$debugMode = true;
// specify the REST web service to interact with
$url = 'http://localhost:8888/sugarcrm/service/v2/rest.php';
// Open a curl session for making the call
$ch = curl_init($url);
// Tell curl to use HTTP POST
curl_setopt($ch, CURLOPT_POST, true);
// Tell curl not to return headers, but do return the response
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the POST arguments to pass to the Sugar server
$parameters = array(
    'user_auth' => array(
        'user_name' => 'admin',
        'password' => md5('SOMETHING'),
    ),
);
$json = json_encode($parameters);
$postArgs = 'method=login&input_type=json&response_type=json&rest_data=' . $json;

print $postArgs;
curl_setopt($ch, CURLOPT_POSTFIELDS, $postArgs);


if( $debugMode ) {
    print "Debugging...";
    // CURLOPT_VERBOSE: TRUE to output verbose information. Writes output to STDERR,
    // or the file specified using CURLOPT_STDERR.
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'rw+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
}

$response = curl_exec ($ch);

if( $debugMode ) {
    !rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    print $verboseLog;
}


$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// curl_close($http);
if( !preg_match( '/2\d\d/', $http_status ) ) {
    print "\n\n!!! ERROR: HTTP Status Code == " . $http_status . " (302 also isn't an error)\n";
}

print $response;

var_dump($response);
///--------------//
$result = json_decode($response);

print "RESULT:";
var_dump($result);

//print "asdkfljaslkfajslfdkj" . print_r($result,true);
print "SESSION" . $response['id'];

// Get the session id
$sessionId = $result['id'];

print $sessionId . " is the session id";
// Now, let's add a new Accounts record
$parameters = array(
    'session' => $session,
    'module' => 'Accounts',
    'name_value_list' => array(
        array('name' => 'name', 'value' => 'New Account'),
        array('name' => 'description', 'value' => 'This is an
account created from a REST web services call'),
    ),
);
$json = json_encode($parameters);
$postArgs = 'method=set_entry&input_type=json&
     response_type=json&rest_data=' . $json;
curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
// Make the REST call, returning the result
$response = curl_exec($session);
// Convert the result from JSON format to a PHP array
$result = json_decode($response);
// Get the newly created record id
$recordId = $result['id'];

print "new record id = " . $recordId;