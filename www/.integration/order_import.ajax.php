<?php
    // Output debug messages to 1C exchange
    define("IMPORT_DEBUG",false);
    if(!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
        $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/..");

    require(
        $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"
    );
    require("includes/datafilter.lib.php");
    require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");
    
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CAGShop.class.php"
    );
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogOffer.class.php"
    );
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegration.class.php"
    );
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php"
    );
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationParking.class.php"
    );
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationInfotech.class.php"
    );
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CSSAG/CSSAGAccount.class.php"
    );

    use AGShop\Integration as Integration;
    use AGShop\Catalog as Catalog;
    use AGShop\SSAG as SSAG;


    // Если блокировку поставили и она не протухла - отваливаемся
    // Иначе ставим свою и выдаём обмен
    /*
    if($nWaitTime = orderOrderIsLocked()){
        echo "Failed : query is locked. Wait for $nWaitTime seconds";
        die;
    }
    else{
        orderOrderSetLock();
    }
    */


    if(!$USER->IsAdmin()){
        echo "failure\nAccess Denied\n";
        die;
    }

    $uploadDir = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_exchange/";
    $CatalogIblockId = CATALOG_IB_ID;
    $OfferIblockId = OFFER_IB_ID;
    
   //$res = CSaleOrder::GetList(array("DATE_INSERT"=>"ASC"));
    
    // Получаем имя файла заказов
    $ordersFilename = $_GET["filename"];
    if(!$ordersFilename){
        $dd = opendir($uploadDir);
        while($filename = readdir($dd))
            if(
                preg_match("#^orders.*\.xml$#",$filename)
                ||
                preg_match("#^orders.*\.zip$#",$filename)
           )
                {$ordersFilename = $filename;break;}
        closedir($dd);
    }
  
    // Если имя файла ZIP, распаковываем перед употреблением
    if(preg_match("#^.*\.zip$#",$ordersFilename)){
	    $zipFilename = $uploadDir.$ordersFilename;
	    if(!file_exists($zipFilename)){
	        echo "failed\n$zipFilename is not exists";
	        die;
	    }
	
        $zip = new ZipArchive();
        if(!$zip->open($zipFilename)){
            echo "failed\n";
            echo "Cant open $zipFilename";
            die;
        }
        if(!$nZipFilesCount = $zip->numFiles){
            echo "failed\n";
            echo "Archive hasnt any files";
            die;
        }
        if($nZipFilesCount>1){
            echo "failed\n";
            echo "Archive has more than 1 file";
            die;
        }
        $arZipStat = $zip->statIndex (0);
        if(!$arZipStat["name"] || !preg_match("#^.*\.xml$#",$arZipStat["name"])){
            echo "failed\n";
            echo "Archive hasnt xml file";
            die;
        }
        if(!$zip->extractTo($uploadDir)){
            echo "failed\n";
            echo "Cant extract archive";
            die;
        }
        
        $ordersFilename = $arZipStat["name"];
    }

    CModule::IncludeModule("catalog");
    CModule::IncludeModule("iblock");
    CModule::IncludeModule("price");
    global $DB;
    $objOrder   = new CSaleOrder;
    $objUser    = new CUser;
    $objBasket  = new CSaleBasket;
    $objIBlockElement = new CIBlockElement;
    $objPrice = new CPrice;
    
    // Определяем ID платёжной системы "Внутренний счёт", если такого
    // нет - берём первый же активный
    $arPaySystem = CSalePaySystem::GetList(
        array("ID"=>"ASC"),
        array("NAME"=>"Внутренний счет"),
        false,array("nTopCount"=>1),array("ID")
    )->Fetch();
    if(!$arPaySystem)
        $arPaySystem = CSalePaySystem::GetList(
            array("ID"=>"ASC"),
            array("ACTIVE"=>"Y"),
            false,array("nTopCount"=>1),array("ID")
        )->Fetch();
    // Определяем ID системы доставки "Самовывоз"
    // нет - берём первый же активный
    //echo "asd";
    $arDelivery = CSaleDelivery::GetList(
        array("ID"=>"ASC"),
        array("NAME"=>"Самовывоз"),
        false,array("nTopCount"=>1,array("ID"))
    )->Fetch();
    if(!$arDelivery)
        $arDelivery = CSaleDelivery::GetList(
            array("ID"=>"ASC"),array("ACTIVE"=>"Y"),false,array(
                "nTopCount"=>1,array("ID")
            )
        )->Fetch();
   
    header("Content-type: text/plain; charset=UTF-8");
