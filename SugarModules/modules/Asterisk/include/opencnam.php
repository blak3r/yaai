<?php
/**
 * User: blake
 * Date: 2/10/13
 * OpenCNAM.com api implementation
 */
class opencnam
{
    private $_account_sid = '';
    private $_auth_token = '';

    function __construct($account_sid = '', $auth_token = '') {
        $_account_sid = $account_sid;
        $_auth_token = $auth_token;
    }

    /**
     * Fetch a list of records from OpenCNAM
     *
     * @param string $phoneNumber 10 digit US telephone number
     *
     * @return array fetch results of OpenCNAM lookup
     *
     * @todo implement a number cleaner that always formats input into 10 digits
     */
    public function fetch($phoneNumber) {
        $credentials = "";
        if( !empty($_account_sid) && !empty($_auth_token) ) {
            $credentials = "&auth_token=$_auth_token&account_sid=$_account_sid";
        }

        // TODO need to format into E194 first to work outside the us.

        $phoneNumber = preg_replace('/\D/i', '', $phoneNumber); // Removes everything but digits.
        $request_url = "https://api.opencnam.com/v2/phone/" . $phoneNumber . "?format=text" . $credentials;
        $found = false;
        $response = file_get_contents($request_url); // First call returns with 404 immediately with free api, 2nd call will succeed. See https://github.com/blak3r/yaai/issues/5
        // "Currently running a lookup for phone '7858647222'. Please check back in a few seconds."
        if (empty($response) || strpos($response, "unavailable") !== false) {
            $response = "";
        } else {
            $found = true;
        }
        return $response;
    }
}
