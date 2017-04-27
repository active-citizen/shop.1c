<?php
/*
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
 */


    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/active-citizen-bridge.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/point.class.php");
    
    $agBrige = new ActiveCitizenBridge;
    
    $answer = array("errors"=>"");
    
    
    $args = array(
        "login"     =>  isset($_REQUEST["login"])?$_REQUEST["login"]:'',
        "password"  =>  isset($_REQUEST["password"])?$_REQUEST["password"]:'',
        "token"     =>  $EMP_TOKENS[CONTOUR],
        "session_id"=>  
            isset($_REQUEST["enc_session_id"])?$_REQUEST["enc_session_id"]:'',
    );
    
    if($args["session_id"])
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

    if(!isset($profile["session_id"]) || !trim($profile["session_id"])){
        $USER->Logout();
        $answer["errors"][] = 'Ошибка авторизации';
    }
    
    if(isset($profile['result']['personal']['phone']))
        $args["login"] = $profile['result']['personal']['phone'];

    if(isset($profile["errorMessage"]) && $profile["errorMessage"])
        $answer["errors"][] = $profile["errorMessage"];
        
    if(isset($profile["result"]))$answer["profile"] = $profile["result"];


    // Проверяем есть ли в жкрнале записи о его последних обновлениях профиля
    // Если информации об обновления профиля нет - заводим новую
    $bxUser = new bxUser;
    $login = $args["login"];
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
    

    if($updateRecord = $bxUser->getUpdateRecord($login,$email)){
        $bxUser->setLastUpdateTime($login, $email, $profile["session_id"]);
    }

    if(
        isset($profile["result"]) 
        && $profile["result"] 
        && isset($profile["session_id"]) 
        && isset($profile["session_id"])
        && !$USER->isAuthorized()
    ){
       if(!$bxUser->login(
            $args["login"],
            $profile["session_id"], 
            $profile["result"])
        ){
            $answer["errors"][] = $bxUser->error;
        }
        $answer["redirect"] = '/catalog/';
    }
    
    //=========== Стягиваем баллы =========
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
    
    echo json_encode($answer);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
    
    
    
