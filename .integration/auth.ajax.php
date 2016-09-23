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
    require_once("classes/active-citizen-bridge.class.php");
    require_once("classes/user.class.php");
    
    $agBrige = new ActiveCitizenBridge;
    
    $answer = array("errors"=>"");
    
    
    $args = array(
        "login"     =>  isset($_REQUEST["login"])?$_REQUEST["login"]:'',
        "password"  =>  isset($_REQUEST["password"])?$_REQUEST["password"]:'',
        "token"     =>  "ag_uat_token3",
        "session_id"=>  isset($_REQUEST["enc_session_id"])?$_REQUEST["enc_session_id"]:'',
    );
    
    if($args["session_id"])
        $agBrige->setMethod('enc_auth');
    else
        $agBrige->setMethod('auth');
    
    $agBrige->setMode('emp');
    $agBrige->setArguments($args);
    $answer["errors"] = $agBrige->getErrors();
    $profile = array();
    if(!$answer["errors"])
        $profile = $agBrige->exec();

    if(isset($profile['result']['personal']['phone']))
        $args["login"] = $profile['result']['personal']['phone'];

    if(isset($profile["errorMessage"]) && $profile["errorMessage"])
        $answer["errors"][] = $profile["errorMessage"];
        
    if(isset($profile["result"]))$answer["profile"] = $profile["result"];
    
    if(isset($profile["result"]) && $profile["result"] && isset($profile["session_id"]) && isset($profile["session_id"])){
        $bxUser = new bxUser;
        if(!$bxUser->login($args["login"],$profile["session_id"], $profile["result"])){
            $answer["errors"][] = $bxUser->error;
        }
    }
    
    echo json_encode($answer);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
    
    //echo "<pre>";
    //print_r($profile);
    
    
