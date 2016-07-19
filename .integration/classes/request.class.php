<?php

    class request{
        
        private $urlPrefix = 'https://emp.mos.ru';
        private $urlPostxix = '';
        private $token = 'ag_uat_token3';
        protected $postData = '';
        private $timeout = 10;
        
        function riseError(){
        }
        
        function setMethod($method){
            
            switch($method){
                case 'getProfile':
                    $this->urlPostxix = '/v2.0.0/agprofile/getProfile';
                break;
                default:
                    $this->riseError("Unknown method");
                break;
            }
            
            
        }
        
        
        function setToken($token){
            $this->token = $token;
        }
        
        function setData($data = array()){
            $this->postData = $data;
        }
        
        function exec(){
            
            $this->postData["token"] = $this->token;
            
            
            $url = $this->urlPrefix.$this->urlPostxix;
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            $data = json_encode($this->postData);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $data = @json_decode(curl_exec ($ch));
            if($data)return $data;
            $this->riseError("Error during parse JSON answer");
            return false;
        }
        
    }
