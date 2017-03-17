<?php

    require_once("request.class.php");

    class products extends request{
        
        private $email = '';
        
        function __construct(){
        }
        
        function all(){
            $this->setMethod("getProducts");
            $data = $this->exec();
            if(is_array($data))return $data;
        }

    }
