<?php

require_once('OAuth.php');

class StackMob {

    private $response;

    function __construct($http_method, $uri_info_path, $body) {
        $consumer_key = "6a12313c-e7fd-4216-a781-ff202761618e";
        $consumer_secret = "0553e6c1-667b-46d1-b71c-ea4daa2e990f";
        $rest_endpoint = "https://api.stackmob.com";
        $body;


        $endpoint = "$rest_endpoint/$uri_info_path";
        $params = NULL;

        $consumer = new OAuthConsumer($consumer_key, $consumer_secret, NULL);

        // Setup OAuth request - Use NULL for OAuthToken parameter
        $request = OAuthRequest::from_consumer_and_token($consumer, NULL, $http_method, $endpoint, $params);

         // Sign the constructed OAuth request using HMAC-SHA1 - Use NULL for OAuthToken parameter
        $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);

        // Extract OAuth header from OAuth request object and keep it handy in a variable
        $oauth_header = $request->to_header();

        $this->response = $this->send_request($request->get_normalized_http_method(), $endpoint, $oauth_header, $body);
    }


   private function send_request($http_method, $url, $auth_header = null, $postData = null) {

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        switch ($http_method) {
            case 'GET':
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/vnd.stackmob+json;",
                    'Content-Length: 0',
                    "Accept: application/vnd.stackmob+json; version=0",
                    $auth_header));
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/vnd.stackmob+json; version=0',
                    'Content-Length: ' . strlen(json_encode($postData)),
                    "Accept: application/vnd.stackmob+json; version=0",
                    $auth_header));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/vnd.stackmob+json;",
                    'Content-Length: ' . strlen(json_encode($postData)),
                    "Accept: application/vnd.stackmob+json; version=0",
                    $auth_header));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/vnd.stackmob+json;",
                    'Content-Length: 0',
                    "Accept: application/vnd.stackmob+json; version=0", $auth_header));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
                break;
        }

        echo $curl . "\n\n";

        $response = curl_exec($curl);
        if (!$response) {
            $response = curl_error($curl);
        }
        curl_close($curl);
        return $response;
    }

}
