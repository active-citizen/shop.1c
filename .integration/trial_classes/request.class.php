<?php

    class request{
        
        private $urlPrefix = 'https://emp.mos.ru';
        private $urlPostxix = '';
        private $token = 'ag_uat_token3';
        private $jsonPost = 1;
        private $contentType = "Content-Type: application/json";
        private $requestType = 'POST';
        protected $postData = '';
        private $timeout = 10;
        
        function riseError(){
        }
        
        function setMethod($method){
            
            switch($method){
                case 'getProfile':
                    $this->urlPostxix = '/v2.0.0/agprofile/getProfile';
                break;
                case 'getPointsSummary':
                    $this->urlPostxix = '/v0.3/poll/getPoints';
                break;
                case 'getPointsHistory':
                    $this->urlPostxix = '/v2.0.0/poll/getHistory';
                break;
                case 'getOrders':
                    $this->urlPostxix = '/rest/getOrders';
                    $this->urlPrefix = 'http://arm.ag.mos.ru';
                    $this->jsonPost = 0;
                    $this->contentType = 'Content-Type: application/x-www-form-urlencoded';
                break;
                case 'getCategories':
                    $this->urlPostxix = '/rest/getCategories';
                    $this->urlPrefix = 'http://arm.ag.mos.ru';
                    $this->jsonPost = 0;
                    $this->contentType = 'Content-Type: application/x-www-form-urlencoded';
                    $this->requestType = 'GET';
                break;
                case 'getProducts':
                    $this->urlPostxix = '/rest/getProducts';
                    $this->urlPrefix = 'http://arm.ag.mos.ru';
                    $this->jsonPost = 0;
                    $this->contentType = 'Content-Type: application/x-www-form-urlencoded';
                    $this->requestType = 'GET';
                break;
                case 'getManufacturers':
                    $this->urlPostxix = '/rest/getManufacturers';
                    $this->urlPrefix = 'http://arm.ag.mos.ru';
                    $this->jsonPost = 0;
                    $this->contentType = 'Content-Type: application/x-www-form-urlencoded';
                    $this->requestType = 'GET';
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
            
            if($this->jsonPost){
                $this->postData["token"] = $this->token;
                $data = json_encode($this->postData);
            }
            else{
                $data = $this->postData;
            }
            
            $url = $this->urlPrefix.$this->urlPostxix;
            
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url);
            if($this->requestType=='POST'){
                curl_setopt ($ch, CURLOPT_POST, 1);
                curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt ($ch, CURLOPT_HTTPHEADER, array($this->contentType));
            }
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec ($ch);
            $data = @json_decode($data);
            if($data)return $data;
            $this->riseError("Error during parse JSON answer");
            return false;
        }
        
    }
