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
    $dd = opendir($uploadDir);
    $importFilename = '';
    while($filename = readdir($dd))
        if(preg_match("#^import.*\.xml$#",$filename))
            {$importFilename = $filename;break;}
    
    
    echo "|$importFilename|";

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
