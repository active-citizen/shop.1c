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

    CModule::IncludeModule("catalog");
    CModule::IncludeModule("iblock");

    ///////////////////////////////////////////////////////////////////////////
    ///                     Импортируем склады
    ///////////////////////////////////////////////////////////////////////////
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
    }

    if($importFilename){
        
        $xmlImport = file_get_contents($uploadDir.$importFilename);
        $obImport = simplexml_load_string(
            $xmlImport, "SimpleXMLElement" 
        );
        
        $arImport = json_decode(json_encode((array)$obImport), TRUE);        

        $arGroups = $arImport["Классификатор"]["Группы"]["Группа"];
        $arManufacturers = 
            $arImport["Классификатор"]["Производители"]["Производитель"];
        $arProducts = $arImport["Каталог"]["Товары"]["Товар"];
        
        ///////////////////////////////////////////////////////////////////////
        ///                  Импортируем производителей
        ///////////////////////////////////////////////////////////////////////
        $manufacturersIndex = array();
        foreach($arManufacturers as $arManufacturer){
            $manufacturersIndex[$arManufacturer["Ид"]] = $arManufacturer;
        }
        
        ///////////////////////////////////////////////////////////////////////
        ///                     Импортируем разделы
        ///////////////////////////////////////////////////////////////////////
        $sectionsIndex = array();
        foreach($arGroups as $arGroup){
            $arFields = array();
            $arFields["NAME"]       = $arGroup["Наименование"];
            $arFields["ACTIVE"]     = "Y";
            $arFields["SORT"]       = $arGroup["Сортировка"];
            $arFields["IBLOCK_ID"]  = 2;
            $arFields["XML_ID"]     = $arGroup["Ид"];
            
            $arFields["CODE"]       = CUtil::translit(
                $arGroup["Наименование"], 
                "ru", 
                array("replace_space"=>"-", "replace_other"=>"-")
            );

            $res = CIBlockSection::GetList(array(),array("XML_ID"=>$arGroup["Ид"]));

            $objIBlockSection = new CIBlockSection;
            if(!$existsSection = $res->GetNext()){
                $sectionId = $objIBlockSection->Add($arFields);
                $sectionsIndex[$arGroup["Ид"]] = $sectionId;
            }
            else{
                $sectionsIndex[$arGroup["Ид"]] = $existsSection["ID"];
                $objIBlockSection->Update($existsSection["ID"], $arFields);
            }
        }
        

        ///////////////////////////////////////////////////////////////////////
        ///                     Импортируем товары
        ///////////////////////////////////////////////////////////////////////
        $productsIndex = array();
        /*
        echo "<pre>";
        print_r($arProducts);
        die;
        */

        // Составляем справочник флагов
        $ENUM = array();
        $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>2));
        while($data = $res->getNext()){
            $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
            if(!isset($ENUM[$data["PROPERTY_CODE"]]))$ENUM[$data["PROPERTY_CODE"]] = array();
            $ENUM[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
        }

        foreach($arProducts as $arProduct){
            $arFields = array();
            $arFields["NAME"]           = $arProduct["Наименование"];
            $arFields["ACTIVE"]         = $arProduct["Включен"]=='Да'?"Y":"";
            $arFields["SORT"]           = $arProduct["Сортировка"];
            $arFields["IBLOCK_ID"]      = 2;
            $arFields["XML_ID"]         = $arProduct["Ид"];
            $arFields["IBLOCK_SECTION_ID"] = true;
            if(isset($sectionsIndex[$arProduct["Группы"]["Ид"]]))
                $arFields["IBLOCK_SECTION_ID"] = $sectionsIndex[$arProduct["Группы"]["Ид"]];

            $code = explode("-",$arProduct["Ид"]);
            $code = $code[0];
            $arFields["CODE"]       = CUtil::translit(
                $arProduct["Наименование"], 
                "ru", 
                array("replace_space"=>"-", "replace_other"=>"-")
            )."-".$code;

            $res = CIBlockElement::GetList(array(),array("XML_ID"=>$arProduct["Ид"]));

            $objIBlockElement = new CIBlockElement;
            if(!$existsElement = $res->GetNext()){
                if(!$elementId = $objIBlockElement->Add($arFields)){
                    echo "<pre>";
                    print_r($arFields);
                    print_r($objIBlockElement);
                    die;
                }
                
                $productsIndex[$arProduct["Ид"]] = $elementId;
            }
            else{
                $productsIndex[$arProduct["Ид"]] = $existsElement["ID"];
                $elementId = $existsElement["ID"];
                $objIBlockElement->Update($existsElement["ID"], $arFields);
            }

            $arProperties["MANUFACTURER"]   = $manufacturersIndex[$arProduct["Производитель"]["Ид"]]["ПолноеНаименование"];
            $arProperties["QUANT"]          = $arProduct["БазоваяЕдиница"]["@attributes"]["НаименованиеПолное"];
            
            foreach($arProperties as $propertyCode=>$propertyValue){
                CIBlockElement::SetPropertyValueCode(
                    $elementId,
                    $propertyCode,
                    $propertyValue
                );
            }

        }
        
        echo "<pre>";
        print_r($productsIndex);
        die;

    }
    
    echo json_encode($answer);
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/epilog_after.php"
    );
