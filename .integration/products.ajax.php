<?php
/*
 * products.ajax.php
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
    
    $agBrige = new ActiveCitizenBridge;
    
    $answer = array(
        "errors"=>""
    );
    
    
    
    $args = array(
    );
    
    
    $agBrige->setMethod('getProducts');
    $agBrige->setMode('arm');
    $agBrige->setArguments($args);
    $answer["errors"] = $agBrige->getErrors();
    
    if(!$answer["errors"] && !$products = $agBrige->exec()){
        $answer["errors"] = array_merge($answer["errors"],$agBrige->getErrors());
    }
    echo "<pre>";
    print_r($products);
    die;
        
        
        
    
    echo json_encode($answer);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
    
    //echo "<pre>";
    //print_r($profile);
    
    
