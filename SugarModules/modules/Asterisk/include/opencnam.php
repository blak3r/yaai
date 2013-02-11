<?php
/**
 * User: blake
 * Date: 2/10/13
 * OpenCNAM.com api implementation
 */
class opencnam
{
    protected $account_sid;
    protected $auth_token;

    function __construct($account_sid = '', $auth_token = '') {
        $this->account_sid = $account_sid;
        $this->auth_token = $auth_token;
    }

    /**
     * Fetch a list of records from OpenCNAM
     *
     * @param string $phoneNumber 10 digit US telephone number or E194 formatted number
     *
     * @return array fetch results of OpenCNAM lookup
     */
    public function fetch($phoneNumber) {
        $credentials = "";
        //   print "OPENCNAM SET IN FETCH: " . $the_account_sid . $the_auth_token . "\n";
        if( !empty($this->account_sid) && !empty($this->auth_token) ) {
            $credentials = "&auth_token={$this->auth_token}&account_sid={$this->account_sid}";
        }

        // TODO need to format into E194 first to work outside the us.
        $phoneNumber = preg_replace('/\D/i', '', $phoneNumber); // Removes everything but digits.
        if( strlen($phoneNumber) == 10 ) {
            // Assume it's a US phone number.
            $phoneNumber = "+1" . $phoneNumber;
        }
        else if(strlen($phoneNumber) > 10 ) {
            $phoneNumber = "+" . $phoneNumber;
        }

        $request_url = "https://api.opencnam.com/v2/phone/" . $phoneNumber . "?format=pbx" . $credentials;
        //print "OPENCNAM URL: " . $request_url . "\n";
        $response = file_get_contents($request_url);
        $response = trim($response);
        // "Currently running a lookup for phone '7858647222'. Please check back in a few seconds."
        if (empty($response) || strpos($response, "unavailable") !== false) {
            $response = "";
        }
        return $response;
    }
}
