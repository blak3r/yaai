
<?php

$debugMode = true;
// specify the REST web service to interact with
$url = 'http://127.0.0.1:8888/sugarcrm/service/v2/rest.php';
// Open a curl session for making the call
$ch = curl_init($url);
// Tell curl to use HTTP POST
curl_setopt($ch, CURLOPT_POST, true);
// Tell curl not to return headers, but do return the response
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the POST arguments to pass to the Sugar server
$parameters = array(
    'user_name' => 'admin',
    'password' => md5('adF32wjkh'),
);
$json = json_encode($parameters);
$postArgs = 'method=login&input_type=json&
      response_type=json&rest_data=' . $json;
curl_setopt($ch, CURLOPT_POSTFIELDS, $postArgs);


if( $debugMode ) {
    print "Debugging...";
    // CURLOPT_VERBOSE: TRUE to output verbose information. Writes output to STDERR,
    // or the file specified using CURLOPT_STDERR.
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'rw+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
}

$returndata = curl_exec ($ch);

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
$result = json_decode($response);
// Echo out the session id
echo $result['id'];