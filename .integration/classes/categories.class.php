<?php

    require_once("request.class.php");

    class categories extends request{
        
        private $email = '';
        
        function __construct(){
        }
        
        function all(){
            $this->setMethod("getCategories");
            $data = $this->exec();
            if(is_array($data))return $data;
        }

    }
