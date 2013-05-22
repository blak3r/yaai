<?php

    chdir('../../..');
    require_once('SugarWebServiceImplv4_1_callinize.php');

    $webservice_path = 'service/core/SugarRestService.php';
    $webservice_class = 'SugarRestService';
    $webservice_impl_class = 'SugarWebServiceImplv4_1_callinize';

    $registry_path = 'custom/service/callinize/registry.php';
    $registry_class = 'registry_v4_1_callinize';

    $location = 'custom/service/callinize/rest.php';

    require_once('service/core/webservice.php');

?>