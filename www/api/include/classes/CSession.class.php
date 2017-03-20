<?php
    require_once(realpath(dirname(__FILE__)."/../common.php"));

    require_once(realpath(dirname(__FILE__)."/wirix/db.class.php"));
    require_once(realpath(dirname(__FILE__)."/CUser.class.php"));

    /**
     * Класс работы с сессиями
     */
    class CSession{

        var $verbose = true;

        function __construct(){
        }
    
        /**
         * Получение информации о сессии
        */
        function get(
            $sessionId
        ){
            $CUser = new CUser;
            
            $sSessionSuffix = self::getSuffix($sessionId);
            // Если сессия есть
            if($GLOBALS["DB"]->search_one(
                "sessions_$sSessionSuffix",
                array("session_id"=>$sessionId),"","*"
            )){
                return $GLOBALS["DB"]->record;
            }
            // Если сессии нет, но по её ID удаётся получить у ЕМП профиль пользователя
            elseif($objEMPProfile = $CUser->getEMPProfile($sessionId)){
                // Если такой пользователь уже есть в БД - получаем его ID
                if($arUser = $CUser->getBySSOID($objEMPProfile->result->ssoId)){
                    $nUserId = $arUser['id'];
                }
                // Если такого пользователя в БД нет - создаём и получаем его ID
                elseif(!$nUserId = $CUser->Add(
                    array(
                        "phone" =>$objEMPProfile->result->msisdn,
                        "sso_id"=>$objEMPProfile->result->ssoId
                    ),
                    @$objEMPProfile->result
                )){
                    
                }
                
                $this->add($sessionId, $nUserId);
                $GLOBALS["DB"]->record = array();
                $GLOBALS["DB"]->search_one(
                    "sessions_$sSessionSuffix",
                    array("session_id"=>$sessionId),"","*"
                );
                return $GLOBALS["DB"]->record;
            }
            return false;
        }
        
        /**
         * Связывание пользователя с сессией
        */
        function add(
            $sessionId,
            $sUserId
        ){
            $sSessionSuffix = self::getSuffix($sessionId);
            return $GLOBALS["DB"]->insert(
                "sessions_$sSessionSuffix",
                array(
                    "user_id"=>$sUserId,
                    "session_id"=>$sessionId,
                    "ctime"=>date("Y-m-d H:i:s")
                )
            );
        }

        static function getSuffix($sessionId){
            $sessionSuffix = substr($sessionId,strlen($sessionId)-2,2);
            return strtolower($sessionSuffix);
        }

    }
