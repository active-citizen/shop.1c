<?php

    require_once("auth.class.php");

    class points extends auth{
        
        function __construct($sessionId){
            $this->setSessionId($sessionId);
        }
        
        function summary(){
            $this->setMethod("getPointsSummary");
            $data = $this->sendRequest();
            if(isset($data->result))return $data->result;
        }

        function history(){
            $this->setMethod("getPointsHistory");
            $data = $this->sendRequest();
            if(isset($data->result))return $data->result;
        }

    }
