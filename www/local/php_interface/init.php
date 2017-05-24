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

    //
    define("COMMON_CACHE_TIME",3600);

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
    // Определяем ID свойства ХОЧУ
    $arr = CIBlockProperty::GetList(
        array(), array(
            "IBLOCK_ID"=>CATALOG_IB_ID,
            "NAME"=>"Хочу"
        )
    )->GetNext();
    define("IWANT_PROPERTY_ID",$arr["ID"]);
    // Определяем ID свойства ИНТЕРЕСУЮСЬ 
    $arr = CIBlockProperty::GetList(
        array(), array(
            "IBLOCK_ID"=>CATALOG_IB_ID,
            "NAME"=>"Интересуюсь"
        )
    )->GetNext();
    define("INTEREST_PROPERTY_ID",$arr["ID"]);
      
     
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
        "sale", "OnOrderStatusSendEmail", "eventOrderStatusSendEmail"
    );
    //AddEventHandler(
    //    "sale", "OnSaleStatusOrder", "eventOrderStatusSendEmail"
    //);
    
    
    function eventOrderStatusSendEmail(
        $orderId, &$eventName, &$arFields, $orderStatus
    ){
        
        // Получаем информацию о заказе
        $orderInfo = initOrderGetInfo($orderId);
        
        // Зунуляем письма о заказах с опенкарта
        if(preg_match("#^\d+$#", $orderInfo["ORDER"]["ADDITIONAL_INFO"]))
            return true;
        
        $sMailText      =   '';
        $sMailAttach    =   '';
        $sTo            =   $orderInfo["USER"]["EMAIL"];
        $sBC            =   SHOP_EMAIL;
        $sFrom          =   SHOP_EMAIL;
        $sFilename      =   $orderInfo["ORDER"]["ADDITIONAL_INFO"].
            "-сертификат-АГ.html";

        $sSubject       =   "Магазин поощрений «Активный гражданин» заказ ".
            $orderInfo["ORDER"]["ADDITIONAL_INFO"];
        $sSubject       =   "=?UTF-8?B?".base64_encode($sSubject)."?=";
        
        $boundary = "--".md5(uniqid(rand().time()));
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

        $sMailAttachHeaders = "\r\n--$boundary\r\n"
            ."Content-Type: application/octet-stream; name=\"$sFilename\"\r\n"
            ."Content-Transfer-Encoding: base64\r\n"
            ."Content-Disposition: attachment; filename=\"$sFilename\"\r\n"
            ."\r\n"
        ;

        $sMailTextHeaders = "--$boundary\r\n"
            ."Content-Type: text/html; UTF-8\r\n"
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
        require(MAIL_TMPL_PATH."/_header.php");
        if(
            preg_match("#^\w+$#",$orderStatus)
            &&
            file_exists(
                MAIL_TMPL_PATH."/".$orderStatus."-".$orderInfo["TYPE"].".php"
            )
        )require(
            MAIL_TMPL_PATH."/".$orderStatus."-".$orderInfo["TYPE"].".php"
        );
        
        require(MAIL_TMPL_PATH."/_footer.php");

        $sMailText = $html;
       
        $sMailFunction = "mail";
        if(function_exists("custom_mail"))$sMailFunction = "custom_mail";

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
    }
    

    function eventSaleStatusEMail($orderId, $orderStatus){
    }

    /**
        Функция вызывается при создании заказа
    */
    function eventOrderNewSendEmail_normal($orderID, &$eventName, &$arFields){
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


