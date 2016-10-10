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
            
            // Поиск заказа под XML-Ид
            $res = CSaleOrder::GetList(array(),array("XML_ID"=>$arDocument["Ид"]));
            $arOrder = $res->GetNext();
            
            // Поиск заказа по номеру
            if(!$arOrder){
                $number = 0;
                if(preg_match("#^.*\-(\d+)$#",$arDocument["Ид"], $m))$number = $m[1];
                $res = CSaleOrder::GetList(array(),array("ID"=>$number));
            }
            
            // Если заказ не найден - добавляем, иначе = обновляем
            if(!$arOrder){
                
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
                $objUser = new CUser;
                if(!$existsUser){
                    $userId = $objUser->Add($userData);
                }
                else{
                    $userId = $existsUser["ID"];
                    $objUser->Update($userId, $userData);
                }
                
                // Нормализация товаров
                if(!isset($arDocument["Товары"]["Товар"][0]))
                    $arDocument["Товары"]["Товар"] = array($arDocument["Товары"]["Товар"]);
                
               
                $basketProducts = array();
                foreach($arDocument["Товары"]["Товар"] as $product){
                    if(!isset($product["Ид"]))continue;
                    
                    $XML_ID = $product["Ид"];
                    $resOffer = CIblockElement::GetList(array(),array("IBLOCK_ID"=>3,"XML_ID"=>$XML_ID),false,
                        array("nTopCount"=>1));
                    $existsOffer = $resOffer->GetNext();
                    // Если продукта нет - создаём его прототип
                    if(!$existsOffer){
                    }
                    else{
                        $basketProducts[$existsOffer["ID"]] = array(
                            "count" => $product["Количество"],
                            "price" => $product["ЦенаЗаЕдиницу"]
                        );
                        
                    }
                    
                }
                
                $resOrder = CSaleOrder::GetList(array(),array($cond = "XML_ID"=>$arDocument["Ид"]),
                    false,array("nTopCount"=>1));
                $existsOrder = $resOrder->GetNext();
                // Считаем общую сумму заказа
                $sum = 0;
                foreach($basketProducts as $product)$sum+=$product["count"]*$product["price"];
                
                $arOrder = array(
                   "LID" => "en",
                   "PERSON_TYPE_ID" => 1,
                   "PAYED" => "Y",
                   "CANCELED" => "N",
                   "STORE_ID" => "0",
                   "STATUS_ID" => "N",
                   "PRICE" => $sum,
                   "CURRENCY" => "BAL",
                   "USER_ID" => $userId,
                   "PAY_SYSTEM_ID" => 9,
                   "PRICE_DELIVERY" => 0,
                   "DELIVERY_ID" => 3,
                   "DISCOUNT_VALUE" =>0,
                   "TAX_VALUE" => 0.0,
                   "USER_DESCRIPTION" => ""                    
                );
                
                // Если заказа нет - создаём, есть - обновляем
                $objOrder = new CSaleOrder;
                if(!$existsOrder){
                    echo "Создаём";
                    echo "<pre>";
                    print_r($arOrder);
                    echo "</pre>";
                    
                }
                else{
                }
                
                // Обновляем статус заказа
                
                
                // Если корзины заказа нет - создаём, иначе - обновляем
                
            }
            else{
            }
            
        }
        
        
        
        echo "<pre>";
        print_r($arOrders);
        die;
    }











?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>


