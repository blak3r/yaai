<?php

    require_once('service/v4_1/registry.php');

    class registry_v4_1_callinize extends registry_v4_1
    {
        protected function registerFunction()
        {
            parent::registerFunction();
            $this->serviceClass->registerFunction('find_beans_with_phone_number', array('session'=>'xsd:string', 'phone_number'=>'xsd:string', 'module_order'=>'xsd:string', 'stop_on_find'=>'xsd:bool'), array('return'=>'xsd:string'));
            $this->serviceClass->registerFunction('originate_call', array('session'=>'xsd:string', 'from_extension'=>'xsd:string', 'to_phone_number'=>'xsd:string'));
        }
    }

?>