//    echo file_get_contents($uploadDir.$ordersFilename);
//    die;

    $nOrderCounter = 0;
    if(file_exists($uploadDir.$ordersFilename)){

        $xmlOrders = file_get_contents($uploadDir.$ordersFilename);
        $arOrders = simplexml_load_string($xmlOrders, "SimpleXMLElement" );
        //$arOrders = json_decode(json_encode((array)$arOrders), TRUE);        

        // Нормализуем массив заказов
        if(!isset($arOrders["Документ"][0]))
            $arOrders["Документ"] = array($arOrders["Документ"]);
        elseif(!isset($arOrders->Документ[0]))
            $arOrders->Документ = array($arOrders->Документ);


        $ccc = 0;
        foreach($arOrders->Документ as $arDocument){

            $ccc++;
            $arDocument = json_decode(json_encode((array)$arDocument), TRUE); 
            $arDocument["Телефон"] = preg_replace("#[^\d]#","",$arDocument["Телефон"]);
            if($ccc>3500){/*break;*/}else{
//                if(IMPORT_DEBUG)
//                    echo "      ".round(($t1-$t0)*1000,2)."ms\n$ccc) ";
            }

            // Поиск заказа под XML-Ид
            $sQuery = "
                SELECT
                    `ID`,`PAYED`,`STATUS_ID`,`ADDITIONAL_INFO`,`STORE_ID`
                FROM
                    `b_sale_order`
                WHERE
                    `XML_ID`='".$arDocument["Ид"]."'
                LIMIT
                    1
            ";
            $existsOrder = $DB->Query($sQuery)->Fetch();
            /*
            $res = CSaleOrder::GetList(
                array(),
                array("XML_ID"=>$arDocument["Ид"]),
                false,
                array("nTopCount"=>1),
                array("ID","PAYED","STATUS_ID","ADDITIONAL_INFO","STORE_ID")
            );
            $existsOrder = $res->Fetch();
            */ 

            // Поиск заказа по номеру
            if(!$existsOrder){
                $sQuery = "
                    SELECT
                        `ID`,`PAYED`,`STATUS_ID`,`ADDITIONAL_INFO`,`STORE_ID`
                    FROM
                        `b_sale_order`
                    WHERE
                        `ADDITIONAL_INFO`='".$arDocument["Номер"]."'
                    LIMIT
                        1
                ";
                $existsOrder = $DB->Query($sQuery)->Fetch();
                /*
                $res = CSaleOrder::GetList(
                    array(),array("ADDITIONAL_INFO"=>$arDocument["Номер"]),false,
                    array("nTopCount"=>1),
                    array("ID","PAYED","STATUS_ID","ADDITIONAL_INFO")
                );
                $existsOrder = $res->Fetch();
                */
            }

            // Бортуем заказы с неверно указанным телефоном
            if(!preg_match("#^\d{5,11}$#",$arDocument["Телефон"])){
                if(IMPORT_DEBUG){
                    echo "Order_num=".$arDocument["Номер"].
                        ": Incorrect phone ".print_r($arDocument["Телефон"],1)."\n";
                }
                continue;
            }
 
             // Нормализация товаров
            if(!isset($arDocument["Товары"]["Товар"][0]))
                $arDocument["Товары"]["Товар"] = 
                    array($arDocument["Товары"]["Товар"]);
            // пОЛУЧЕНИЕ МАССИВА ТОВАРОВ КОРЗИНЫ
            $basketProducts = array();
            foreach($arDocument["Товары"]["Товар"] as $product){
                if(!isset($product["Ид"])){
                if(IMPORT_DEBUG)
                    echo "Order_num=".$arDocument["Номер"].
                        ":  Incorrect product XML_ID
                        ".print_r($product["Ид"],1)."\n";
                    continue;
                }


                if(isset($product["ИмяПоля1"]) || isset($product["ИмяПоля2"]))
                    $product["Промокоды"] = array(
                        "ИмяПараметра1"     =>
                            isset($product["ИмяПоля1"])?$product["ИмяПоля1"]:"",
                        "ЗначениеПараметра1"=>
                            isset($product["ЗначПоля1"])?$product["ЗначПоля1"]:"",
                        "ИмяПараметра2"     =>
                            isset($product["ИмяПоля2"])?$product["ИмяПоля2"]:"",
                        "ЗначениеПараметра2"=>
                            isset($product["ЗначПоля2"])?$product["ЗначПоля2"]:"",
                    );
                // Запоминаем промокоды для письма
                if(isset($product["Промокоды"]))
                    $GLOBALS["promocodes"] = $product["Промокоды"];
                
               
                $XML_ID = $product["Ид"];
                $resOffer = CIblockElement::GetList(
                    array(),
                    array("IBLOCK_ID"=>$OfferIblockId,"XML_ID"=>$XML_ID),
                    false,
                    array("nTopCount"=>1),array("ID","NAME")
                );
                $existsOffer = $resOffer->Fetch();
                // Если продукта нет - создаём его прототип
                if(!$existsOffer){
                    // Нет, не создаём
                    continue;
                    $product["product_id"] = explode("-",$product["Ид"]);
                    $product["product_id"] = $product["product_id"][0];
                    $product["product_xml_id"] = explode("#",$product["Ид"]);
                    $product["product_xml_id"] = $product["product_xml_id"][0];
                    // Создаём элемент каталога
                    $arrFields = array(
                        "SITE_ID"       =>  "s1",
                        "XML_ID"        =>  $product["product_xml_id"],
                        "NAME"          =>  $product["Наименование"],
                        "CODE"          =>  Cutil::translit(
                                $product["Наименование"],
                                "ru",
                                array("replace_space"=>"-","replace_other"=>"-")
                            )."-".$product["product_id"],
                        "IBLOCK_ID"     =>  $CatalogIblockId,
                        "DETAIL_TEXT"   =>  '',
                        "PREVIEW_TEXT"  =>  '',
                        "IBLOCK_SECTION_ID" =>  1,//$categoryId,
                        "SECTION_ID"    =>  1,//$categoryId,
                        "PREVIEW_TEXT_TYPE" =>  'html',
                        "DETAIL_TEXT_TYPE"  =>  'html'
                    );
                    
                    $resCatalog = CIblockElement::GetList(array(),array(
                        "CODE"=>$arrFields["CODE"]
                    ),false,array("nTopCount"=>1));
                    $arCatalog = $resCatalog->GetNext();
                    
                    if (isset($arCatalog["ID"])) {
                        $id = $arCatalog["ID"];
                    }
                    elseif(!$id = $objIBlockElement->Add($arrFields)){
                        if(IMPORT_DEBUG)
                            echo "Order_num=".$arDocument["Номер"].
                                ": Cant create catalog item ".print_r($arrFields, 1)."\n";
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
                        if(IMPORT_DEBUG)
                            echo "Order_num=".$arDocument["Номер"].
                                "   : Cant offer item ".print_r($arrFields, 1)."\n";
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
                        "QUANTITY"=>1000,
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
                    "name"  => $existsOffer["NAME"],
                    "price" => $product["ЦенаЗаЕдиницу"]
                );
            }
          
            // Если заказ не содержит товаров то пропускаем такой заказ
            if(!$basketProducts){
                // Отмечаем, что он был обработан
                $nOrderCounter++;    
                continue;
            }
           
            // Считаем сумму заказа
            $sum = 0;
            foreach($basketProducts as $product)$sum+=$product["count"]*$product["price"];
            // Бортуем заказы с нулевой суммой
            /*
            if(!$sum){
                //echo "Empty order sum, OrderId =".$arDocument["Ид"];
                $t1 = microtime(true);
                continue;
            }
            */

            // Выделяем из ФИО фамилию и Имя-Отчество
            $tmpName = explode(" ", $arDocument["Клиент"]);
            $userLastName = $tmpName[0];unset($tmpName[0]);
            $userName = implode(" ",$tmpName);
            // Делаем пользователю случайный пароль
            $password = mb_substr(md5(rand()),0,10);
            $sEmailRegister =  
                preg_match(
                    "#^[\d\w\-\_\.]{2,}\@[\d\w\-\_]{2,}(\.[\d\w\-\_]{2,})*$#",
                    $arDocument["ЭлектроннаяПочта"]
                )
                ?
                $arDocument["ЭлектроннаяПочта"]
                :
                "u".$arDocument["Телефон"]."@shop.ag.mos.ru"
                ;
            // Данные для добавления пользователя
            $userData = array(
                "LOGIN"             =>  "u".$arDocument["Телефон"],
                "PASSWORD"          =>  $password,
                "CONFIRM_PASSWORD"  =>  $password,
                "EMAIL"             =>  $sEmailRegister,
                "GROUP_ID"          =>  array(2,3,4,6),
                "NAME"              =>  dataNormalize($userName), 
                "LAST_NAME"         =>  dataNormalize($userLastName),
                "PERSONAL_PHONE"    =>  $arDocument["Телефон"],
                "PERSONAL_NOTES"    =>  "original email: ".$arDocument["ЭлектроннаяПочта"],
                "ACTIVE"            =>  "Y"
            );
            
            // Определяем пользователя, если нет - создаём
            $resUser = CUser::GetByLogin($userData["LOGIN"]);
            $existsUser = $resUser->Fetch();
            // Если пользователя нет - создаём
            //  Если есть - обновляем ему EMAIL

        
            
            if(!$existsUser){
                // Если создание провалилось - сообщаем об ошибке
                if(!$userId = $objUser->Add($userData)){
                    if(IMPORT_DEBUG)
                        echo "Order_num=".$arDocument["Номер"].
                            ": Cant create user ".print_r($userData, 1)."\n";
                    continue;
                }
            }
            else{
                $userId = $existsUser["ID"];
                $objUser->Update($userId, ["EMAIL"=>$sEmailRegister]);
            }

            // Нормализуем историю
            if(
                isset($arDocument["История"]["Состояние"])
                &&
                $arDocument["История"]["Состояние"]
                &&
                !$arDocument["История"]["Состояние"][0]
            )
               $arDocument["История"]["Состояние"][0]
               =$arDocument["История"]["Состояние"];  

            // Определяем склад заказа
            if(
                !isset($arDocument["История"]["Состояние"][0]["Склад"])
                ||
                !trim($arDocument["История"]["Состояние"][0]["Склад"])
            ){
                if(IMPORT_DEBUG)
                    echo "Store ID undefined ".print_r($arDocument,1)."\n";
                continue;
            }
           
            // Запоминаем комментарий к заказу
            $sSupportComment =
               $arDocument["История"]["Состояние"][0]["Комментарий"];

            $sStoreId = $arDocument["История"]["Состояние"][0]["Склад"];
            if(!$arStore = CCatalogStore::GetList(
                array(),
                array("XML_ID"=>$sStoreId),
                false,
                array("nTopCount"=>1),
                array("ID")
            )->Fetch()){
                if(IMPORT_DEBUG){
                    echo "Order_num=".$arDocument["Номер"]." Store ID not found ".
                        $arDocument["История"]["Состояние"][0]["Склад"]."\n";
//                    print_r($arDocument);
                }
                continue;
            }
            
            // Вычисляем флаги статуса
            if(!isset($arDocument["История"]["Состояние"][0]))
                $arDocument["История"]["Состояние"] = array($arDocument["История"]["Состояние"]);
            // Состояние заказа по умолчанию
            if(!isset($arDocument["История"]["Состояние"][0]["СостояниеЗаказа"]))
                $arDocument["История"]["Состояние"][0]["СостояниеЗаказа"] = 'В работе';
            // Определяем дату смены статуса
            if(!isset($arDocument["История"]["Состояние"][0]["ДатаИзменения"]))
                $arDocument["История"]["Состояние"][0]["СостояниеЗаказа"] =
                    date("Y-m-d H:i:s");


            $arDate = date_parse(
                $arDocument["История"]["Состояние"][0]["ДатаИзменения"]
            );
            if(!$arDate["error_count"]){
                $sDateStatus = sprintf("%04d",$arDate["year"])
                    ."-".sprintf("%02d",$arDate["month"])
                    ."-".sprintf("%02d",$arDate["day"])
                    ." ".sprintf("%02d",$arDate["hour"])
                    .":".sprintf("%02d",$arDate["minute"])
                    .":".sprintf("%02d",$arDate["second"]);
            }
            else{
                $sDateStatus = date("Y-m-d H:i:s");
            }

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
                case 'Отклонен':
                    $statusId = "AF";$canceled = "Y";
                break;
            }

           
            $arOrder = array(
                "ADDITIONAL_INFO"   =>  $arDocument["Номер"],
                "LID"               =>  "s1",
                "XML_ID"            =>  $arDocument["Ид"],
                "PERSON_TYPE_ID"    =>  1,
                "PAYED"             =>  isset($existsOrder["PAYED"])?$existsOrder["PAYED"]:"N",
                "CANCELED"          =>  $canceled,
                "STATUS_ID"         =>  $statusId,
                "CURRENCY"          =>  "BAL",
                "USER_ID"           =>  $userId,
                "PAY_SYSTEM_ID"     =>  $arPaySystem["ID"],
                "PRICE_DELIVERY"    =>  0,
                "DELIVERY_ID"       =>  $arDelivery["ID"],
                "DISCOUNT_VALUE"    =>  0,
                "TAX_VALUE"         =>  0,
                "STORE_ID"          =>  $arStore["ID"]
                ,
                /*
                "DATE_INSERT"       =>  $DB->FormatDate(
                    $arDocument["Дата"]." ".$arDocument["Время"],
                    "YYYY-MM-DD HH:MI:SS",
                    "DD.MM.YYYY HH:MI:SS"
                ),
                
                "DATE_UPDATE"       =>  $DB->FormatDate(
                    trim($arDocument["Дата"])." ".trim($arDocument["Время"]),
                    "YYYY-MM-DD HH:MI:SS",
                    "DD.MM.YYYY HH:MI:SS"
                )
                */
            );

            if($sum){
                $arOrder["SUM_PAID"] = $sum;
                $arOrder["PRICE"] = $sum;
            }
            
            // Определяем ID склада
            /*
            $resStorage = CCatalogStore::GetList(array(),array("XML_ID"=>$arDocument["Склад"]),
                false,array("nTopCount"=>1),array("ID"));
            $arStorage = $resStorage->GetNext();
            $storeId = 0;
            if(!isset($arStorage["ID"]))$storeId = $arStorage["ID"];
            if($storeId)$arOrder["STORE_ID"] = $storeId;
            */

            /*
            +--------------+---------+---------------+-----------------+
            |   Префикс    | Откуда  | Снятие баллов | Снятие остатков |
            +--------------+---------+---------------+-----------------+
            |       Б      | Битрикс |      +        |        +        |
            |       О      |    1С   |      +        |        +        |
            |       М      |    1С   |      -        |        +        |
            +--------------+---------+---------------+-----------------+
           
            */
            // Если заказа нет - создаём, есть - обновляем
            // Определяем префикс заказа
            $sPrefix = '';
            if(preg_match("#^.*?(.)\-(.*)$#u", $arOrder["ADDITIONAL_INFO"], $m))
                $sPrefix = $m[1];

            // Посылать ли письмо
            $bSendEmail = true;
            /*
            if(
                isset($arDocument["История"]["Состояние"][0]['Уведомление'])
                && $arDocument["История"]["Состояние"][0]['Уведомление']=='Нет'
            )$bSendEmail = false;
            */
            if($sPrefix=="М"){
                $bSendEmail = false;
            }

            $sArtNumber = $arDocument["Товары"]["Товар"][0]["Артикул"];
            if(!$existsOrder 
                && (
                    $sPrefix=="О"
                    ||
                    $sPrefix=="М"
                    ||
                    $sPrefix=="Б"
                )
            ){
                 
                if(!$orderId = $objOrder->Add($arOrder)){
                    if(IMPORT_DEBUG){
                        echo "failed\n";
                        echo "Not created:";
                        print_r($arOrder);
                        print_r($objOrder);
                        continue;
                    }
                }

                //echo "Add order_id=$orderId  ";
                // Прицепить сессии корзину
                $userBasketId = $objBasket->GetBasketUserID();
                // Добавляем в корзину продукты
                $nTotalSum = 0;
                foreach($basketProducts as $productId=>$item){
                    // 
                    if($sPrefix=="М")$item["price"] = 0;
                    if($sPrefix=="М" && (
                        $sArtNumber=='parking'
                        ||
                        $sArtNumber=='troyka'
                    ))$item["price"] = 1;
                    // 
            	    $strSql = "
                        INSERT INTO b_sale_basket(
                            FUSER_ID, 
                            ORDER_ID, 
                            PRODUCT_ID, 
                            QUANTITY, 
                            NAME, 
                            PRICE, 
                            DATE_UPDATE, 
                            CURRENCY, 
                            LID, 
                            MODULE, 
                            CAN_BUY, 
                            DELAY
                        )
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
                        )
                    ";
            	    $DB->Query($strSql);
                    $nTotalSum += $item["price"];
                }
                CSaleBasket::OrderBasket($orderId, $userBasketId);
                orderPropertiesUpdate($orderId,IMPORT_DEBUG);

                // Статус парковки
                // 0 - не заказывалась
                // 1 - Успешно
                // 2 - неудачно
                $sParkingStatus = 0;
                if($sArtNumber=='parking'){
                    $objParking = new \Integration\CIntegrationParking(
                        str_replace("u","",$userData["LOGIN"])
                    );

                    $objParking->payment($arOrder["ADDITIONAL_INFO"]);
                    // Производим транзакцию в парковку
                    if($objParking->getErrors()){
                        // Сохраняем неуспешный статус
                        $sParkingStatus = 2;
                    }
                    else{
                        // Сохраняем успешный статус
                        $sParkingStatus = 1;
                    }
                    // Запоминаем номер транзакции 
                    $objParking->linkOrderTransact($arOrder["ADDITIONAL_INFO"]);
                }

                $objOffer = new \Catalog\CCatalogOffer;
                $arOffer = $objOffer->getById($productId);
                ////////////////// Статус интеграции с инфотехом
                // 0 - не заказывалась
                // 1 - Успешно
                // 2 - неудачно
                $sInfotechStatus = 0;
                if($nPriceCategory = intval(
                    $arOffer["PROPERTIES"]["INFOTECH_CATEGORY_PRICE_ID"]
                )){
                    $objInfotech = new \Integration\CIntegrationInfotech(
                        str_replace("u","",$userData["LOGIN"]),
                        $arOrder["ADDITIONAL_INFO"]
                    );
                    
                    if($nInfotechOrderId = $objInfotech->paymentWithoutSeat(
                        $nPriceCategory, $item["count"]
                    )){
                        $sInfotechStatus = 1;
                    }
                    else{
                        $sInfotechStatus = 2;
                    }
                }
                

                // Уменьшаем запасы на складе 
                if(
                    $sPrefix!='Б' 
                    // Если не парковка или успешная парковка
                    && (
                        $sParkingStatus == 0
                        ||
                        $sParkingStatus == 1
                        ||
                        $sInfotechStatus == 0
                        ||
                        $sInfotechStatus == 1
                    )
                )
                foreach($basketProducts as $productId=>$item){
                    orderStorageChange( $productId, $arStore["ID"],
                    -$item['count']);
                }
               
                // Прописываем дату истечения бронирования
                if(
                    isset($arDocument["ДатаИстеченияБронирования"])
                    &&
                    $arDocument["ДатаИстеченияБронирования"]
                ){
                    $arDocument["ДатаИстеченияБронирования"] = str_replace(
                        "T"," ",
                        $arDocument["ДатаИстеченияБронирования"]
                    );
                    $arDocument["ДатаИстеченияБронирования"] = str_replace(
                        "Т"," ",
                        $arDocument["ДатаИстеченияБронирования"]
                    );
                    $tmp = date_parse($arDocument["ДатаИстеченияБронирования"]);
                    $sDateClose = 
                        sprintf("%04d",$tmp["year"])
                        ."-".sprintf("%02d",$tmp["month"])
                        ."-".sprintf("%02d",$tmp["day"])
                    ;
                    orderPropertiesUpdate($orderId, IMPORT_DEBUG,
                        'CLOSE_DATE',$sDateClose
                    );
                }

                // Прописываем промокоды, если есть 
                if(isset($GLOBALS["promocodes"])){
                    $sPromos = '';
                    if(
                        isset($GLOBALS["promocodes"]["ИмяПараметра1"])
                        &&
                        $GLOBALS["promocodes"]["ИмяПараметра1"]
                    )$sPromos .= $GLOBALS["promocodes"]["ИмяПараметра1"]
                        .":".$GLOBALS["promocodes"]["ЗначениеПараметра1"]
                        ."\n";
                    if(
                        isset($GLOBALS["promocodes"]["ИмяПараметра2"])
                        &&
                        $GLOBALS["promocodes"]["ИмяПараметра2"]
                    )$sPromos .= $GLOBALS["promocodes"]["ИмяПараметра2"]
                        .":".$GLOBALS["promocodes"]["ЗначениеПараметра2"]
                        ."\n";
                    orderPropertiesUpdate($orderId, IMPORT_DEBUG,
                        'PROMOCODES',$sPromos
                    );
                }

                // Ставим статус заказ и отправляем соответствующее ЗНИ
                ///// Ставим в очередь на ЗНИ
                // Если парковка успешно заказалась - Выполнен
                if($sParkingStatus == 1)
                    orderSetZNI($orderId,'F','AA');
                // Если парковка провалилась - Аннулирован
                elseif($sParkingStatus == 2)
                    orderSetZNI($orderId,'AF','AA');

                // Если инфотех успешно заказалась - Выполнен
                if($sInfotechStatus == 1)
                    orderSetZNI($orderId,'F','AA');
                // Если инфотех провалилась - Аннулирован
                elseif($sInfotechStatus == 2)
                    orderSetZNI($orderId,'AF','AA');


                require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/indexes.lib.php");
                if($orderId){
                    syncOrder($orderId);
                }
                
                /*
                Без уведомления пользователей (потом прикрутим)
                eventOrderStatusSendEmail(
                    $orderId, $statusId, ($arFields = array(
                        "SUPPORT_COMMENT"=>$sSupportComment
                    )), $statusId
                );
                */
                
                // Снимаем баллы
                if($sPrefix=="О" && $arOrder["SUM_PAID"]>0){
                    $objSSAGAccount = new \SSAG\CSSAGAccount('',$userId);
                    if(!$objSSAGAccount->transaction(
                       - $arOrder["SUM_PAID"],
                        "Заказ ".$arOrder["ADDITIONAL_INFO"]
                            ." в магазине поощрений АГ"
                    )){
                        orderSetZNI($orderId,'AF','AA');
//                        $bSendEmail = false;
                    }
                    $bSendEmail = false;
                }
                elseif($sPrefix=="О"){
                    $bSendEmail = false;
                }

                if($bSendEmail)eventOrderStatusSendEmail(
                    $orderId, $statusId, ($arFields = array(
                        "SUPPORT_COMMENT"=>$sSupportComment
                    )), $statusId
                );
                // Ставим дату выполнения
                if($statusId=='F')orderPropertiesUpdate($orderId,IMPORT_DEBUG,
                    "SHIPDATE",date("Y-m-d H:i:s")
                );
            }
            elseif($existsOrder){
                $orderId = $existsOrder["ID"];
                //echo "Update order_id = $orderId ";

                // Прописываем дату истечения бронирования
                if(
                    isset($arDocument["ДатаИстеченияБронирования"])
                    &&
                    $arDocument["ДатаИстеченияБронирования"]
                ){
                    $arDocument["ДатаИстеченияБронирования"] = str_replace(
                        "T"," ",
                        $arDocument["ДатаИстеченияБронирования"]
                    );
                    $arDocument["ДатаИстеченияБронирования"] = str_replace(
                        "Т"," ",
                        $arDocument["ДатаИстеченияБронирования"]
                    );
                    $tmp = date_parse($arDocument["ДатаИстеченияБронирования"]);
                    $sDateClose = 
                        sprintf("%04d",$tmp["year"])
                        ."-".sprintf("%02d",$tmp["month"])
                        ."-".sprintf("%02d",$tmp["day"])
                    ;
                    orderPropertiesUpdate($orderId, IMPORT_DEBUG,
                        'CLOSE_DATE',$sDateClose
                    );
                }
                // Прописываем промокоды, если есть 
                if(isset($GLOBALS["promocodes"]))
                    $sPromos = '';
                    if(
                        isset($GLOBALS["promocodes"]["ИмяПараметра1"])
                        &&
                        $GLOBALS["promocodes"]["ИмяПараметра1"]
                    )$sPromos .= $GLOBALS["promocodes"]["ИмяПараметра1"]
                        .":".$GLOBALS["promocodes"]["ЗначениеПараметра1"]
                        ."\n";
                    if(
                        isset($GLOBALS["promocodes"]["ИмяПараметра2"])
                        &&
                        $GLOBALS["promocodes"]["ИмяПараметра2"]
                    )$sPromos .= $GLOBALS["promocodes"]["ИмяПараметра2"]
                        .":".$GLOBALS["promocodes"]["ЗначениеПараметра2"]
                        ."\n";
                    orderPropertiesUpdate($orderId, IMPORT_DEBUG,
                        'PROMOCODES',$sPromos
                    );

                // Обрабатываем все статусы кроме отмены
                if($existsOrder["STATUS_ID"]!=$statusId && $statusId!='AG'){
                    CSaleOrder::Update($orderId, $arOrder);
                    // Меняем статус
                    CSaleOrder::StatusOrder($orderId, $statusId);
                    orderSetZNI($orderId,'',$existsOrder["STATUS_ID"]);
                    orderPropertiesUpdate($orderId,IMPORT_DEBUG);
                   
                    
                    // Если заказ с интеграцией в Инфотех - отправляем билеты
                    $arOrderProperties = orderGetProperties($orderId);
                    $nInfotechOrderId =
                        isset($arOrderProperties["INFOTECH_ORDER_ID"]["VALUE"])
                        ?
                        intval($arOrderProperties["INFOTECH_ORDER_ID"]["VALUE"])
                        :
                        0
                        ;
                    if($nInfotechOrderId){
                        $objInfotech = new \Integration\CIntegrationInfotech(
                            str_replace("u","",$userData["LOGIN"]),
                            $arOrder["ADDITIONAL_INFO"]    
                        );
                        $objInfotech->sendTickets($nInfotechOrderId);
                    }

                    if($bSendEmail && $statusId!='AF')eventOrderStatusSendEmail(
                        $orderId, $statusId, ($arFields = array(
                            "SUPPORT_COMMENT"=>$sSupportComment
                        )), $statusId
                    );



                    // Ставим дату выполнения
                    // для заказа в статусе "В работе"(готово) - не нужно
                    // для него уже в АРМ поставили
                    if($statusId=='F' && $existsOrder["STATUS_ID"]!='N')
                        orderPropertiesUpdate($orderId,IMPORT_DEBUG,
                            "SHIPDATE",date("Y-m-d H:i:s")
                        );
                }
                // При пришедшем статусе "В работе" и "Выполнен" письма
                // отправляем в любом случае при обратном толчке
                // Когла статус не меняется
                elseif(
                    $existsOrder["STATUS_ID"]==$statusId && (
                        $statusId=='N' 
                        || $statusId=='F' 
                        || $statusId=='AI'
                        || $statusId=='AG'
                        || $statusId=='AF'
                    )
                ){
                    /*
                    Утратило силу 29.10.2017 в связи с переход на изменение статуса
                    обратным толчком, но пусть будет для совместимости и прямых
                    толчков из 1С
                    */
                    orderPropertiesUpdate($orderId,IMPORT_DEBUG);
                    if($bSendEmail && $statusId!='AF')eventOrderStatusSendEmail(
                        $orderId, $statusId, ($arFields = array(
                            "SUPPORT_COMMENT"=>$sSupportComment
                        )), $statusId
                    );
                }
                // Обрабатываем отмену
                elseif(
                    $existsOrder["STATUS_ID"]!=$statusId 
                    && $statusId=='AG'
                ){
                    CSaleOrder::Update($orderId, $arOrder);
   
                    // Считаем сумму заказа
                    $orderSum = $arOrder["SUM_PAID"];

                    // Отменяем оплату и возвращаем баллы только если заказ сделан из битрикса
                    $moneyBack = false;

                    if(
                        preg_match("#^.*\-\d+$$#", $existsOrder["ADDITIONAL_INFO"])
                        && $sPrefix!='М'
                    ){
                        $objSSAGAccount = new \SSAG\CSSAGAccount('',$userId);
                        if(!$objSSAGAccount->transaction(
                            $orderSum,
                            "Отмена заказа ".$existsOrder["ADDITIONAL_INFO"]
                                ." в магазине поощрений АГ"
                        )){
                            echo $arOrder["ADDITIONAL_INFO"]
                                .": points transaction error: ".$obOrder->error." ";
                        }
                        $moneyBack = true;
                    }
                    //CSaleOrder::PayOrder($existsOrder["ID"],"N",true,false);
                    CSaleOrder::StatusOrder($existsOrder["ID"], $statusId);
                    orderSetZNI($orderId,'',$existsOrder["STATUS_ID"]);
                    if(!CSaleOrder::CancelOrder($existsOrder["ID"],"Y","Передумал")){
                        $answer["error"] .= "Заказ не был отменён.";
                    }
                    orderPropertiesUpdate($orderId,IMPORT_DEBUG);
                    if($bSendEmail)eventOrderStatusSendEmail(
                        $orderId, $statusId, ($arFields = array(
                            "SUPPORT_COMMENT"=>$sSupportComment
                        )), $statusId
                    );

                    // Увеличикаем запасы на складе 
                    /*
                    $objCCatalogStoreProduct = new CCatalogStoreProduct;
                    $objCCatalogProduct = new CCatalogProduct;
                    foreach($basketProducts as $productId=>$item){
                        /// Получаем текущее значение этого товара сейчас на складе
                        $nQuantity = 0;
                        // Если записей с остатком нет - пропустить его уменьшение
                        if($arStoreProduct = $objCCatalogStoreProduct->GetList(
                            array(),
                            $arStoreProductFilter = array(
                                "PRODUCT_ID"=>  $productId,
                                "STORE_ID"  =>  $arStore["ID"]
                            ),
                            false,
                            array("nTopCount"=>1),
                            array("AMOUNT","ID","PRODUCT_ID","STORE_ID")
                        )->GetNext()){
                            $nQuantity = $arStoreProduct["AMOUNT"];
                        }
                        else{
                            continue;
                        }
                        // Высисляем новый остаток, при отреицательном - нуль
                        $nDQuantity = $nQuantity + $item["count"];

                        // Устанавливаем новое значение остатка
                        if(!$objCCatalogStoreProduct->Update(
                            $arStoreProduct["ID"],
                            $arF = array(
                                "AMOUNT"              =>  $nDQuantity
                        ))){
                            print_r($objCCatalogStoreProduct);
                            die;
                        }

                    }
                    */
                }

                // Конец обработки отмены

                
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
                // Заполняем свойсва заказа из свойст товара на случай
            }


            
            // Обновляем индексную таблицу
            require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/indexes.lib.php");
            if($orderId){
                syncOrder($orderId);
                syncUser($userId);
            }


            // При выполнении заказа прописываем в дату статуса дату выполнения
            if($statusId=='F')$DB->Query("
                UPDATE 
                    `b_sale_order` 
                SET 
                    `DATE_STATUS`='".
                        $sDateStatus."'
                WHERE
                    `ID`='".$orderId."'
                LIMIT
                    1
                ");
            $nOrderCounter++;
        }
        
    }
    
    if($nOrderCounter)
        echo "success";
    else    
        echo "failed: orders.xml not contains valid orders. Some errors were occured.";

    // Снимаем блокировку
    orderOrderResetLock();


?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>


