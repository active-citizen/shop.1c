<?php

    // Библиотаке для склонения баллов, и дней
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/rus.lib.php");
    // Подключение библиотеки почтового SMTP-клиента (закомментарить, если
    // понадобится системный SMTP-клиен). Не шустрого, но позволяющего писать
    // все логи в /upload/smtplog и все письма в /upload/maildir 
    include($_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/common.php");
    // Ключи токены и доступы
    include($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
    // Constants and settings
    include("settings.inc.php");
        
    CModule::IncludeModule("sale");
    CModule::IncludeModule("iblock");
   
    // А не мобильное ли ты приложение
    define("IS_MOBILE",
        isset($_COOKIE["EMPSESSION"])
    );

    define("CONFIG_STATIC",true);
    define("COMMON_CACHE_TIME",3600);
    // Значение невыбираемого остатка по умолчанию
    define("DEFAULT_STORE_LIMIT",0);
    // Определяем ID групп Операторы МФЦ и Партнёры
    define("PARTNERS_GROUP_ID",9);
    define("OPERATORS_GROUP_ID",10);


    if(CONFIG_STATIC){
        if(file_exists("config.inc.php")){
            require_once("config.inc.php");
        }
        else{
            require_once("config.inc.dist.php");
        }
    }
    else{
        //
        // Определяем ID инфоблока каталога
        $arr = CIBlock::GetList(array(),array("CODE"=>"clothes"))->GetNext();
        define("CATALOG_IB_ID",$arr["ID"]);

        // Определяем ID инфоблока предложений
        $arr = CIBlock::GetList(
            array(), array("CODE"=>"clothes_offers")
        )->GetNext();
        define("OFFER_IB_ID",$arr["ID"]);

        // Определяем ID инфоблока производителей
        $arr = CIBlock::GetList(array(),array("CODE"=>"manuacturers"))->GetNext();
        define("MANUFACTURER_IB_ID",$arr["ID"]);

        // Определяем ID инфоблока желаний
        $arr = CIBlock::GetList(array(),array("CODE"=>"whishes"))->GetNext();
        define("WISHES_IB_ID",$arr["ID"]);
        // Определяем ID свойства ХОЧУ
        $arr = CIBlockProperty::GetList(
            array(), array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "CODE"=>"WANTS"
            )
        )->Fetch();
        define("IWANT_PROPERTY_ID",$arr["ID"]);

        // Определяем ID свойства ИНТЕРЕСУЮСЬ 
        $arr = CIBlockProperty::GetList(
            array(), array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "CODE"=>"INTERESTS"
            )
        )->Fetch();
        define("INTEREST_PROPERTY_ID",$arr["ID"]);

        // Определяем ID свойства ссылка на товар каталога
        $arr = CIBlockProperty::GetList(
            array(), array(
                "IBLOCK_ID"=>OFFER_IB_ID,
                "CODE"=>"CML2_LINK"
            )
        )->Fetch();
        define("CML2_LINK_PROPERTY_ID",$arr["ID"]);
        // Определяем ID свойства прятать при исчерпании остатков
        $arr = CIBlockProperty::GetList(
            array(), array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "CODE"=>"HIDE_IF_ABSENT"
            )
        )->Fetch();
        define("HIDE_IF_ABSENT_PROPERTY_ID",$arr["ID"]);
        
        // Определяем ID групп Операторы МФЦ и Партнёры
        define("PARTNERS_GROUP_ID",9);
        define("OPERATORS_GROUP_ID",10);
        // Определяем ID инфоблока ХОЧУ
        $arr = CIBlock::GetList(array(),array("CODE"=>"iwant"))->GetNext();
        define("IWANT_IBLOCK_ID",$arr["ID"]);
        // Определяем ID инфоблока ИНТЕРЕСУЮСЬ
        $arr = CIBlock::GetList(array(),array("CODE"=>"interestme"))->GetNext();
        define("INTEREST_IBLOCK_ID",$arr["ID"]);
    }
    // Определяем ID флага свойства прятать при исчерпании
    $arr = CIBlockPropertyEnum::GetList(
        array(), array(
            "PROPERTY_ID"=>HIDE_IF_ABSENT_PROPERTY_ID,
            "VALUE"=>"да"
        )
    )->Fetch();
    define("YES_HIDE_FLAG_ID",$arr["ID"]);

    AddEventHandler("main", "OnBeforeProlog", "MyOnBeforePrologHandler", 50);


    // Если режим обмена заказами - глушим отправку письма при создании заказа
    if(ORDERS_EXCHANGE_ADMIN_MODE){
        AddEventHandler(
            "sale", "OnOrderNewSendEmail", "eventOrderNewSendEmail_dummy"
        );
    }
    else{
        AddEventHandler(
            "sale", "OnOrderNewSendEmail", "eventOrderNewSendEmail_normal"
        );
    }
    
    // Глушим отправку письма при оплате
    AddEventHandler("sale", "OnOrderPaySendEmail", "eventOrderPaySendEmail");
    // Глушим отправку письма при отмене
    AddEventHandler(
        "sale", "OnOrderCancelSendEmail", "eventOnOrderCancelSendEmail"
    );
    // Глушим отправку письма при доставке
    AddEventHandler(
        "sale", "OnOrderDeliverSendEmail", "eventOnOrderDeliverSendEmail"
    );
    // Глушим отправку письма напоминалку
    AddEventHandler(
        "sale", "OnOrderRemindSendEmail", "eventOrderRemindSendEmail"
    );
    // Эти две фишни тоже на всякий случай баним
    AddEventHandler(
        "sale", "OnOrderRecurringSendEmail", "eventOrderRecurringSendEmail"
    );
    AddEventHandler(
        "sale", "OnOrderRecurringCancelSendEmail", 
        "eventOrderRecurringCancelSendEmail"
    );
    
    
    // Назначаем обработчик формирования письма о смене статуса заказа
    AddEventHandler("sale", "OnSaleStatusEMail", "eventSaleStatusEMail");
    // Назначаем обработчик отправки письма о смене статуса заказа
    AddEventHandler(
        "sale", "OnOrderStatusSendEmail", "eventOrderStatusSendEmailNull"
    );
    //AddEventHandler(
    //    "sale", "OnSaleStatusOrder", "eventOrderStatusSendEmail"
    //);
    
   
    /**
        Функция, вызываемая при сохранении статуса заказа
    */
    function eventOrderStatusSendEmail(
        $orderId, &$eventName, &$arFields, $orderStatus
    ){
        
        // Получаем информацию о заказе
        $orderInfo = initOrderGetInfo($orderId);

        // Зунуляем письма о заказах с опенкарта
        if(preg_match("#^\d+$#", $orderInfo["ORDER"]["ADDITIONAL_INFO"]))
            return true;

        // Текст письма
        $sMailText      =   '';
        // Текст вложения
        $sMailAttach    =   '';
        $sTo            =   $orderInfo["USER"]["EMAIL"];
        $sBC            =   SHOP_EMAIL;
        $sFrom          =   SHOP_EMAIL;
        // Название файла-вложения
        $sFilename      =   $orderInfo["ORDER"]["ADDITIONAL_INFO"].
            "-сертификат-АГ.png";

        $sSubject       =   "Магазин поощрений «Активный гражданин» заказ ".
            $orderInfo["ORDER"]["ADDITIONAL_INFO"];
        $sSubject       =   "=?UTF-8?B?".base64_encode($sSubject)."?=";

        // Метка для разделения частей письма
        $boundary = "--".md5(uniqid(rand().time()));
        // Общие заголовки письма
        $sHeaders = ""
            ."FROM: "."=?UTF-8?B?"
            .base64_encode(
                "«Активный гражданин». Магазин бонусов"
            )
            ."?="."<".$sFrom.">\r\n"
            ."Reply-To: "."=?UTF-8?B?"
            .base64_encode(
                "«Активный гражданин». Магазин бонусов"
            )."?="."<".$sFrom.">\r\n"
            ."Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n";
        ;

        // Заголовки вложения письма
        $sMailAttachHeaders = "\r\n--$boundary\r\n"
//            ."Content-Type: application/octet-stream; name=\"$sFilename\"\r\n"
            ."Content-Type: image/png; name=\"$sFilename\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."Content-Disposition: attachment; filename=\"$sFilename\"\r\n"
            ."\r\n"
        ;

        // Заголовки основного текста письма 
        $sMailTextHeaders = "--$boundary\r\n"
            ."Content-Type: text/html; charset=UTF-8\r\n"
            //."Content-Transfer-Encoding: base64\r\n"
            ."\r\n"
        ;

        /*
        'AC'       // Брак
        'AG'       // Отмена
        'AI'       // Аннулировать
        'F'        // Выполнен
        'N'        // В работе
        */
        // Получаем в переменной $html шапку основного текта письма
        require(MAIL_TMPL_PATH."/_header.php");
        // Получаем в переменной $html шаблон письма, согласно коду статуса
        if(
            preg_match("#^\w+$#",$orderStatus)
            &&
            file_exists(
                MAIL_TMPL_PATH."/".$orderStatus."-".$orderInfo["TYPE"].".php"
            )
        )require(
            MAIL_TMPL_PATH."/".$orderStatus."-".$orderInfo["TYPE"].".php"
        );
        // Получаем в переменной $html подвал основного текста письма 
        require(MAIL_TMPL_PATH."/_footer.php");

        // Запоминаем текст основного письма
        $sMailText = $html;

        header("Content-type: text/plain; charset=utf-8;");
      
        // Определяем какую функцию для отправки почты использовать
        $sMailFunction = "mail";
        if(function_exists("custom_mail"))$sMailFunction = "custom_mail";
        
        // Отправляем письмо
        /*
        $sMailFunction(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                .$sMailText
                //.chunk_split(base64_encode($sMailText))
            //.$sMailAttachHeaders
            //    .chunk_split(base64_encode($sMailAttach))
            , 
            $sHeaders
        );
        */
        /**
            Если для заказа надо отправить сертификат
            И статус "в работе"
        */
        if(
            $orderInfo["SEND_CERT"]
            &&
            $orderStatus=='N'
        ){
            $_REQUEST["generate"] = 1;
            $_REQUEST["id"] = $orderId;
            require($_SERVER["DOCUMENT_ROOT"]."/profile/order/print.png.ajax.php");
            $sMailAttach = file_get_contents($sPngFile);
        }
//        echo chunk_split(base64_encode($sMailAttach));
//        die;

        $sMailFunction(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                //.chunk_split(base64_encode($sMailText))
                .$sMailText
                .(
                    $orderInfo["SEND_CERT"]
                    &&
                    $orderStatus=='N'
                    ?
                    $sMailAttachHeaders.chunk_split(
                        base64_encode($sMailAttach)
                    )
                    :
                    ""
                )
            , 
            $sHeaders
        );

    }
    

    function eventSaleStatusEMail($orderId, $orderStatus){
    }

    /**
        Функция вызывается при создании заказа
        раньше высылал сертификат, теперь просто для красоты
    */
    function eventOrderNewSendEmail_normal($orderID, &$eventName, &$arFields){
        return false;
        // Получаем информацию о заказе
        $orderInfo = initOrderGetInfo($orderID);

        // Зунуляем письма о заказах с опенкарта
        if(
            preg_match("#^\d+$#", $orderInfo["ORDER"]["ADDITIONAL_INFO"])
        ) return true;
        
        $orderInfo["ORDER"]["ADDITIONAL_INFO"] = 
            "Б-".$orderInfo["ORDER"]["ID"];

        $sMailText      =   '';
        $sMailAttach    =   '';
        $sTo            =   $orderInfo["USER"]["EMAIL"];
        $sBC            =   SHOP_EMAIL;
        $sFrom          =   SHOP_EMAIL;
        $sFilename      =   $orderInfo["ORDER"]["ADDITIONAL_INFO"]
            ."-сертификат-АГ.html";

        $sSubject       =   "Магазин поощрений «Активный гражданин» заказ "
            .$orderInfo["ORDER"]["ADDITIONAL_INFO"];
        $sSubject       =   "=?UTF-8?B?".base64_encode($sSubject)."?=";
        
        $boundary = "--".md5(uniqid(rand().time()));
        $sHeaders = ""
            ."FROM: "."=?UTF-8?B?"
            .base64_encode("«Активный гражданин». Магазин бонусов")
            ."?="."<".$sFrom
            .">\r\n"
            ."Reply-To: "."=?UTF-8?B?".
            base64_encode("«Активный гражданин». Магазин бонусов")
            ."?="."<".$sFrom.">\r\n"
            ."Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n";
        ;

        $sMailAttachHeaders = "\r\n--$boundary\r\n"
            ."Content-Type: text/html; UTF-8\r\n"
            ."Content-Type: application/octet-stream; name=\"$sFilename\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."Content-Disposition: attachment; filename=\"$sFilename\"\r\n"
            ."\r\n"
        ;

        $sMailTextHeaders = "--$boundary\r\n"
            ."Content-Type: text/html; UTF-8\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."\r\n"
        ;

        require(MAIL_TMPL_PATH."/_header.php");
        require(MAIL_TMPL_PATH."/N-".$orderInfo["TYPE"].".php");
        require(MAIL_TMPL_PATH."/_footer.php");
        
        $sMailText = $html;

        /**
            Если для заказа надо отправить сертификат
        */
        if($orderInfo["SEND_CERT"]){
            require($_SERVER["DOCUMENT_ROOT"]."/profile/order/print.ajax.php");
            $sMailAttach = $sHTML;
        }
        
        $sMailFunction = "mail";
        if(function_exists("custom_mail"))$sMailFunction = "custom_mail";

        $sMailFunction(
            $sTo, 
            $sSubject, 
            $sMailTextHeaders
                .chunk_split(base64_encode($sMailText))
                //.$sMailText
                .(
                    $orderInfo["SEND_CERT"]
                    ?
                    $sMailAttachHeaders.chunk_split(
                        base64_encode($sMailAttach)
                    )
                    :
                    ""
                )
            , 
            $sHeaders
        );
        
    }

    // Функции пустышки для предотвращения отправки писем при собятиях
    function eventOrderNewSendEmail_dummy($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderPaySendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOnOrderCancelSendEmail($orderID, &$eventName, &$arFields)
        {return false; }
    function eventOnOrderDeliverSendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderRemindSendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderRecurringCancelSendEmail(
        $orderID, &$eventName, &$arFields
    ){return false;}
    function eventOrderRecurringSendEmail($orderID, &$eventName, &$arFields)
        {return false;}
    function eventOrderStatusSendEmailNull( $orderId, &$eventName, &$arFields, $orderStatus
    ){return false;}

 
    
    /**
     * Получаем информацию о заказе
    */
    function initOrderGetInfo($orderID){
        global $USER;
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

        $arOrder = CSaleOrder::GetList(
            array(), 
            array("ID"=>$orderID),
            false,
            array("nTopCount"=>1)
        )->GetNext();
        $arUser = CUser::GetByID($arOrder["USER_ID"])->GetNext();

        // Переформатирование даты
        $tmp = date_parse($arOrder["DATE_INSERT"]);
        $arOrder["DATE_INSERT"] 
            = sprintf("%02d",$tmp["day"])
            .".".sprintf("%02d",$tmp["month"])
            .".".$tmp["year"];

        /**
            Костыль для корректного выбора товара при заказе на сайте и при
            обмене из 1С. 
            
            Суть в том, что в момент заказа на сайте ID заказа 
            при вызове события отправки письма ещё не известен, и приходится
            опираться на FUSER

            А при обмене - обмен идёт от пользователя, который этого не
            заказывал, но известен номер заказа, поэтому опора на ORDER_ID
        */
        if(
            $_SERVER["SCRIPT_NAME"]=='/profile/order/print.ajax.php'
        ){
            $arFilter = array(
                "ORDER_ID" => $arOrder["ID"],
                "LID"=>SITE_ID
            );
        }
        elseif(
            !$USER->isAdmin() 
        ){
            $arFilter = array(
                "FUSER_ID"=>CSaleBasket::GetBasketUserID(),
                "LID"=>SITE_ID,
                "ORDER_ID"=>"NULL"
            );
        }
        else{
            $arFilter = array(
                "ORDER_ID" => $arOrder["ID"],
                "LID"=>SITE_ID
            );
        }
 
        $arBasket = CSaleBasket::GetList(
            array(),
            $arFilter,
            false,
            array("nTopCount"=>1)
        )->GetNext();
        
        $arOffer = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arBasket["PRODUCT_ID"],
                "IBLOCK_ID" =>  OFFER_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array("ID","PROPERTY_CML2_LINK")
        )->GetNext();
        
        $arCatalog = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"],
                "IBLOCK_ID" =>  CATALOG_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array()
        )->GetNext();

        $arCatalogManufacturer = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"],
                "IBLOCK_ID" =>  CATALOG_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array("PROPERTY_MANUFACTURER_LINK")
        )->GetNext();
        
        $arProperties = array();
        $resProperties = CIBlockElement::GetProperty(
            CATALOG_IB_ID, $arCatalog["ID"]
        );
        while($ar = $resProperties->GetNext())$arProperties[$ar["CODE"]] = $ar;
        
        $arStatuses = array();
        $resStatus = CSaleStatus::GetList();
        while($ar = $resStatus->GetNext())$arStatuses[$ar["ID"]] = $ar;
        
        $arManufacturer = CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arCatalogManufacturer[
                    "PROPERTY_MANUFACTURER_LINK_VALUE"
                ],
                "IBLOCK_ID" =>  MANUFACTURER_IB_ID
            ),
            false,
            array("nTopCount"=>1),            
            array()
        )->GetNext();
        $arManufactProps = array();
        $resManufactProps = CIBlockElement::GetProperty(
            MANUFACTURER_IB_ID, $arManufacturer["ID"]
        );
        while( $ar = $resManufactProps->GetNext())
            $arManufactProps[$ar["CODE"]] = $ar;
        
        $tmp = date_parse($arProperties["USE_BEFORE_DATE"]["VALUE"]);
        $date1 = date(
            "d.m.Y",
            $ts1 = mktime(0,0,0,$tmp["month"],$tmp["day"],$tmp["year"])
        );
        $ts2 = time()+$arProperties["DAYS_TO_EXPIRE"]["VALUE"]*24*60*60;
        $date2 = date("d.m.Y",$ts2);
        if(trim($arProperties["USE_BEFORE_DATE"]["VALUE"]) && $ts1<$ts2){
            $arCatalog["EXPIRES"] = $date1;
        }
        elseif(trim($arProperties["USE_BEFORE_DATE"]["VALUE"]) && $ts1>=$ts2){
            $arCatalog["EXPIRES"] = $date2;
        }
        else{
            $arCatalog["EXPIRES"] = $date2;
        }

        $arOrderProperties = orderGetProperties($orderID);

       
        $send_cert = (
                (
                    isset($arProperties["SEND_CERT"]["VALUE"])
                    &&
                    $arProperties["SEND_CERT"]["VALUE"]
                )
                ?
                true
                :
                false
            );
        
        $type = "m";
        if($send_cert)$type = "v";
        
        return array(
            "USER"          =>  $arUser,
            "ORDER"         =>  $arOrder,
            "BASKET"        =>  $arBasket,
            "OFFER"         =>  $arOffer,
            "CATALOG"       =>  $arCatalog,
            "PROPERTIES"    =>  $arProperties,
            "STATUSES"      =>  $arStatuses,
            "MANUFACTURER"  =>  $arManufacturer,
            "MANUFACT_PROPS"=>  $arManufactProps,
            // M - материальное поощрение
            // V - виртуальное поощрение
            // R - поощрение-ресурс
            "TYPE"          =>  $type,
            "SEND_CERT"     =>  $send_cert,
            "ORDER_PROPERTIES"    =>  $arOrderProperties,
            "AR_FILTER"     => $arFilter,
            "AR_BASKET"     =>  $arBasket
        );
    }


    function MyOnBeforePrologHandler()
    {
        global $USER;
        // Предзагрузочная авторизация для мобил
        if(
            IS_MOBILE 
            &&
            (
                preg_match("#^/catalog/(.*/)?$#",$_SERVER["REQUEST_URI"])
                ||
                preg_match("#^/catalog/$#",$_SERVER["REQUEST_URI"])
                ||
                preg_match("#^/profile/#",$_SERVER["REQUEST_URI"])
            )
        ){
            $_REQUEST["session_id"] = $_COOKIE["EMPSESSION"];
            require_once(
                $_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php"
            );
            $oUser = new bxUser();
            if(!is_object($USER))$USER = new CUser;
            $arAuthAnswer = $oUser->authUserCross();
        }

    }

