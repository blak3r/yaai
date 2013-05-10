<?php

    if(!defined('sugarEntry'))define('sugarEntry', true);

    require_once('service/v4_1/SugarWebServiceImplv4_1.php');

    class SugarWebServiceImplv4_1_custom extends SugarWebServiceImplv4_1
    {
        /*
         * Returns the session id if authenticated
         *
         * @param string $session
         * @return string $session - false if invalid.
         *
         */
        function get_license($session, $oid)
        {
            $GLOBALS['log']->info('Begin: SugarWebServiceImplv4_1_custom->example_method');
            $error = new SoapError();
			$retVal = array();
			

            //authenticate
            if (!self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', '', '', '',  $error))
            {
                $GLOBALS['log']->info('End: SugarWebServiceImplv4_1_custom->example_method.');
				//$retVal['status'] = "ERROR: Invalid Session ID";
				//$retVal['data'] = '';
			}
			else {
				// TODO Validate OID is valid
		
$selectQry = <<<ENDSELECT
select * from cl_customerlicense
left join cl_customerlicense_cstm on cl_customerlicense.id = cl_customerlicense_cstm.id_c
join cl_customerlicense_accounts_c on cl_customerlicense.id = cl_customerlicense_accounts_c.cl_customerlicense_accountscl_customerlicense_ida
join accounts on cl_customerlicense_accounts_c.cl_customerlicense_accountsaccounts_idb = accounts.id
join accounts_cstm on accounts.id = accounts_cstm.id_c
where accounts_cstm.alertus_oid_c = "$oid"
ENDSELECT;

				$results = $GLOBALS['db']->query($selectQry);

				while($row = $GLOBALS['db']->fetchByAssoc($results) ) {
					$retVal = $row;
					//$retVal['data'] = $row;
					//$retVal['status'] = 'Successful';
					//$retVal = json_encode($row);
				}
			}

			
            return $retVal;//$session . " oid: " . $oid;
        }
    }

?>