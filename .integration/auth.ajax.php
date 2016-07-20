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


    require_once("classes/active-citizen-bridge.class.php");
    
    $agBrige = new ActiveCitizenBridge;
    
    $answer = array(
        "errors"=>""
    );
    
    
    $args = array(
        "login"     =>  isset($_REQUEST["login"])?$_REQUEST["login"]:'',
        "password"  =>  isset($_REQUEST["password"])?$_REQUEST["password"]:'',
        "token"     =>  "ag_uat_token3"
    );
    
    $agBrige->setMethod('auth');
    $agBrige->setMode('emp');
    $agBrige->setArguments($args);
    $answer["errors"] = $agBrige->getErrors();
    if(!$answer["errors"])
        $profile = $agBrige->exec();
        
    if(isset($profile["errorMessage"]) && $profile["errorMessage"])
        $answer["errors"][] = $profile["errorMessage"];
    
    
    
    //echo json_encode($answer);
    
    echo "<pre>";
    print_r($profile);
    
    
