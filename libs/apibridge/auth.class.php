<?php

    require("request.class.php");

    class auth extends request{
        private $sessionId = '';
        private $urlPrefix = '';
        private $login = '';
        private $password = '';
        
        function __construct(
            $sessionId = ''
        ){
            if($sessionId)setSessionId($sessionId);
        }

        function setSessionId($sessionId){
            $this->sessionId = $sessionId;
        }
        
        function setLogin($login){
            $this->login = $login;
        }

        function setPassword($password){
            echo "111";
            $this->password = $password;
        }

        
        function login(){
            $this->setMethod('getProfile');
            $data = $this->sendRequest();
            if(isset($data->session_id))return $data->session_id;
            return false;
        }
        
        function sendRequest(){
            
            
            $data["auth"] = array();
            if($this->login)$data["auth"]["login"] = $this->login;
            if($this->password)$data["auth"]["password"] = $this->password;
            if($this->sessionId)$data["auth"]["session_id"] = $this->sessionId;
            $this->setData($data);
            print_r($this);
            return $this->exec();
        }
    }
