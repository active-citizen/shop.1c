<?php
    require_once(realpath(dirname(__FILE__)."/../common.php"));
    require_once(realpath(dirname(__FILE__)."/wirix/db.class.php"));
    require_once(realpath(dirname(__FILE__)."/CSession.class.php"));

    /**
     * Класс работы с пользователями
     */
    class CUser{

        var $verbose = true;

        function __construct(){
        }
    
        /**
         * Получение EMP-профиля пользователя по сессии
        */
        function getEMPProfile(
            $sessionId
        ){
            require_once("curl.class.php");
            $sUrl = "http://api.emp.msk.ru:8090/json/v1.0/citizens/profile/get?token=".
                $GLOBALS["CONF"]["emp_token"]."&session_id=".$sessionId;
           
            $objCurl = new curlTool;
            $objAnswer = json_decode($objCurl->get($sUrl));
            $arAnswer = json_decode(json_encode((array)$objAnswer), TRUE);
            if(
                isset($arAnswer["result"]["ssoId"])
                &&
                $arAnswer["result"]["ssoId"]
            ){
                $this->addSSOIDSessionLog(
                    $sessionId,
                    $arAnswer["result"]["msisdn"],
                    $arAnswer["result"]["ssoId"]
                );
            }

            return $objAnswer;
        }

        /**
            Добавление лога соответствий ssoid и session_id
        */
        function addSSOIDSessionLog($sSessionId, $sPhone, $sSSOID){
            return $GLOBALS["DB"]->insert("ssoid_log",[
                "ctime"         =>  date("Y-m-d H:i:s"),
                "session_id"    =>  $sSessionId,
                "phone"         =>  $sPhone,
                "sso_id"        =>  $sSSOID
            ]);
        }

        /**
         * Получение подльзователя по SSO_ID
        */
        function getBySSOID($sSSOID){
            $GLOBALS["DB"]->search_one("users",array("sso_id"=>$sSSOID));
            return $GLOBALS["DB"]->record;
        }

        /**
         * Получение подльзователя по SSO_ID
        */
        function getByPhone($sPhone){
            $GLOBALS["DB"]->search_one("users",array("phone"=>$sPhone));
            return $GLOBALS["DB"]->record;
        }

        /**
         * Получение подльзователя по ID
        */
        function getById($nId){
            $GLOBALS["DB"]->search_one("users",array("id"=>$nId));
            return $GLOBALS["DB"]->record;
        }
        
        /**
         * Получение подльзователя по ID сессии
        */
        function getBySessionId($nId){
            $CSession = new CSession;
            $arSession = $CSession->get($sSessionId);

            if(!$arSession)return false;
            $nUserId = $arSession["user_id"];
            $arUser = $this->getById($nUserId);
            return $arUser;
        }

        /**
        
        Добавление пользователя
        
            @param $arUser = array(), keys phone, sso_id, profileJSON
        
        */
        function add($arUser, $objProfile){
            $arUser["ctime"]=date("Y-m-d H:i:s", time());
            $nUserId = $GLOBALS["DB"]->insert("users", $arUser);
            $GLOBALS["DB"]->insert("profiles", array(
                "json_data" =>  json_encode($objProfile),
                "ctime"     =>  $arUser["ctime"]
            ));
            return $nUserId;
        }
        
        function UpdateSSOId($sPhone, $sSSOId){
            $GLOBALS["DB"]->update("users", [
                "sso_id"=>  $GLOBALS["DB"]->escape($sSSOId),
            ],[
                "phone" =>  $GLOBALS["DB"]->escape($sPhone)
            ]);
        }

        function updateBalance($nUserId,$field = "balance",$value=""){

            if($field=="balance")
                $fBalance = $this->getBalance($nUserId);
            else
                $fBalance = $value;

            $GLOBALS["DB"]->update("users",array(
                $field=>$fBalance
            ),array(
                "id"=>$nUserId
            ));
        }


        static function getUserPoints($nUserId){
            $GLOBALS["DB"]->search_one(
                "users",
                array("id"=>$nUserId),
                "",
                "`current_points`,`all_points`,`freezed_points`,`spent_points`,`ag_status`"
            );
            return $GLOBALS["DB"]->record;
        }

        function getBalance($nUserId){
            $GLOBALS["DB"]->search_one(
                "transacts_brief_".self::getSuffix($nUserId),
                array("user_id"=>$nUserId),
                "",
                "SUM(`quantity`*`debit`*`accepted`)  as `balance`",
                "user_id"
            );
            if(isset($GLOBALS["DB"]->record["balance"]))
                return $GLOBALS["DB"]->record["balance"];
            return false;
        }
        
        static function getSuffix($nUserId){
            return sprintf("%02d",$nUserId % 100);
        }

    }
