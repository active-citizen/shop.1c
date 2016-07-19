<?php

    require_once("request.class.php");

    class manufacturers extends request{
        
        private $email = '';
        
        function __construct(){
        }
        
        function all(){
            $this->setMethod("getManufacturers");
            $data = $this->exec();
            if(is_array($data))return $data;
        }

    }
