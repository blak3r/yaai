<?php

    require_once('service/v4_1/registry.php');

    class registry_v4_1_callinize extends registry_v4_1
    {
        protected function registerFunction()
        {
            parent::registerFunction();
            $this->serviceClass->registerFunction('get_license', array('session'=>'xsd:string', 'oid'=>'xsd:string'), array('return'=>'xsd:string'));
        }
    }

?>