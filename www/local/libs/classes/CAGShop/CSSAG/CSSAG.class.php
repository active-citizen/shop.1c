<?php
    namespace SSAG;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");

    use AGShop as AGShop;
    use AGShop\SSAG as SSAG;

    /**
        Класс для работы с СС АГ
    */
    class CSSAG extends \AGShop\CAGShop{

        var $arProfile = [];
       
        private $sSecret = '';
        private $sHTTPMethod = "GET";
        private $sMethod = '/mvag/billing/getHistory';
        private $arParams  = [];
        private $nAGID = 0;

        function __construct($sSessionId = '', $nUserId = 0){
            parent::__construct();
            // Получаем настройки в зависимости от контура 
фывфывфывф

            // Получаем AG ID из пользователя битрикса
            $this->nAGID = $this->__getAGIDFromBitrixUser($nUserId);
            if(!$this->nAGID && $sSessionId = trim($sSessionId))
                $this->nAGID = $this->__getAGIDFromAGProfile($sSessionId);
        }

        

        function request(){
            print_r($this);
            die;
        }

        function addLog(){
            echo "111";
            die;
        }

        function setHTTPMethod($sMethodName){
            $this->sHTTPMethod = $sMethodName;
        }

        function setMethod($sMethod){
            $this->sMethod = $sMethod;
        }

        function setParam($sName, $sValue){
            $this->arParams[$sName] = $sValue;
        }

        private function __getAGIDFromBitrixUser($nUserId = 0){
            if(!$nUserId)$nUserId = \CUser::GetID();
            $arUser = \CUser::GetList(
                ($by="personal_country"), ($order="desc"),
                ["ID"=>$nUserId],
                [
                    "SELECT"=>["UF_USER_AGID"],
                    "NAV_PARAMS"=>["nTopCount"=>1]
                ]
            )->Fetch();
            if(isset($arUser["UF_USER_AGID"]))return $arUser["UF_USER_AGID"];
            return false;
        }

        private function __getAGIDFromAGProfile($sSessionId){
            $sNonce = $this->__nonce();
            $sRequest = json_encode([
                "session_id"=>  $sSessionId,
                "nonce"     =>  $sNonce,
                "signature" =>  $sSign
            ]);
            echo "$sRequest";
            die;
        }

        private function __nonce(
            $nLength = 20,
            $arAlphabet = [
                "0","1","2","3","4","5","6","7","8","9"
                ,"a","b","c","d","e","f","g","h","i","j","k","l","m"
                ,"n","o","p","q","r","s","t","u","v","w","x","y","z"
            ]
        ){
            $sResult = '';
            for($i=0;$i<$nLength;$i++)
                $sResult .= $arAlphabet[rand(0,count($arAlphabet)-1)];
            return $sResult;
        }
    }
   
