<?php
/*
 * points.ajax.php
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


    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    require_once("classes/active-citizen-bridge.class.php");
    require_once("classes/user.class.php");
    require_once("classes/point.class.php");
    
    $agBrige = new ActiveCitizenBridge;
    
    $answer = array(
        "errors"=>""
    );
    
    
    $bxUser = new bxUser;
    $session_id = $bxUser->getEMPSessionId();
    
    $args = array(
        "session_id"     =>  $session_id,
        "token"     =>  "ag_uat_token3"
    );
    $agBrige->setMethod('pointsHistory');
    $agBrige->setMode('emp');
    $agBrige->setArguments($args);
    $answer["errors"] = $agBrige->getErrors();
    $profile = array();
    if(!$answer["errors"] && !$history = $agBrige->exec()){
        $answer["errors"] = array_merge($answer["errors"],$agBrige->getErrors());
    }
        
        
        
    if(isset($history["errorMessage"]) && $history["errorMessage"])
        $answer["errors"][] = $history["errorMessage"];
        
    $bxPoint = new bxPoint;
    $bxPoint->updatePoints($history["result"]['history'], CUser::GetID());
    
    
    
    echo json_encode($answer);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
    
    //echo "<pre>";
    //print_r($profile);
    
    