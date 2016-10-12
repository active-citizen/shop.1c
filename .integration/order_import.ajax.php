<?php
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    $uploadDir = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_exchange/";
    
    // Получаем имя файла каталога
    $dd = opendir($uploadDir);
    $importFilename = '';
    while($filename = readdir($dd))
        if(preg_match("#^import.*\.xml$#",$filename))
            {$importFilename = $filename;break;}
    closedir($dd);
    
    // Получаем имя файла заказов
    $dd = opendir($uploadDir);
    $offersFilename = '';
    while($filename = readdir($dd))
        if(preg_match("#^orders.*\.xml$#",$filename))
            {$offersFilename = $filename;break;}
    closedir($dd);

    CModule::IncludeModule("sale");
    CModule::IncludeModule("catalog");
    CModule::IncludeModule("iblock");
    CModule::IncludeModule("price");
    $objOrder   = new CSaleOrder;
    $objUser    = new CUser;
    $objBasket  = new CSaleBasket;
    if(file_exists($uploadDir.$offersFilename)){
        $xmlOrders = file_get_contents($uploadDir.$offersFilename);
        $arOrders = simplexml_load_string($xmlOrders, "SimpleXMLElement" );
        $arOrders = json_decode(json_encode((array)$arOrders), TRUE);        
        
        // Нормализуем массив заказов
        if(!isset($arOrders["Документ"][0]))
            $arOrders["Документ"] = array($arOrders["Документ"]);
        
        
        foreach($arOrders["Документ"] as $arDocument){
            
            // Бортуем заказы с неверно указанным телефоном
            if(!preg_match("#^\d{11}$#",$arDocument["Телефон"]))continue;

            // Нормализация товаров
            if(!isset($arDocument["Товары"]["Товар"][0]))
                $arDocument["Товары"]["Товар"] = array($arDocument["Товары"]["Товар"]);
            // пОЛУЧЕНИЕ МАССИВА ТОВАРОВ КОРЗИНЫ
            $basketProducts = array();
            foreach($arDocument["Товары"]["Товар"] as $product){
                if(!isset($product["Ид"]))continue;
                
                $XML_ID = $product["Ид"];
                $resOffer = CIblockElement::GetList(array(),array("IBLOCK_ID"=>3,"XML_ID"=>$XML_ID),false,
                    array("nTopCount"=>1));
                $existsOffer = $resOffer->GetNext();
                // Если продукта нет - создаём его прототип
                if(!$existsOffer){
                    // Создаём торговое предложение
                    // Назначаем свойства торгового предложения
                    
                    // Создаём элемент каталога
                    // Назначаем свойства элемента каталога
                    
                    // Создаём товар на складе
                    
                    // Создаём цену
                }
                else{
                    $basketProducts[$existsOffer["ID"]] = array(
                        "count" => $product["Количество"],
                        "name"  => $product["Наименование"],
                        "price" => $product["ЦенаЗаЕдиницу"]
                    );
                    
                }
            }
            // Считаем сумму заказа
            $sum = 0;
            foreach($basketProducts as $product)$sum+=$product["count"]*$product["price"];
            // Бортуем заказы с нулевой суммой
            if(!$sum)continue;
            
            $tmpName = explode(" ", $arDocument["Клиент"]);
            $userLastName = $tmpName[0];
            $password = mb_substr(md5(rand()),0,10);
            unset($tmpName[0]);
            $userName = implode(" ",$tmpName);
            
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
                if(!$userId = $objUser->Add($userData)){
                    echo "<pre>";
                    print_r($objUser);
                    die;
                }
            }
            else{
                $userId = $existsUser["ID"];
                $objUser->Update($userId, $userData);
            }
            
            // Вычисляем флаги статуса
            if(!isset($arDocument["История"]["Состояние"][0]))
                $arDocument["История"]["Состояние"] = array($arDocument["История"]["Состояние"]);
            if(!isset($arDocument["История"]["Состояние"][0]["СостояниеЗаказа"]))
                $arDocument["История"]["Состояние"][0]["СостояниеЗаказа"] = 'В работе';
                
            $statusId = "N";
            $canceled = "N";
            switch($arDocument["История"]["Состояние"][0]["СостояниеЗаказа"]){
                case 'В работе':
                    $statusId = "N";
                    $canceled = "N";
                break;
                case 'Аннулирован':
                    $statusId = "AI";
                    $canceled = "N";
                break;
                case 'Брак':
                    $statusId = "AC";
                    $canceled = "N";
                break;
                case 'Выполнен':
                    $statusId = "F";
                    $canceled = "N";
                break;
                case 'Отменён':
                    $statusId = "AG";
                    $canceled = "Y";
                break;
            }
                
            // Поиск заказа под XML-Ид
            $res = CSaleOrder::GetList(array(),array("XML_ID"=>$arDocument["Ид"]));
            $existsOrder = $res->GetNext();
            
            // Поиск заказа по номеру
            if(!$existsOrder){
                $number = 0;
                if(preg_match("#^.*\-(\d+)$#",$arDocument["Ид"], $m))$number = $m[1];
                $res = CSaleOrder::GetList(array(),array("ID"=>$number));
                $existsOrder = $res->GetNext();
            }

            $arOrder = array(
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
                false,array("nTopCount"=>1));
            $arStorage = $resStorage->GetNext();
            $storeId = 0;
            if(!isset($arStorage["ID"]))$storeId = $arStorage["ID"];
            if($storeId)$arOrder["STORE_ID"] = $storeId;

                
            // Если заказа нет - создаём, есть - обновляем
            if(!$existsOrder){
                if(!$orderId = $objOrder->Add($arOrder)){
                    echo "failed ";
                    echo "Not created";
                    die;
                }

                // Прицепить сессии корзину
                $userBasketId = $objBasket->GetBasketUserID();
                // Добавляем в корзину продукты
                foreach($basketProducts as $productId=>$item){
                    if(!$basketItemId = $objBasket->Add($addArrBasket = 
                        array(
                            "PRODUCT_ID"        =>  $productId,
                            "PRICE"             =>  $item["price"],
                            "CURRENCY"          =>  "BAL",
                            "QUANTITY"          =>  $item["count"],
                            "LID"               =>  LANG,
                            "DELAY"             =>  "N",
                            "CAN_BUY"           => "Y",
                            "MODULE"            => "catalog",
                            "NAME"              =>  $item["name"],
                        )
                    )){
                        $this->error = $objBasket->LAST_ERROR;
                        return false;
                    }
                }
                echo "<pre>";
                print_r($addArrBasket);
                echo "</pre>";
                CSaleBasket::OrderBasket($orderId, $userBasketId);
                CSaleOrder::PayOrder($orderId,"Y",true,false);
            }
            else{
                echo "Обновляем: ";
                echo "<pre>";
                print_r($existsOrder);
                echo "</pre>";
                $orderId = $existsOrder["ID"];
                CSaleOrder::Update($orderId, $arOrder);
            }
                
        }
        
    }
    
    echo "success";











?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>


