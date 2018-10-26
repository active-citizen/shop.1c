<?php
    namespace SSAG;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CCurl/CCurlSimple.class.php");
    require_once(realpath(__DIR__."/..")."/CLog/CSSAGLog.class.php");

    use AGShop as AGShop;
    use AGShop\SSAG as SSAG;
    use AGShop\Curl as Curl;
    use AGShop\Log as Log; 

    /**
        Класс для работы с СС АГ
    */
    class CSSAG extends \AGShop\CAGShop{

        var $arProfile = [];

        var $nAGID = 0;
        var $sHash = '';
        var $nBitrixUserId = 0;
        var $sDomain = '';
        var $sPort = '';
       
        private $sSecret = '';
        private $sProfileMethod = '/mvag/user/getProfile';

        function __construct($sSessionId = '', $nUserId = 0){
            parent::__construct();
            // Получаем настройки в зависимости от контура 
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
            $this->sSecret = $AG_SECRETS[CONTOUR]["secret"];
            $this->sDomain = $AG_SECRETS[CONTOUR]["local_url"];
            $this->sPort = $AG_SECRETS[CONTOUR]["local_port"];

            // Получаем AG ID из пользователя битрикса
            $this->nAGID = $this->__getAGIDFromBitrixUser($nUserId);
            $sSessionId = trim($sSessionId);
            if(
                !$this->nAGID 
                && $sSessionId
                && $this->nAGID = $this->__getAGIDFromAGProfile($sSessionId)
            ){
                $this->__setAGIDFromBitrixUser($this->nAGID, $nUserId);
            }
            elseif(
                !$this->sHash 
                && $sSessionId
                && $this->nAGID = $this->__getAGIDFromAGProfile($sSessionId)
            ){
                $this->__setAGHASHFromBitrixUser($this->sHash, $nUserId);
            }
            
            return true;
        }

        function updateProfile($sSessionId,$nUserId){

            global $USER;

            $arAnswer = $this->getProfile($sSessionId);
            include($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/settings.inc.php");
            $arUrl = parse_url($sCrossDomainAuthURL);
            $sBaseUrl = $arUrl["scheme"]."://".$arUrl['host'];
            $arFile = [];
            if(isset($arAnswer['result']['avatar'])){
                $arAnswer['result']['avatar'] = 
                    $this->sDomain.":".$this->sPort
                    .$arAnswer['result']['avatar'];
                $arFile = \CFile::MakeFileArray($arAnswer['result']['avatar']);
                if(isset($arFile["size"]) && intval($arFile["size"])>0)
                    $arFile["del"] = 'Y';
                else
                    $arFile = [];
            }
            $arFields = [];
            if($arFile)$arFields["PERSONAL_PHOTO"] = $arFile;

            if($arAnswer['result']['email'])$arFields["EMAIL"] =
                $arAnswer['result']['email'];
            if($arAnswer['result']['firstname'])$arFields["NAME"] =
                $arAnswer['result']['firstname'];
            if($arAnswer['result']['surname'])$arFields["LAST_NAME"] =
                $arAnswer['result']['surname'];
            if($arAnswer['result']['middlename'])$arFields["SECOND_NAME"] =
                $arAnswer['result']['middlename'];
            if($arAnswer['result']['ag_id'])$arFields["UF_USER_AGID"] =
                $arAnswer['result']['ag_id'];

            $USER->Update($nUserId, $arFields);
        }

        function getProfile($sSessionId){
            $arSign = $this->getSignature($sSessionId);
            $sUrl = $this->sDomain.":".$this->sPort.$this->sProfileMethod;
            $sRequest = json_encode([
                "session_id"=>  $sSessionId,
                "nonce"     =>  $arSign["nonce"],
                "signature" =>  $arSign["signature"]
            ]);
            $objCurl = new \Curl\CCurlSimple;
            $sResult = $objCurl->post($sUrl, $sRequest);
            \Log\CSSAGLog::addLog($sUrl, $sRequest, $sResult);           
           
            if(!$arAnswer = $this->checkAnswer($sResult))return false;
            
            return $arAnswer;
        }

        
        private function __getAGIDFromBitrixUser($nUserId = 0){
            if(!$nUserId)$nUserId = \CUser::GetID();
            $this->nBitrixUserId = $nUserId;
            $arUser = \CUser::GetList(
                ($by="personal_country"), ($order="desc"),
                ["ID"=>$nUserId],
                [
                    "SELECT"=>["UF_USER_AGID","UF_USER_HASH"],
                    "NAV_PARAMS"=>["nTopCount"=>1]
                ]
            )->Fetch();
            if(isset($arUser["UF_USER_HASH"]))
                $this->sHash = $arUser["UF_USER_HASH"];
            if(isset($arUser["UF_USER_AGID"]))return $arUser["UF_USER_AGID"];
            return false;
        }
        
        private function __setAGIDFromBitrixUser($nAgId, $nUserId = 0){
            if(!$nUserId)$nUserId = \CUser::GetID();
            $objUser = new \CUser;
            $objUser->Update($nUserId, ["UF_USER_AGID"=>$nAgId]);
        }

        private function __setAGHASHFromBitrixUser($sHash, $nUserId = 0){
            if(!$nUserId)$nUserId = \CUser::GetID();
            $objUser = new \CUser;
            $objUser->Update($nUserId, ["UF_USER_HASH"=>$sHash]);
        }

        /**
            Получение профиля по ID сессии
            
            @param $sSessionId - ID сессии
        */
        private function __getAGIDFromAGProfile($sSessionId){
            
            $arAnswer = $this->getProfile($sSessionId);
            if(!$arAnswer = $this->checkAnswer($sResult))return false;
            if(
                !isset($arAnswer["result"]["ag_id"])
                ||
                !intval($arAnswer["result"]["ag_id"])
            )return $this->addError("Не указан ag_id в ответе СС АГ");
            $this->sHash = $arAnswer["result"]["hash"];
            
            return $arAnswer["result"]["ag_id"];
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
        
        
        /**
            Проверка ответа от СС АГ
            
            @param
            return ответ от СС АГ в виде массива
             
        */
        function checkAnswer($sAnswer){
            $objAnswer = json_decode($sAnswer);
            if(!$objAnswer)return $this->addError(
                'Ошибка парсинга JSON ответа'
            );
            $arAnswer = json_decode(json_encode((array)$objAnswer), TRUE);        

            if(!isset($arAnswer["errorCode"]))return $this->addError(
                "Не указан код ошибки в ответе"
            );
            
            if(
                isset($arAnswer["errorCode"])
                &&
                $arAnswer["errorCode"]!=0
            )return $this->addError("Ошибка [".$arAnswer["errorCode"]
                ."] ".$arAnswer["errorMessage"]);
            
            return $arAnswer;
        }
        
        
        /**
            Получение подписи запроса
            
            @param $sSignString - подписываемая строка
            
            returns [                
                "signature"=>"Додпись",
                "nonce"=>"Соль"
            ]
        */
        function getSignature($sSignString){
            $sNonce = $this->__nonce();
            $sSign = base64_encode(hash_hmac(
                'sha256', 
                $this->sSecret.'&'.$sSignString.'&'.$sNonce, 
                $this->sSecret, 
                true
            ));
            return [
                "signature"=>$sSign,
                "nonce"=>$sNonce
            ];
        }
    }
   
