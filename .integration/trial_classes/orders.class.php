<?php

    require_once("request.class.php");

    class orders extends request{
        
        private $email = '';
        
        function __construct($email){
            $this->email = $email;
        }
        
        function history(){
            $this->setMethod("getOrders");
            $this->setData("email=".$this->email);
            $data = $this->exec();
            if(isset($data->orders))return $data->orders;
        }

    }
