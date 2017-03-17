<?php

    require_once("auth.class.php");

    class profile extends auth{
        
        function __construct($sessionId){
            $this->setSessionId($sessionId);
        }
        
        function get(){
            $this->setMethod("getProfile");
            $data = $this->sendRequest();
            if(isset($data->result))return $data->result;
        }
    }
