<?php
    ///////////////////////////////////////////////////////////////////////
    ///                  Импортируем торговые предложения
    ///////////////////////////////////////////////////////////////////////
    // Либа ручного кэша
    require( $_SERVER["DOCUMENT_ROOT"].  "/local/libs/customcache.lib.php");

    $objPrice = new CPrice;
    $objOffer = new CIBlockElement;
    $ibp = new CIBlockProperty;
    $ibpenum = new CIBlockPropertyEnum;
    $resCatalogStoreProduct = new CCatalogStoreProduct;
    $OFFERS_IBLOCK_ID = OFFER_IB_ID;
    // Перебираем товарные предложения

    // Составляем справочник флагов 
    $ENUM_OFFERS = array();
    $res = CIBlockPropertyEnum::GetList(
        array(),array("IBLOCK_ID"=>$OFFERS_IBLOCK_ID)
    );
    while($data = $res->getNext()){
        $propName = mb_strtolower(trim($data["PROPERTY_NAME"]));
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUM_OFFERS[$propName]))
            $ENUM_OFFERS[$propName] = array(
                "PROP_ID"=>$data["PROPERTY_ID"],
                "CODE"=>$data["PROPERTY_CODE"],
                "ITEMS"=>array()
            );
            
        $ENUM_OFFERS[$propName]["ITEMS"][mb_strtolower($enum["VALUE"])] = 
            $enum["ID"];
    }

    // Обнуляем остатки на складах
    /*
    Не обнуляем, ибо огульное обнуление приводит к ... надо обновлять остатки
    идивидуально
    $res = CCatalogStoreProduct::GetList(array(),array());
    while($store = $res->GetNext()){
        $resCatalogStoreProduct->Update($store["ID"],array("AMOUNT"=>0));
    }
    */
    if(!isset($arOffers[0]))$arOffers = array($arOffers);

    foreach($arOffers as $arOffer){
       
        $XML_ID = explode("#", $arOffer["Ид"]);
        $XML_ID = $XML_ID[0];
        
        // Если склад еданственный
        if(isset($arOffer["Склад"]) && !isset($arOffer["Склад"][0]))
            $arOffer["Склад"] = array($arOffer["Склад"]);
        // Если характеристика едиснтвенная
        if(
            isset($arOffer["ХарактеристикиТовара"]["ХарактеристикаТовара"])
            && 
            !isset($arOffer["ХарактеристикиТовара"]["ХарактеристикаТовара"][0])
        )$arOffer["ХарактеристикиТовара"]["ХарактеристикаТовара"] = 
            array($arOffer["ХарактеристикиТовара"]["ХарактеристикаТовара"]);
        
        
        $offerFields = array(
            "IBLOCK_ID"         =>  $OFFERS_IBLOCK_ID,
            "NAME"              =>  $arOffer["Наименование"],
            "PRICE"             =>  
                $productsIndexDetail[$XML_ID]["Баллы"],
            "XML_ID"            =>  $arOffer["Ид"],
        );

        // Ищем в товарных предложениях с указанным XML_ID
        $res = CIBlockElement::GetList(
            array(),
            array(
                "IBLOCK_ID"=>$OFFERS_IBLOCK_ID,
                "XML_ID"=>$arOffer["Ид"]
            )
        );
        // Если предложения нет - добавляем
        if(!$existsOffer = $res->GetNext()){
            // Добавляем предложение
            $offerId = $objOffer->Add($offerFields);
            // Добавляем продукт
            CCatalogProduct::Add(array(
                "ID"=>$offerId,
                "QUANTITY"=>$arOffer["Количество"],
                "QUANTITY_TRACE"=>"N",
                "CAN_BUY_ZERO"=>"Y"
            ));
            
            // Добавляем цену
            $priceId = $objPrice->Add(
                $arrPriceAdd = array(
                    "PRODUCT_ID"=>$offerId,
                    "CATALOG_GROUP_ID"=>1,
                    "PRICE"=>$productsIndexDetail[$XML_ID]["Баллы"],
                    "CURRENCY"=>"BAL"
                ),
                true
            );
          
            // Добавляем наличие на складах
            if(isset($arOffer["Склад"]) && is_array($arOffer["Склад"]))
                foreach($arOffer["Склад"] as $storage){
                    if(!$resCatalogStoreProduct->Add($arFields = array(
                        "PRODUCT_ID"=>  $offerId,
                        "STORE_ID"=>    
                            $arStoragesIndex[
                                $storage["@attributes"]["ИдСклада"]
                            ],
                        "AMOUNT"=>      
                            $storage["@attributes"]["КоличествоНаСкладе"]
                    ))){
                        echo "<pre>";
                        print_r($arFields);
                        print_r($resCatalogStoreProduct);
                        die;
                    }
                }

        }
        // Если предложения есть - обновляем
        else{
            $offerId = $existsOffer["ID"];
            $objOffer->Update($existsOffer["ID"], $offerFields);
            CCatalogProduct::Update(
                $offerId, 
                array(
                    "QUANTITY"=>$arOffer["Количество"],
                    "QUANTITY_TRACE"=>"N",
                    "CAN_BUY_ZERO"=>"Y"
                )
            );
            
            
            $res = CPrice::GetList(array(),array("PRODUCT_ID"=>$offerId));
            if(!$existsPrice = $res->GetNext()){
                $priceId = $objPrice->Add(
                    array(
                        "PRODUCT_ID"=>$offerId,
                        "CATALOG_GROUP_ID"=>1,
                        "PRICE"=>$productsIndexDetail[$XML_ID]["Баллы"],
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
                        "PRICE"=>$productsIndexDetail[$XML_ID]["Баллы"],
                        "CURRENCY"=>"BAL",
                    ),
                    true
                );
            }
            
            // Обнуляем остатки только для этого товара 
            $res = CCatalogStoreProduct::GetList(array(),array(
                "PRODUCT_ID"=>$offerId
            ));
            while($store = $res->GetNext()){
                $resCatalogStoreProduct->Update($store["ID"],array(
                    "AMOUNT"=>0
                ));
            }
            
            // Прописываем новые остатки
            if(isset($arOffer["Склад"]) && is_array($arOffer["Склад"]))
                foreach($arOffer["Склад"] as $storage){
                    $res = CCatalogStoreProduct::GetList(
                        array(),
                        $restRequest = array(
                            "PRODUCT_ID"=>$offerId,
                            "STORE_ID"  =>$arStoragesIndex[
                                $storage["@attributes"]["ИдСклада"]
                            ]
                    ));
                    
                    // Если запись для товара на складе есть - обновляем
                    // Иначе добавляем
                    if(!$existsRest = $res->GetNext()){
                        $resCatalogStoreProduct->Add(array(
                            "PRODUCT_ID"=>$offerId,
                            "STORE_ID"  =>$arStoragesIndex[
                                $storage["@attributes"]["ИдСклада"]
                            ],
                            "AMOUNT"=>
                                $storage["@attributes"]["КоличествоНаСкладе"]
                        ));
                    }
                    else{
                        $resCatalogStoreProduct->Update(
                            $existsRest["ID"],
                            array(
                                "AMOUNT"=>$storage[
                                    "@attributes"
                                ]["КоличествоНаСкладе"]
                            )
                        );
                    }
                }
            
            
        }
        
        ///////////////////////  Дополнительные изображения
        if(count($productsIndexDetail[$XML_ID]["Картинка"])){

            $arrFile = array();
            
            // Составляем индекс размеров файлов
            $check_prop_value = array();
            foreach($productsIndexDetail[$XML_ID]["Картинка"] as $value){
                $picturePath = $value;
                $picturePath = 
                    $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/"
                        .$picturePath;
                $headers = CFile::MakeFileArray($picturePath);
                $check_prop_value[$headers["size"]] = $value; 
            }
            // Получаем размеры фотографий свойства MORE_PHOTO
            $res = CIBlockElement::GetProperty(
                $OFFERS_IBLOCK_ID, 
                $offerId, 
                array(),array("CODE"=>"MORE_PHOTO")
            );
            while($photoItem = $res->GetNext()){
                $res1 = CFile::GetByID($photoItem["VALUE"]);
                $localFileInfo = $res1->GetNext(); 
                if(isset(
                    $check_prop_value[$localFileInfo["FILE_SIZE"]]
                ))unset(
                    $check_prop_value[$localFileInfo["FILE_SIZE"]]
                );
            }

            // Если хоть одн изображение не совпадает по размерам
            // меняем весь список изображений
            if(count($check_prop_value)){
                // Удаляем все файлы
                $res = CIBlockElement::GetProperty(
                    $OFFERS_IBLOCK_ID, 
                    $offerId, array(),array("CODE"=>"MORE_PHOTO")
                );
                while($photoItem = $res->GetNext())
                    CFile::Delete($photoItem["VALUE"]);
                // Делаем массив для добавления
                foreach($productsIndexDetail[$XML_ID]["Картинка"] as $img){
                    $picturePath = $img;
                    $picturePath = 
                        $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/"
                            .$picturePath;
                    $arrFile[] = array(
                        "VALUE"=>CFile::MakeFileArray($picturePath),
                        "DESCRIPTION"=>""
                    );
                }
                CIBlockElement::SetPropertyValuesEx(
                    $offerId, $OFFERS_IBLOCK_ID, 
                    array('MORE_PHOTO' => $arrFile)
                );
            }
            
        }
        
        ////////// Создаём несуществующие свойства спецпредложения ////////////
        if(isset($arOffer["ХарактеристикиТовара"]["ХарактеристикаТовара"])){
            foreach(
                $arOffer["ХарактеристикиТовара"]["ХарактеристикаТовара"] 
                as 
                $offerProp
            ){
                $nameLower  = mb_strtolower($offerProp["Наименование"]);
                $nameTranslit = mb_strtoupper("PROP1C_".CUtil::translit(
                    $offerProp["Наименование"], "ru", 
                    array("replace_space"=>"_", "replace_other"=>"_")
                ));
                $valueLower = mb_strtolower($offerProp["Значение"]);
                $valueTranslit = mb_strtoupper("VAL1C_".CUtil::translit(
                    $offerProp["Значение"], "ru", 
                    array("replace_space"=>"_", "replace_other"=>"_")
                ));
                
                // Если свойства нет - создаём
                $arFields = array(
                    "NAME"          =>  $offerProp["Наименование"],
                    "ACTIVE"        =>  "Y",
                    "SORT"          =>  "999",
                    "CODE"          =>  $nameTranslit,
                    "PROPERTY_TYPE" =>  "L",
                    "IBLOCK_ID"     =>  $OFFERS_IBLOCK_ID,
                    "VALUES"        =>  array("VALUE"=>$offerProp["Значение"])
                );
                if(!isset($ENUM_OFFERS[$nameLower])){
                    if($propId = $ibp->Add($arFields)){
                        $res = CIBlockPropertyEnum::GetList(array(),array(
                            "PROPERTY_ID"=>$propId
                        ));
                        $arrEnum = $res->GetNext();
                        $ENUM_OFFERS[$nameLower] = array(
                            "PROP_ID"   =>  $propId,
                            "CODE"      =>  $nameTranslit,
                            "ITEMS"     =>  array(
                                $offerProp["Значение"]=>$arrEnum["ID"]
                            )
                        );
                    }
                    else{
                        print_r($ibp);
                        die;
                    }
                }
                else{
                    $propId = $ENUM_OFFERS[$nameLower]["PROP_ID"];
                }

                if(!isset($ENUM_OFFERS[$nameLower]["ITEMS"][$valueLower])){
                    $arrField = array(
                        "PROPERTY_ID"   =>  $propId,
                        "VALUE"         =>  $offerProp["Значение"]
                    );
                    
                    if($enumId = $ibpenum->Add($arrField)){
                        $ENUM_OFFERS[$nameLower]["ITEMS"][$valueLower] = 
                            $enumId;
                    }
                }

                // Вставляем характреристику товара
                CIBlockElement::SetPropertyValueCode(
                    $offerId,$ENUM_OFFERS[$nameLower]["CODE"],
                    $ENUM_OFFERS[$nameLower]["ITEMS"][$valueLower]
                );

            }
            
        }
        
        // Привязывает торговое предложение к каталогу
        CIBlockElement::SetPropertyValueCode(
            $offerId,"CML2_LINK",
            $productsIndex[$XML_ID]
        );
        
    }

    unset($ibp);
    unset($ibpenum);
    // Чистим кэш компонента складов после обновления
    $objComponent = new CBitrixComponent();
    $objComponent->initComponent("ag:stores");
    $objComponent->clearResultCache();

    // Чистим кэш компонента меню разделов 
    $objComponent = new CBitrixComponent();
    $objComponent->initComponent("ag:menu.catalog");
    $objComponent->clearResultCache();

    // Чистим кэш компонента фильтра 
    $objComponent = new CBitrixComponent();
    $objComponent->initComponent("ag:filter");
    $objComponent->clearResultCache();

    // Чистим файловый кэш плитки тизеров
    customCacheClear();

    // Чистим группы memcache
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CCache/CCache.class.php");
    use AGShop\Cache as Cache;
    
    $objCache = new \Cache\CCache("card_product_common_info","0");
    $objCache->clearAll();
    unset($objCache);
    
    $objCache = new \Cache\CCache("card_product_properties","0");
    $objCache->clearAll();
    unset($objCache);
 
    $objCache = new \Cache\CCache("card_section_common_info","0");
    $objCache->clearAll();
    unset($objCache);

    $objCache = new \Cache\CCache("mobile_interests","0");
    $objCache->clearAll();
    unset($objCache);

    $objCache = new \Cache\CCache("mobile_teasers","0");
    $objCache->clearAll();
    unset($objCache);

    $objCache = new \Cache\CCache("sections_list","0");
    $objCache->clearAll();
    unset($objCache);


