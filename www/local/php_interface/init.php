<?php
    define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX

    // Класс для отладочного вывода
    require_once(
        $_SERVER["DOCUMENT_ROOT"]
            ."/local/libs/classes/CAGShop/xprint.class.php"
    );
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
    include(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCache/CCache.class.php"
    );
    use AGShop\Cache as Cache;


        
    CModule::IncludeModule("sale");
    CModule::IncludeModule("iblock");

    // А не мобильное ли ты приложение
    define("IS_MOBILE",
        //true
        isset($_COOKIE["EMPSESSION"])
    );
    
    define("IS_PHONE",
        preg_match("#(phone|mobile)#i", $_SERVER["HTTP_USER_AGENT"])
    );
    
    
    define("CONFIG_STATIC",false);
    define("COMMON_CACHE_TIME",300);
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
        define("CATALOG_IB_ID",getIBIDByCode("clothes"));
        // Определяем ID инфоблока предложений
        define("OFFER_IB_ID",getIBIDByCode("clothes_offers"));
        // Определяем ID инфоблока производителей
        define("MANUFACTURER_IB_ID",getIBIDByCode("manuacturers"));
        // Определяем ID инфоблока производителей
        define("USERSCATS_IB_ID",getIBIDByCode("userscat"));
        // Определяем ID инфоблока желаний
        define("WISHES_IB_ID",getIBIDByCode("whishes"));
        // Определяем ID инфоблока ХОЧУ
        define("IWANT_IBLOCK_ID",getIBIDByCode("iwant"));
        // Определяем ID инфоблока ИНТЕРЕСУЮСЬ
        define("INTEREST_IBLOCK_ID",getIBIDByCode("interestme"));


        // Определяем ID свойства ХОЧУ
        define("IWANT_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID, "WANTS"));
        // Определяем ID свойства ИНТЕРЕСУЮСЬ 
        define("INTEREST_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID, "INTERESTS"));
        // Определяем ID свойства ЦЕНА
        define("PRICE_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID, "MINIMUM_PRICE"));
        // Определяем ID свойства ссылка на товар каталога
        define("CML2_LINK_PROPERTY_ID",getPropIDByCode(OFFER_IB_ID, "CML2_LINK"));
        // Определяем ID свойства прятать при исчерпании остатков
        define("HIDE_IF_ABSENT_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID, "HIDE_IF_ABSENT"));
        // Определяем ID свойства производитель 
        define("MANUFACTURER_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID, "MANUFACTURER_LINK"));
        // Определяем ID свойства дата скрытия 
        define("HIDE_DATE_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"HIDE_DATE"));
        // Определяем ID свойства МЕСЯЧНЫЙ ЛИМИТ
        define("MON_LIMIT_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"MON_LIMIT"));
        // Определяем ID свойства Артикул
        define("ARTNUMBER_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"ARTNUMBER"));
        // Определяем ID свойства дозволяемые группы пользователей
        define("USERSCATS_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"USERSCATS"));
        // Определяем ID свойства ХИТ
        define("SALELEADER_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"SALELEADER"));
        // Определяем ID свойства НОВИНКА
        define("NEWPRODUCT_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"NEWPRODUCT"));
        // Определяем ID свойства АКЦИЯ
        define("SPECIALOFFER_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"SPECIALOFFER"));
        // Определяем ID свойства Дата начала аукциона
        define("AUCTION_START_DATE_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"AUCTION_START_DATE"));
        // Определяем ID свойства Дата конца аукциона
        define("AUCTION_START_DATE_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"AUCTION_END_DATE"));
        // Определяем ID свойства пожелавшию пользователь
        define("WISH_USER_PROPERTY_ID",getPropIDByCode(WISHES_IB_ID,"WISH_USER"));
        // Определяем ID свойства желаемый товар
        define("WISH_PRODUCT_PROPERTY_ID",getPropIDByCode(WISHES_IB_ID,"WISH_PRODUCT"));
        // Определяем ID свойства дополнительные изображения предложения
        define("MORE_PHOTO_PROPERTY_ID",getPropIDByCode(OFFER_IB_ID,"MORE_PHOTO"));
        // Определяем ID свойства дополнительные изображения предложения
        define("MORE_PHOTO_PRODUCT_PROPERTY_ID",getPropIDByCode(CATALOG_IB_ID,"MORE_PHOTO"));

        // Определяем ID групп Операторы МФЦ и Партнёры
        define("PARTNERS_GROUP_ID",9);
        define("OPERATORS_GROUP_ID",10);
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
    
    /**
        Определение ID инфоблока по его коду
        @param $sCode - код инфоблока
        @return ID инфоблока
    */
    function getIBIDByCode($sCode){
        $objCache = new \Cache\CCache("getIBIDByCode",$sCode,COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get())return $sCacheData;

        $arr = CIBlock::GetList([],["CODE"=>$sCode])->Fetch();        
        return $objCache->set($arr["ID"]);
    }
    
    /**
        Определение ID инфоблока по его коду
        @param $nIblockId - ID инфоблока
        @param $sCode - код свойства
        @return ID свойства
    */
    function getPropIDByCode($nIblockId, $sCode){
        $objCache = new \Cache\CCache("getPropIDByCode",$nIblockId.$sCode,COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get())return $sCacheData;
        
        $arr = CIBlockProperty::GetList(
            array(), array(
                "IBLOCK_ID"=>$nIblockId,
                "CODE"=>$sCode
            )
        )->Fetch();
        return $objCache->set($arr["ID"]);
    }

