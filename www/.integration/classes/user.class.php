<?php
/*
 * user.class.php
 * 
 * Copyright 2016 Андрей Инюцин <inutcin@yandex.ru>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CSSAG/CSSAGAccount.class.php");
    use AGShop\SSAG as SSAG;


    class bxUser{
        
        
        var $error = ''; //Текст ошибки
        
        /*
         * @param $login - логин пользователя в emp
         * @param $sessionId - ID сессии пользователя
         * @param $profile - опционально - данные профиля
         * @return true если всё ок
        */
        function login($login, $sessionId, $profile = array()){
            
            // Устанавливаем целевой логив битриксе
            $bitrixLogin = "u$login";

            // Проверяем корректност email
            $sOriginalEmail = "";
            if(
                !isset(
                    $profile["personal"]["email"]
                ) 
                || 
                !preg_match(
                    "#^[\d\w\-\.\_]+@[\d\w\-\.\_]+$#",
                    $profile["personal"]["email"]
                )
            ){
                $sOriginalEmail = $profile["personal"]["email"];
                $profile["personal"]["email"] = 
                    $bitrixLogin."@shop.ag.mos.ru";
                //$this->error = 'Профиль пользователя не содержит корректный email';
                //return false;
            }
            // ДЕлаем для битрикса случайный пароль
            $password = substr(md5(/*time()*/'newpassword'),0,16);
            $email = $profile["personal"]["email"];
            $userData = array(
                "LOGIN"             =>  $bitrixLogin,
                "PASSWORD"          =>  $password,
                "CONFIRM_PASSWORD"  =>  $password,
                "EMAIL"             =>  $email,
                "GROUP_ID"          =>  array(2,3,4,6),
                "PERSONAL_GENDER"   =>  
                    isset($profile["personal"]["sex"]) 
                        && 
                    $profile["personal"]["sex"]=='male'
                    ?
                    'M'
                    :'F',
                "NAME"              =>  
                    isset($profile["personal"]["firstname"]) 
                        && 
                    isset($profile["personal"]["middlename"])
                    ?
                    $profile["personal"]["firstname"]
                        ." ".$profile["personal"]["middlename"]
                    :
                    $profile["personal"]["firstname"],
                "LAST_NAME"         =>  
                    isset($profile["personal"]["surname"])
                    ?
                    $profile["personal"]["surname"]
                    :
                    '',
                "PERSONAL_PHONE"    =>  
                    isset($profile["personal"]["phone"])
                    ?
                    $profile["surname"]["phone"]
                    :
                    '',
                "PERSONAL_BIRTHDAY" =>  
                    isset($profile["personal"]["birthday"])
                    ?
                    $profile["surname"]["birthday"]
                    :
                    '',
                "ACTIVE"            =>  "Y",
                "PERSONAL_NOTES"    => "original email: ".$sOriginalEmail
            );

            $objUser = new CUser;
            CModule::IncludeModule("sale");
            $objAccount = new CSaleUserAccount;

            // Проверяем есть ли пользователь с таким логином в битриксе
            // Если пользователя нет - заводим
            $res = CUser::GetByLogin($bitrixLogin);
            
            // Если пользователя с таким логином нет - создаём
            if(!$arUser = $res->GetNext()){

                if(!$userId = $objUser->Add($userData)){
                    $this->error = 
                        "Ошибка добавления пользователя: ". print_r($objUser,1);
                    return false;
                }
                // Добавляем счёт
                if(!$accountId = $objAccount->Add(array(
                    "USER_ID"=>$userId,
                    "CURRENT_BUDGET"=>0,
                    "CURRENCY"=>"BAL"
                ))){
                    $this->error = "Ошибка добавления аккаунта: ". 
                        print_r($objAccount,1);
                    return false;
                }
                
                $res = $objUser->GetByID($iserId);
                $arUser = $res->GetNext();
            }
            // Если пользователь есть - обновляем информацию о нём
            else{
                if(!$objUser->Update($arUser["ID"], $userData)){
                    $this->error = "Ошибка обновления счёта: ". print_r($objUser,1);
                    return false;
                }
                
                // Если у пользователя нет счёта = создаём
                /*
                Depricated
                if(!CSaleUserAccount::GetByUserID($arUser["ID"], "BAL")){
                    // Добавляем счёт
                    if(!$accountId = $objAccount->Add(array(
                        "USER_ID"=>$arUser["ID"],
                        "CURRENT_BUDGET"=>0,
                        "CURRENCY"=>"BAL"
                    ))){
                        $this->error = "Ошибка добавления счёта: ". 
                            print_r($objAccount,1);
                        return false;
                    }
                }
                */
                
            }
            $res = CUser::GetByLogin($bitrixLogin);
            $arUser = $res->GetNext();
            
            // Проверяем есть ли в жкрнале записи о его последних обновлениях профиля
            // Если информации об обновления профиля нет - заводим новую
            if(!$updateRecord = $this->getUpdateRecord($login,$email)){
                $this->createUpdateRecord($login, $email, $sessionId);
                $updateRecord = $this->getUpdateRecord($login,$email);
            }
            else{
                $this->setLastUpdateTime($login, $email, $sessionId);
            }
            
            // Авторизуемся
            $objUser->Authorize($arUser["ID"],true);
            
            return true;
        }
        
        /*
         * Получение записи в реестре обновлений профиля пользователя
         * 
         * @param $login - логин пользователя в emp
         * @param $email - email         
         * @return массив с записью о последнем обновлении
        */
        
        function getUpdateRecord($login, $email){
            global $DB;
            $query = "
                SELECT 
                    * 
                FROM 
                    int_profile_import 
                WHERE 
                    login='$login' 
                    -- AND email='$email' 
                ORDER BY 
                    `last_update` DESC 
                LIMIT 1
            ";
            $res = $DB->Query($query);
            return $res->GetNext();
        }
        
        
        /*
         * Создание записи в реестре обновлений профиля пользователя
         * 
         * @param $login - логин пользователя в emp
         * @param $email - email         
         * @return ID вставленной записи
        */
        function createUpdateRecord($login, $email, $sessionId){
            global $DB;
            $query = "INSERT INTO `int_profile_import`(`login`,`email`,`session_id`,`last_update`)
            VALUES('$login', '$email', '$sessionId',UNIX_TIMESTAMP(NOW()))";
            $DB->Query($query);
            return  $DB->LastID();
        }
        
        /*
         * Обновление у записи в реестре обновлений профиля пользователя времени последнего обновления
         * 
         * @param $login - логин пользователя в emp
         * @param $email - email         
         * @param $timeStamp - время, которое надо установить (если пусто - вставляется текущее)
         * @return 
        */
        function setLastUpdateTime(
            $login, $email, $sSessionId, $timeStamp = ''
        ){
            global $DB;
            $query = "
                UPDATE 
                    `int_profile_import` 
                SET 
                    `last_update`="
                        .($timeStamp?$timeStamp:"UNIX_TIMESTAMP(NOW())")."
                    ,session_id='$sSessionId' 
                WHERE 
                    `login`='$login' 
                    -- AND `email`='$email' 
                LIMIT 1";
            $DB->Query($query);
            return true;
        }
        
        
        /*
         *  Получение EMP-сессии текущего залогиненного BITRIX-пользователя
         * 
         * @return ID сессии или ничего
         * 
         */
        function getEMPSessionId($login=''){
            global $DB;
            global $USER;
            if(!$login)
                $login = $USER->GetLogin();
            $login = preg_replace("#^u+(\d+)$#","$1",$login);
            
            // Смотрим последнюю сессию у этого пльзователя
            $res = $DB->Query($query = "SELECT `session_id` FROM
            `int_profile_import` WHERE `login`='".$login."' ORDER BY `id` DESC
            LIMIT 1");
            if(!$result = $res->GetNext()){return false;}
            if(!isset($result["session_id"])){return false;}
            
            return $result["session_id"];
        }
        
        /**
         * Получение пользователя по его логину(номеру телефона)
        */
        function getUserInfo(){
            global $DB;
            $sessionId = $this->getEMPSessionId();
            $res = $DB->Query($query = 
                "SELECT * FROM `int_profile_import` 
                WHERE `session_id`='".$sessionId."' LIMIT 1");
            
            return $res->GetNext();
        }
        
        /**
            Прозрачная авторизация пользователя
        */
        function authUserCross(){
            require_once($_SERVER["DOCUMENT_ROOT"].
                "/.integration/classes/active-citizen-bridge.class.php");
            require_once(
                $_SERVER["DOCUMENT_ROOT"]."/.integration/classes/point.class.php"
            );
            // Секретный файлк с токенами и ключами
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
            
            $agBrige = new ActiveCitizenBridge;
            
            $answer = array("errors"=>"");
            
            $args = array(
                "login"     =>  isset($_REQUEST["login"])?$_REQUEST["login"]:'',
                "password"  =>  isset($_REQUEST["password"])?$_REQUEST["password"]:'',
                "token"     =>  $EMP_TOKENS[CONTOUR],
                "session_id"=>  
                    isset($_REQUEST["enc_session_id"])
                    ?
                    $_REQUEST["enc_session_id"]
                    :
                    $_REQUEST["session_id"],
            );
            $login = $args["login"];
             
            // Получаем от EMP сессию и профиль текущего залогиненного на АГ
            // пользователя
            if($args["session_id"] && $_REQUEST["enc_session_id"])
                $agBrige->setMethod('enc_auth');
            else
                $agBrige->setMethod('auth');
            
            $agBrige->setMode('emp');
            $agBrige->setArguments($args);
            $answer["errors"] = $agBrige->getErrors();
            if(!is_array($answer["errors"]))$answer["errors"] = array();
            $profile = array();
            if(!$answer["errors"])
                $profile = $agBrige->exec();

            $email = $profile["result"]["personal"]["email"];
            // Проверяем корректност email
            $sOriginalEmail = "";
            if(
                !isset(
                    $profile["result"]["personal"]["email"]
                ) 
                || 
                !preg_match(
                    "#^[\d\w\-\.\_]+@[\d\w\-\.\_]+$#",
                    $profile["result"]["personal"]["email"]
                )
            )$email = "u".$login."@shop.ag.mos.ru";

            if(isset($profile['result']['personal']['phone']))
                $args["login"] = $profile['result']['personal']['phone'];

            if(isset($profile["errorMessage"]) && $profile["errorMessage"])
                $answer["errors"][] = $profile["errorMessage"];
                
            if(isset($profile["result"]))$answer["profile"] = $profile["result"];

            // Заглушка
            $sLockPhone = $answer["profile"]["personal"]["phone"];
           
            /*
            Заглушка
            if(
                $sLockPhone=='79171189696'
                || $sLockPhone == '79269189938'
                || $sLockPhone == '79262068246'
                || $sLockPhone == '79772612862'
                || $sLockPhone == '79036619266'
                || $sLockPhone == '79151086593'
                || $sLockPhone == '79178146980'
                || $sLockPhone == '79209672105'
                || $sLockPhone == '79063443775'
                || $sLockPhone == '79063443775'
                || $sLockPhone == '79377979855'
            ){
            }
            else{
                return false;
            }
            */
            


            // Если пользователь разлогинился на АГ, но залогинен в битриксомом магазина 
            // - разлогиниваем и в битриксе и редиректимся
            if(!is_object($USER))$USER = new CUser;
            if(
                $USER->isAuthorized() 
                && (
                    !isset($profile["session_id"]) 
                    || 
                    !trim($profile["session_id"])
                )
            ){
                $USER->Logout();
                $answer["errors"] = array();
        //        $answer["redirect"] =
        //        trim($_REQUEST["backurl"])?$_REQUEST['backurl']:"/catalog/";
                return $answer;
            }
          
            // Если в полученном профиле сессия пользователя отличается от текущей
            // создаём новую сессию, перелогиниваемся, получаем баллы и редиректимся

            $sCurrentSessionId =
                $this->getEMPSessionId(
                );

            if(
                isset($profile["result"]) 
                && $profile["result"] 
                && preg_match("#^[0-9a-f]{32,40}$#i",$profile['session_id']) 
                && $sCurrentSessionId!=$profile["session_id"]
            ){
                $args["login"] = $profile["result"]["personal"]["phone"];

                if(!$this->login(
                    $args["login"],
                    $profile["session_id"], 
                    $profile["result"])
                ){
                    $answer["errors"][] = $this->error;
                }
                else{
                    /*
                    require_once("classes/point.class.php");
                    $objPoints = new bxPoint;
                    $answer["points"] = $objPoints->fetchAccountFromAPI();
                    */
                    //=========== Стягиваем баллы =========
                    /*
                    $args = array(
                        "session_id"    =>  $profile["session_id"],
                        "token"         =>  $EMP_TOKENS[CONTOUR]
                    );
                    $agBrige->setMethod('pointsHistory');
                    $agBrige->setMode('emp');
                    $agBrige->setArguments($args);
                    $answer["errors"] = array_merge($agBrige->getErrors(),$answer["errors"]);
                    $profile = array();
                    if(!$answer["errors"] && !$history = $agBrige->exec()){
                        $answer["errors"] = array_merge($answer["errors"],$agBrige->getErrors());
                    }
                    
                    if(isset($history["errorMessage"]) && $history["errorMessage"]){
                        $answer["errors"][] = $history["errorMessage"];
                    }else{
                        $bxPoint = new bxPoint;
                        $bxPoint->updatePoints($history["result"], CUser::GetID());
                    }
        //            $answer["redirect"] =
        //            trim($_REQUEST["backurl"])?$_REQUEST['backurl']:"/catalog/";
                    */
                    return $answer;
                }
            }
            $objSSAGAccount = new \SSAG\CSSAGAccount('',$USER->GetID());
            $objSSAGAccount->update();
           
            if(CUser::isAuthorized()){
            }
            
            return $answer;        
        }
        
        
    }
