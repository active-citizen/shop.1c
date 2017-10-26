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
        //true
        isset($_COOKIE["EMPSESSION"])
    );

    define("CONFIG_STATIC",true);
    define("COMMON_CACHE_TIME",3600);
    // Значение невыбираемого остатка по умолчанию
    define("DEFAULT_STORE_LIMIT",0);
    // Определяем ID групп Операторы МФЦ и Партнёры
    define("PARTNERS_GROUP_ID",9);
    define("OPERATORS_GROUP_ID",10);
    define("SHOP_ADMIN",7);


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

        // Определяем ID свойства ЦЕНА
        $arr = CIBlockProperty::GetList(
            array(), array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "CODE"=>"MINIMUM_PRICE"
            )
        )->Fetch();
        define("PRICE_PROPERTY_ID",$arr["ID"]);

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
        // Определяем ID свойства производитель 
        $arr = CIBlockProperty::GetList(
            array(), array(
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "CODE"=>"MANUFACTURER_LINK"
            )
        )->Fetch();
        define("MANUFACTURER_PROPERTY_ID",$arr["ID"]);
        
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
    

    require_once("mail.lib.php");

    require_once("points.lib.php");
    /*
    register_shutdown_function('pointsAlreadyUpdate'); 
    */


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

