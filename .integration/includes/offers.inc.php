<?php
    ///////////////////////////////////////////////////////////////////////
    ///                  Импортируем торговые предложения
    ///////////////////////////////////////////////////////////////////////
    $objPrice = new CPrice;
    $objPrice = new CPrice;
    $objOffer = new CIBlockElement;
    $resCatalogStoreProduct = new CCatalogStoreProduct;
    // Перебираем товарные предложения
    foreach($arOffers as $arOffer){
        // Если склад еданственный
        if(isset($arOffer["Склад"]) && !isset($arOffer["Склад"][0]))
            $arOffer["Склад"] = array($arOffer["Склад"]);
            
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
        
        // Ищем в товарных предложениях с указанным XML_ID
        $res = CIBlockElement::GetList(array(),array("IBLOCK_ID"=>3,"XML_ID"=>$arOffer["Ид"]));
        // Если предложения нет - добавляем
        if(!$existsOffer = $res->GetNext()){
            // Добавляем предложение
            $offerId = $objOffer->Add($offerFields);
            // Добавляем продукт
            CCatalogProduct::Add(array("ID"=>$offerId,"QUANTITY"=>$arOffer["Количество"],"QUANTITY_TRACE"=>"Y","CAN_BUY_ZERO"=>"N"));
            // Добавляем цену
            $priceId = $objPrice->Add(
                array("PRODUCT_ID"=>$offerId,"CATALOG_GROUP_ID"=>1,"PRICE"=>$productsIndexDetail[$arOffer["Ид"]]["Баллы"],"CURRENCY"=>"BAL"),
                true
            );
            // Добавляем наличие на складах
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
        // Если предложения есть - обновляем
        else{
            $offerId = $existsOffer["ID"];
            $objOffer->Update($existsOffer["ID"], $offerFields);
            CCatalogProduct::Update($offerId, array("QUANTITY"=>$arOffer["Количество"],"QUANTITY_TRACE"=>"Y","CAN_BUY_ZERO"=>"N",));
            
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
