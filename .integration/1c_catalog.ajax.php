<?php
/*
 * 1c_catalog.ajax.php
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
    
    $uploadDir = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/";
    // Получаем имя файла каталога
    $dd = opendir($uploadDir);
    $importFilename = '';
    while($filename = readdir($dd))
        if(preg_match("#^import.*\.xml$#",$filename))
            {$importFilename = $filename;break;}
    closedir($dd);
    
    // Получаем имя файла товарных предложений
    $dd = opendir($uploadDir);
    $offersFilename = '';
    while($filename = readdir($dd))
        if(preg_match("#^offers.*\.xml$#",$filename))
            {$offersFilename = $filename;break;}
    closedir($dd);


    if($offersFilename){
        $xmlOffers = file_get_contents($uploadDir.$offersFilename);
        $obOffers = simplexml_load_string(
            $xmlOffers, "SimpleXMLElement" 
        );
        
        $arOffers = json_decode(json_encode((array)$obOffers), TRUE);        
        
        $arStorages = $arOffers["ПакетПредложений"]["Склады"]["Склад"];
        $arOffers = $arOffers["ПакетПредложений"]["Предложения"]["Предложение"];
        
        $arStoragesIndex = array();
        foreach($arStorages as $arStorage){

            $arStoragesIndex[$arStorage["Ид"]] = 0;

            $arFields = array();
            $arFields["TITLE"] = $arStorage["НаименованиеПолное"];
            $arFields["ACTIVE"] = 'Y';
            $arFields["ADDRESS"] = '';
            $arFields["DESCRIPTION"] = '';
            $arFields["GPS_N"] = '';
            $arFields["GPS_S"] = '';
            $arFields["IMAGE_ID"] = '';
            $arFields["PHONE"] = '';
            $arFields["SCHEDULE"] = '';
            $arFields["XML_ID"] = $arStorage["Ид"];
            
            $arFields["USER_ID"] = '';
            $arFields["EMAIL"] = '';
            $arFields["ISSUING_CENTER"] = '';
            $arFields["SHIPPING_CENTER"] = '';
            $arFields["SITE_ID"] = 's1';
            

            if(
                isset($arStorage["КакПроехать"]) 
                && $arStorage["КакПроехать"]
                && !is_array($arStorage["КакПроехать"])
            )$arFields["ADDRESS"] .= $arStorage["КакПроехать"];
                
            if(
                isset($arStorage["ДополнительнаяИнформация"]) 
                && $arStorage["ДополнительнаяИнформация"]
                && !is_array($arStorage["ДополнительнаяИнформация"])
            )$arFields["DESCRIPTION"] .= $arStorage["ДополнительнаяИнформация"];

            if(
                isset($arStorage["ГрафикРаботы"]) 
                && $arStorage["ГрафикРаботы"]
                && !is_array($arStorage["ГрафикРаботы"])
            )$arFields["SCHEDULE"] .= $arStorage["ГрафикРаботы"];

            CModule::IncludeModule("catalog");
            $res = CCatalogStore::GetList(array(),array(
                "XML_ID"=>$arStorage["Ид"]
            ),false,array("nTopCount"=>1));

            if(!$existsStorage = $res->GetNext()){
                $arStoragesIndex[$arStorage["Ид"]] = CCatalogStore::Add($arFields);
            }
            else{
                $arStoragesIndex[$arStorage["Ид"]] = $existsStorage["ID"];
                CCatalogStore::Update($existsStorage["ID"], $arFields);
            }
        }
        echo "<pre>";
        print_r($arStoragesIndex);
        die;
    }

    if($importFilename){
        
        $xmlImport = file_get_contents($uploadDir.$importFilename);
        $obImport = simplexml_load_string(
            $xmlImport, "SimpleXMLElement" 
        );
        
        $arImport = json_decode(json_encode((array)$obImport), TRUE);        

        $arGroups = $arImport["Классификатор"]["Группы"];
        $arManufacturers = $arImport["Классификатор"]["Производители"]["Производитель"];
        $arProducts = $arImport["Каталог"]["Товары"]["Товар"];
        
        echo "<pre>";
        print_r($arManufacturers);
        echo "</pre>";
    }
    
    
    echo json_encode($answer);
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/epilog_after.php"
    );
