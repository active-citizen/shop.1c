<?php
    if(!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
        $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/..");
    
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    $uploadDir = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_exchange/";
    $CatalogIblockId = 2;
    $OfferIblockId = 3;
    
    // Получаем имя файла заказов
    $ordersFilename = $_GET["filename"];
    if(!$ordersFilename){
        $dd = opendir($uploadDir);
        while($filename = readdir($dd))
            if(preg_match("#^orders.*\.xml$#",$filename))
                {$ordersFilename = $filename;break;}
        closedir($dd);
    }

    CModule::IncludeModule("sale");
    CModule::IncludeModule("catalog");
    CModule::IncludeModule("iblock");
    CModule::IncludeModule("price");
    global $DB;
    $objOrder   = new CSaleOrder;
    $objUser    = new CUser;
    $objBasket  = new CSaleBasket;
    $objIBlockElement = new CIBlockElement;
    $objPrice = new CPrice;
    if(file_exists($uploadDir.$offersFilename)){
        $xmlOrders = file_get_contents($uploadDir.$ordersFilename);
        $arOrders = simplexml_load_string($xmlOrders, "SimpleXMLElement" );
        $arOrders = json_decode(json_encode((array)$arOrders), TRUE);        
        
        // Нормализуем массив заказов
        if(!isset($arOrders["Документ"][0]))
            $arOrders["Документ"] = array($arOrders["Документ"]);

        foreach($arOrders["Документ"] as $ccc=>$arDocument){
            $arDocument["Телефон"] = preg_replace("#[^\d]#","",$arDocument["Телефон"]);
            if(0 && $ccc>55){break;}else{echo "      ".round(($t1-$t0)*1000,2)."ms\n$ccc) ";}
            $t0 = microtime(true);
            // Поиск заказа под XML-Ид
            $res = CSaleOrder::GetList(
                array(),array("XML_ID"=>$arDocument["Ид"]),false,array("nTopCount"=>1),
                array("ID","PAYED")
            );
            $existsOrder = $res->GetNext();

            // Поиск заказа по номеру
            if(!$existsOrder){
                $res = CSaleOrder::GetList(
                    array(),array("ADDITIONAL_INFO"=>$arDocument["Номер"]),false,
                    array("nTopCount"=>1),
                    array("ID","PAYED")
                );
                $existsOrder = $res->GetNext();
            }
                        
            
            // Бортуем заказы с неверно указанным телефоном
            if(!preg_match("#^\d{7,11}$#",$arDocument["Телефон"])){
                echo "Incorrect phone ".$arDocument["Телефон"]."<br/>";
                $t1 = microtime(true);
                continue;
            }

            // Нормализация товаров
            if(!isset($arDocument["Товары"]["Товар"][0]))
                $arDocument["Товары"]["Товар"] = array($arDocument["Товары"]["Товар"]);
            // пОЛУЧЕНИЕ МАССИВА ТОВАРОВ КОРЗИНЫ
            $basketProducts = array();
            foreach($arDocument["Товары"]["Товар"] as $product){
                if(!isset($product["Ид"])){
                    echo "Incorrect goods id= ".$product["Ид"]."<br/>";
                    continue;
                }
                
                $XML_ID = $product["Ид"];
                $resOffer = CIblockElement::GetList(
                    array(),array("IBLOCK_ID"=>$OfferIblockId,"XML_ID"=>$XML_ID),false,
                    array("nTopCount"=>1),array("ID")
                );
                $existsOffer = $resOffer->GetNext();
                // Если продукта нет - создаём его прототип
                if(!$existsOffer){
                    $product["product_id"] = explode("-",$product["Ид"]);
                    $product["product_id"] = $product["product_id"][0];
                    $product["product_xml_id"] = explode("#",$product["Ид"]);
                    $product["product_xml_id"] = $product["product_xml_id"][0];
                    // Создаём элемент каталога
                    $arrFields = array(
                        "SITE_ID"       =>  "s1",
                        "XML_ID"        =>  $product["product_xml_id"],
                        "NAME"          =>  $product["Наименование"],
                        "CODE"          =>  Cutil::translit($product["Наименование"],"ru",
                            array("replace_space"=>"-","replace_other"=>"-")
                        )."-".$product["product_id"],
                        "IBLOCK_ID"     =>  $CatalogIblockId,
                        "DETAIL_TEXT"   =>  '',
                        "PREVIEW_TEXT"  =>  '',
                        "IBLOCK_SECTION_ID" =>  1,//$categoryId,
                        "SECTION_ID"    =>  1,//$categoryId,
                        "PREVIEW_TEXT_TYPE" =>  'html',
                        "DETAIL_TEXT_TYPE"  =>  'html',
                    );
                    
                    $resCatalog = CIblockElement::GetList(array(),array(
                        "CODE"=>$arrFields["CODE"]
                    ),false,array("nTopCount"=>1));
                    $arCatalog = $resCatalog->GetNext();
                    
                    if (isset($arCatalog["ID"])) {
                        $id = $arCatalog["ID"];
                    }
                    elseif(!$id = $objIBlockElement->Add($arrFields)){
                        echo "failed\n";
                        echo "Cant create catalog item: ".__FILE__.":".__LINE__;
                        $t1 = microtime(true);
                        continue;
                    }
                    else{
                        //print_r($arrFields);
                        //print_r($objIBlockElement);
                        //print_r($product);
                        //echo "failed\n";
                        //echo "Error: ".__FILE__.":".__LINE__;
                        //continue;
                    }
                    
                    // Создаём торговое предложение
                    $arrFields["IBLOCK_ID"] = $OfferIblockId;
                    $arrFields["PRICE"] = $product["ЦенаЗаЕдиницу"];
                    $arrFields["XML_ID"] = $product["Ид"];
                    if(!$offerId = $objIBlockElement->Add($arrFields)){
                        echo "failed\n";
                        echo "cant create offer: ".__FILE__.":".__LINE__;
                        $t1 = microtime(true);
                        continue;
                    }
                    
                    // Назначаем свойства торгового предложения
                    CIBlockElement::SetPropertyValueCode($offerId,"CML2_LINK",$id);
                    
                    // Создаём цену
                    $objPrice->Add(array(
                        "PRODUCT_ID"=>$offerId,
                        "CATALOG_GROUP_ID"=>1,
                        "PRICE"=>$product["ЦенаЗаЕдиницу"],
                        "CURRENCY"=>"BAL",
                    ),true);
                    
                    // Создаём товар на складе
                    CCatalogProduct::Add(array(
                        "ID"=>$offerId,
                        "QUANTITY"=>0,
                        "QUANTITY_TRACE"=>"Y",
                        "CAN_BUY_ZERO"=>"N",
                    ));
                    
                    // Получаем инормацию о получившемся торговом предложении
                    $resOffer = CIBlockElement::GetList(
                        array(),array("IBLOCK_ID"=>$OfferIblockId,"ID"=>$offerId),
                        false,array("nTopCount"=>1)
                    );
                    $existsOffer = $resOffer->GetNext(); 
                }
                // Запоминаем товар для добавления в корзину
                $basketProducts[$existsOffer["ID"]] = array(
                    "count" => $product["Количество"],
                    "name"  => $product["Наименование"],
                    "price" => $product["ЦенаЗаЕдиницу"]
                );
            }
            
            
            // Считаем сумму заказа
            $sum = 0;
            foreach($basketProducts as $product)$sum+=$product["count"]*$product["price"];
            // Бортуем заказы с нулевой суммой
            if(!$sum){
                echo "Empty order sum, OrderId =".$arDocument["Ид"];
                $t1 = microtime(true);
                continue;
            }

            // Выделяем из ФИО фамилию и Имя-Отчество
            $tmpName = explode(" ", $arDocument["Клиент"]);
            $userLastName = $tmpName[0];unset($tmpName[0]);
            $userName = implode(" ",$tmpName);
            // Делаем пользователю случайный пароль
            $password = mb_substr(md5(rand()),0,10);
            
            // Данные для добавления пользователя
            $userData = array(
                "LOGIN"             =>  "u".$arDocument["Телефон"],
                "PASSWORD"          =>  $password,
                "CONFIRM_PASSWORD"  =>  $password,
                "EMAIL"             =>  $arDocument["ЭлектроннаяПочта"],
                "GROUP_ID"          =>  array(2,3,4,6),
                "NAME"              =>  $userName, 
                "LAST_NAME"         =>  $userLastName,
                "PERSONAL_PHONE"    =>  $arDocument["Телефон"],
                "ACTIVE"            =>  "Y"
            );
            
            // Определяем пользователя, если нет - создаём
            $resUser = CUser::GetByLogin($userData["LOGIN"]);
            $existsUser = $resUser->GetNext();
            // Если пользователя нет - создаём
            
            
            if(!$existsUser){
                // Если создание провалилось - сообщаем об ошибке
                if(!$userId = $objUser->Add($userData)){
                    echo "failed\n";
                    echo "cant_create_user: ".__FILE__.":".__LINE__;
                    $t1 = microtime(true);
                    continue;
                }
            }
            else{
                $userId = $existsUser["ID"];
                $objUser->Update($userId, $userData);
            }
            
            // Вычисляем флаги статуса
            if(!isset($arDocument["История"]["Состояние"][0]))
                $arDocument["История"]["Состояние"] = array($arDocument["История"]["Состояние"]);
            // Состояние заказа по умолчанию
            if(!isset($arDocument["История"]["Состояние"][0]["СостояниеЗаказа"]))
                $arDocument["История"]["Состояние"][0]["СостояниеЗаказа"] = 'В работе';
            
            // Статус и опрлата по умолчанию    
            $statusId = "N";$canceled = "N";
            switch($arDocument["История"]["Состояние"][0]["СостояниеЗаказа"]){
                case 'В работе':
                    $statusId = "N";$canceled = "N";
                break;
                case 'Аннулирован':
                    $statusId = "AI";$canceled = "N";
                break;
                case 'Брак':
                    $statusId = "AC";$canceled = "N";
                break;
                case 'Выполнен':
                    $statusId = "F";$canceled = "N";
                break;
                case 'Отменен':
                    $statusId = "AG";$canceled = "Y";
                break;
            }
            
            $arOrder = array(
                "ADDITIONAL_INFO"    =>  $arDocument["Номер"],
                "LID"                =>  "s1",
                "XML_ID"             =>  $arDocument["Ид"],
                "PERSON_TYPE_ID"     =>  1,
                "PAYED"              =>  isset($existsOrder["PAYED"])?$existsOrder["PAYED"]:"N",
                "CANCELED"           =>  $canceled,
                "STATUS_ID"          =>  $statusId,
                "PRICE"              =>  $sum,
                "SUM_PAID"           =>  $sum,
                "CURRENCY"           =>  "BAL",
                "USER_ID"            =>  $userId,
                "PAY_SYSTEM_ID"      =>  9,
                "PRICE_DELIVERY"     =>  0,
                "DELIVERY_ID"        =>  3,
                "DISCOUNT_VALUE"     =>  0,
                "TAX_VALUE"          =>  0,
                "DATE_INSERT"        =>  $DB->FormatDate(
                    $arDocument["Дата"]." ".$arDocument["Время"],
                    "Y-m-d H:i:s"
                ),
                "DATE_UPDATE"        =>  $DB->FormatDate(
                    $arDocument["Дата"]." ".$arDocument["Время"],
                    "Y-m-d H:i:s"
                )
            );
            
            // Определяем ID склада
            $resStorage = CCatalogStore::GetList(array(),array("XML_ID"=>$arDocument["Склад"]),
                false,array("nTopCount"=>1),array("ID"));
            $arStorage = $resStorage->GetNext();
            $storeId = 0;
            if(!isset($arStorage["ID"]))$storeId = $arStorage["ID"];
            if($storeId)$arOrder["STORE_ID"] = $storeId;

                
            // Если заказа нет - создаём, есть - обновляем
            if(!$existsOrder){
                
                if(!$orderId = $objOrder->Add($arOrder)){
                    echo "failed\n";
                    echo "Not created";
                    $t1 = microtime(true);
                    continue;
                }

                echo "Add order_id=$orderId  ";

                // Прицепить сессии корзину
                $userBasketId = $objBasket->GetBasketUserID();
                // Добавляем в корзину продукты
                foreach($basketProducts as $productId=>$item){
            	    $strSql = "INSERT INTO b_sale_basket(FUSER_ID, ORDER_ID, PRODUCT_ID, QUANTITY, NAME, PRICE, DATE_UPDATE, CURRENCY, LID, MODULE, CAN_BUY, DELAY)
            	    VALUES(
                    '".$userId."', 
                	'".$orderId."', 
                        '".$productId."',
                        '".$item["count"]."',
                        '".$DB->ForSql($item["name"])."',
                        '".$item["price"]."',
                        '".$DB->GetNowFunction()."',
                        'BAL',
                        's1',
                        'catalog',
                        'Y',
                        'N'
                    )";
            	    $DB->Query($strSql);
                }
                CSaleBasket::OrderBasket($orderId, $userBasketId);
                CSaleOrder::PayOrder($orderId,"Y",true,false); //?????
            }
            else{
                $orderId = $existsOrder["ID"];
                echo "Update order_id = $orderId ";
                
                CSaleOrder::Update($orderId, $arOrder);
                if($arOrder["STATUS_ID"]!=$statusId){
                    // Меняем статус
                    CSaleOrder::StatusOrder($orderId, $statusId);
                }
                
                // Ищем корзину для этого заказа
                //// $resBasket = CSaleBasket::GetList(array(),array("ORDER_ID"=>$orderId),false,array("nTopCount"=>1));
                // Удаляем корзины заказа
                //// while($arBasket = $resBasket->GetNext())CSaleBasket::Delete($arBasket["ID"]);
                
                /*
                foreach($basketProducts as $productId=>$item){
            	    
            	    $strSql = "INSERT INTO b_sale_basket(FUSER_ID, ORDER_ID, PRODUCT_ID, QUANTITY, NAME, PRICE, DATE_UPDATE, CURRENCY, LID, MODULE, CAN_BUY, DELAY)
            	    VALUES(
        		'".$userId."', 
                	'".$orderId."', 
                        '".$productId."',
                        '".$item["count"]."',
                        '".$DB->ForSql($item["name"])."',
                        '".$item["price"]."',
                        '".$DB->GetNowFunction()."',
                        'BAL',
                        's1',
                        'catalog',
                        'Y',
                        'N'
                    )";
            	    $DB->Query($strSql);
                }
                */
            }
            $t1 = microtime(true);
        }
        
    }
    
    echo "success";
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>


