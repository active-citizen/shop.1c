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
            if(!isset($profile["personal"]["email"]) || !preg_match("#^[\d\w\-\.\_]+@[\d\w\-\.\_]+$#",$profile["personal"]["email"])){
                $this->error = 'Профиль пользователя не содержит корректный email';
                return false;
            }
            
            // ДЕлаем для битрикса случайный пароль
            $password = substr(md5(time()),0,16);
            $email = $profile["personal"]["email"];
            $userData = array(
                "LOGIN"             =>  $bitrixLogin,
                "PASSWORD"          =>  $password,
                "CONFIRM_PASSWORD"  =>  $password,
                "EMAIL"             =>  $email,
                "GROUP_ID"          =>  array(2,3,4,6),
                "PERSONAL_GENDER"   =>  isset($profile["personal"]["sex"]) && $profile["personal"]["sex"]=='male'?'M':'F',
                "NAME"              =>  isset($profile["personal"]["firstname"]) && isset($profile["personal"]["middlename"])?$profile["personal"]["firstname"]." ".$profile["personal"]["middlename"]:'',
                "LAST_NAME"         =>  isset($profile["personal"]["surname"])?$profile["personal"]["surname"]:'',
                "PERSONAL_PHONE"    =>  isset($profile["personal"]["phone"])?$profile["surname"]["phone"]:'',
                "PERSONAL_BIRTHDAY" =>  isset($profile["personal"]["birthday"])?$profile["surname"]["birthday"]:'',
                "ACTIVE"            =>  "Y"
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
                    $this->error = "Ошибка добавления пользователя: ". print_r($objUser,1);
                    return false;
                }
                // Добавляем счёт
                if(!$accountId = $objAccount->Add(array(
                    "USER_ID"=>$userId,
                    "CURRENT_BUDGET"=>0,
                    "CURRENCY"=>"BAL"
                ))){
                    $this->error = "Ошибка добавления аккаунта: ". print_r($objAccount,1);
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
                if(!CSaleUserAccount::GetByUserID($arUser["ID"], "BAL")){
                    // Добавляем счёт
                    if(!$accountId = $objAccount->Add(array(
                        "USER_ID"=>$arUser["ID"],
                        "CURRENT_BUDGET"=>0,
                        "CURRENCY"=>"BAL"
                    ))){
                        $this->error = "Ошибка добавления счёта: ". print_r($objAccount,1);
                        return false;
                    }
                }
                
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
                $this->setLastUpdateTime($login, $email);
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
        
        private function getUpdateRecord($login, $email){
            global $DB;
            $query = "SELECT * FROM int_profile_import WHERE login='$login' AND email='$email' ORDER BY last_update DESC LIMIT 1";
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
        private function createUpdateRecord($login, $email, $sessionId){
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
        private function setLastUpdateTime($login, $email, $timeStamp = ''){
            global $DB;
            $query = "UPDATE `int_profile_import` SET `last_update`=".($timeStamp?$timeStamp:"UNIX_TIMESTAMP(NOW())")."
            WHERE `login`='$login' AND `email`='$email' LIMIT 1";
            $DB->Query($query);
            return true;
        }
        
        
        /*
         *  Получение EMP-сессии текущего залогиненного BITRIX-пользователя
         * 
         * @return ID сессии или ничего
         * 
         */
        function getEMPSessionId(){
            global $DB;
            global $USER;
            $login = $USER->GetLogin();
            $login = preg_replace("#^u(\d+)$#","$1",$login);
            
            // Смотрим последнюю сессию у этого пльзователя
            $res = $DB->Query($query = "SELECT `session_id` FROM `int_profile_import` WHERE `login`='".$login."' ORDER BY `id` DESC");
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
            
        
        
        
    }
