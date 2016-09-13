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
        $productsIndexDetail = array();
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
            if(isset($sectionsIndex[$arProduct["Группы"]["Ид"]])){
                $arFields["IBLOCK_SECTION_ID"] = $sectionsIndex[$arProduct["Группы"]["Ид"]];
                $arFields["SECTION_ID"] = $arFields["IBLOCK_SECTION_ID"];
            }
            else{
                $arFields["IBLOCK_SECTION_ID"] = true;
                $arFields["SECTION_ID"] = true;
            }

            $code = explode("-",$arProduct["Ид"]);
            $code = $code[0];
            $arFields["CODE"]       = CUtil::translit(
                $arProduct["Наименование"], 
                "ru", 
                array("replace_space"=>"-", "replace_other"=>"-")
            )."-".$code;

            $res = CIBlockElement::GetList(array(),array(
                "IBLOCK_ID"=>2,
                "XML_ID"=>$arProduct["Ид"]
            ));

            $objIBlockElement = new CIBlockElement;
            if(!$existsElement = $res->GetNext()){
                if(!$elementId = $objIBlockElement->Add($arFields)){
                    echo "<pre>";
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
            $productsIndexDetail[$arProduct["Ид"]] = $arProduct;

            ////////// Устанавливаем свойства товара
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
        
    }

    if($offersFilename){
        $xmlOffers = file_get_contents($uploadDir.$offersFilename);
        $obOffers = simplexml_load_string(
            $xmlOffers, "SimpleXMLElement" 
        );
        
        $arOffers = json_decode(json_encode((array)$obOffers), TRUE);        
        
        $arStorages = $arOffers["ПакетПредложений"]["Склады"]["Склад"];
        $arOffers = $arOffers["ПакетПредложений"]["Предложения"]["Предложение"];
        
        ///////////////////////////////////////////////////////////////////////
        ///                     Импортируем склады
        ///////////////////////////////////////////////////////////////////////
        $arStoragesIndex = array();
        foreach($arStorages as $arStorage){

            $arStoragesIndex[$arStorage["Ид"]] = 0;

            $arFields = array();
            $arFields["TITLE"] = $arStorage["Наименование"];
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
        
        ///////////////////////////////////////////////////////////////////////
        ///                  Импортируем торговые предложения
        ///////////////////////////////////////////////////////////////////////
        $objPrice = new CPrice;
        $objPrice = new CPrice;
        $objOffer = new CIBlockElement;
        $resCatalogStoreProduct = new CCatalogStoreProduct;
        foreach($arOffers as $arOffer){
            if(isset($arOffer["Склад"]) && !isset($arOffer["Склад"][0]))
                $arOffer["Склад"] = array($arOffer["Склад"]);
                
/*            echo "<pre>";
            print_r($arOffer);
            echo "</pre>";
            continue;
*/
            $offerFields = array(
                "IBLOCK_ID"         =>  3,
                "NAME"              =>  $arOffer["Наименование"],
                "PRICE"             =>  $productsIndexDetail[$arOffer["Ид"]]["Баллы"],
                "XML_ID"            =>  $arOffer["Ид"]
//                "DETAIL_TEXT"       =>  $product["DETAIL_TEXT"],
//                "PREVIEW_TEXT"      =>  $product["PREVIEW_TEXT"],
//                "PREVIEW_TEXT_TYPE" =>  $product["PREVIEW_TEXT_TYPE"],
//                "PREVIEW_PICTURE"   =>  (
//                "DETAIL_PICTURE"    =>  (
            );
            
            $res = CIBlockElement::GetList(array(),array(
                "IBLOCK_ID"=>3,"XML_ID"=>$arOffer["Ид"]
            ));
            if(!$existsOffer = $res->GetNext()){
                $offerId = $objOffer->Add($offerFields);
                CCatalogProduct::Add(array(
                    "ID"=>$offerId,
                    "QUANTITY"=>$arOffer["Количество"],
                    "QUANTITY_TRACE"=>"Y",
                    "CAN_BUY_ZERO"=>"N",
                ));
                $priceId = $objPrice->Add(
                    array(
                        "PRODUCT_ID"=>$offerId,
                        "CATALOG_GROUP_ID"=>1,
                        "PRICE"=>$productsIndexDetail[$arOffer["Ид"]]["Баллы"],
                        "CURRENCY"=>"BAL",
                    ),
                    true
                );
                if(isset($arOffer["Склад"]) && is_array($arOffer["Склад"]))
                    foreach($arOffer["Склад"] as $storage){
                        if(!$resCatalogStoreProduct->Add($arFields = array(
                            "PRODUCT_ID"=>  $offerId,
                            "STORE_ID"=>    $arStoragesIndex[$storage["@attributes"]["ИдСклада"]],
                            "AMOUNT"=>      $storage["@attributes"]["КоличествоНаСкладе"]
                        ))){
                            echo "<pre>";
                            print_r($resCatalogStoreProduct);
                            die;
                        }
                    }
            }
            else{
                $offerId = $existsOffer["ID"];
                $objOffer->Update($existsOffer["ID"], $offerFields);
                CCatalogProduct::Update($offerId, array(
                    "QUANTITY"=>$arOffer["Количество"],
                    "QUANTITY_TRACE"=>"Y",
                    "CAN_BUY_ZERO"=>"N",
                ));
                
                $res = CPrice::GetList(array(),array("PRODUCT_ID"=>$offerId));
                if(!$existsPrice = $res->GetNext()){
                    $priceId = $objPrice->Add(
                        array(
                            "PRODUCT_ID"=>$offerId,
                            "CATALOG_GROUP_ID"=>1,
                            "PRICE"=>$productsIndexDetail[$arOffer["Ид"]]["Баллы"],
                            "CURRENCY"=>"BAL",
                        ),
                        true
                    );
                }
                else{
                    $priceId = $existsPrice["ID"];
                    $objPrice->Update(
                        $priceId,
                        array(
                            "PRODUCT_ID"=>$offerId,
                            "CATALOG_GROUP_ID"=>1,
                            "PRICE"=>$productsIndexDetail[$arOffer["Ид"]]["Баллы"],
                            "CURRENCY"=>"BAL",
                        ),
                        true
                    );
                }
                
                // Обнуляем остатки на складах
                $res = CCatalogStoreProduct::GetList(array(),array("PRODUCT_ID"=>$offerId));
                while($store = $res->GetNext())
                    $resCatalogStoreProduct->Update($store["ID"],array("AMOUNT"=>0));
                    
                // Прописываем новые остатки
                if(isset($arOffer["Склад"]) && is_array($arOffer["Склад"]))
                    foreach($arOffer["Склад"] as $storage){
                        $res = CCatalogStoreProduct::GetList(array(),array(
                            "PRODUCT_ID"=>$offerId,
                            "STORE_ID"  =>$arStoragesIndex[$storage["@attributes"]["ИдСклада"]]
                        ));
                        // Если запись для товара на складе есть - обновляем
                        // Иначе добавляем
                        if(!$existsRest = $res->GetNext()){
                            $resCatalogStoreProduct->Add(array(
                                "PRODUCT_ID"=>$offerId,
                                "STORE_ID"  =>$arStoragesIndex[$storage["@attributes"]["ИдСклада"]],
                                "AMOUNT"=>$storage["@attributes"]["КоличествоНаСкладе"]
                            ));
                        }
                        else{
                            $resCatalogStoreProduct->Update($existsRest,
                                array("AMOUNT"=>$storage["@attributes"]["КоличествоНаСкладе"])
                            );
                        }
                    }
                
                
            }
            
            
            $arOffer["Ид"] = explode("#",$arOffer["Ид"]);
            $arOffer["Ид"] = $arOffer["Ид"][0];
            CIBlockElement::SetPropertyValueCode(
                $offerId, "CML2_LINK", $productsIndex[$arOffer["Ид"]]
            );
            
            echo "<pre>";
            print_r($offerFields);
            echo "</pre>";
        }
die;        
        
    }


    
    echo json_encode($answer);
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/epilog_after.php"
    );
