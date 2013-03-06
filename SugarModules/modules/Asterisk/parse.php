<?php
/**
 * User: blake
 * Date: 2/10/13
 * Time: 6:26 PM
 * Simple curl wrapper for parse BaaS Rest api.
 *
 * Dependencies: expects there to be a ca-bundle-mozilla.crt in the same directory in order to work on windows.
 *
 * Sample Usage:
 * $arr = array();
 * $arr['message'] = "this is a test";
 * $arr['call_record_id'] = "Some ID";
 * $p = new ParseBackendWrapper();
 * $p->customCodeMethod("send_push", $arr);
 */
class ParseBackendWrapper
{

    /**
     * @param $method_name - is the custom cloud code method name.  It'll be used to form the URL as in:
     *                       "https://api.parse.com/1/functions/" . $method_name
     * @param $postData - an array of key value pairs that will be json encoded.
     * @return mixed
     */
    public function customCodeMethod( $method_name, $postData ) {
        //$ca_path = "ca-bundle-mozilla.crt"; // This file was originally downloaded from: http://curl.haxx.se/docs/caextract.html
        $ca_path = "api.parse.com.crt";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.parse.com/1/functions/" . $method_name);
        curl_setopt($ch, CURLOPT_CAINFO, $ca_path); // Needed on windows boxes!
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "X-Parse-Application-Id: v7GjYfotqF0to7rkw3yfarS7WfHJTTPytwtqpa0T",  // FIXME Remove these keys from source
            "X-Parse-REST-API-Key: nvCZ08juhI8BomopJuCD7qR9fJEGMvrlx76gxVgI",
            "Content-Type: application/json"
        ));

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // receive server response ...
        $server_output = curl_exec($ch);

        curl_close($ch);

        return $server_output;
    }
}
