<?php
/*
 * orders.ajax.php
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


    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php"
    );
    require_once("classes/active-citizen-bridge.class.php");
    require_once("classes/user.class.php");
    require_once("classes/order.class.php");
    
    $agBrige = new ActiveCitizenBridge;
    $bxUser =  new bxUser;
    
    $answer = array(
        "errors"=>""
    );
    
    $userInfo = $bxUser->getUserInfo();

    $args = array(
        "phone"     =>  $userInfo["login"]
    );
    $agBrige->setMethod('getOrders');
    $agBrige->setMode('arm');
    $agBrige->setArguments($args);
    
    if(0 && !$answer["errors"] && !$orders = $agBrige->exec()){
        $answer["errors"] = array_merge($answer["errors"],$agBrige->getErrors());
        echo json_encode($answer);
        die;
    }
    
    //file_put_contents("orders.txt",json_encode($orders["orders"]));

    $orders = $agBrige->objectToArray(json_decode(file_get_contents("orders.txt")));
    echo "<pre>";
    print_r($orders);
    die;
    
    if(isset($orders["errorMessage"]) && $orders["errorMessage"])
        $answer["errors"][] = $history["errorMessage"];

    $bxOrder = new bxOrder;
    $bxOrder->updateOrders($orders["orders"], CUser::GetID());
    
    
    
    echo json_encode($answer);
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/epilog_after.php"
    );
    
    //echo "<pre>";
    //print_r($profile);
    
    
