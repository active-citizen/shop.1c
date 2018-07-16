<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/COrder/COrderStatus.class.php"
);

require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/COrder/COrder.class.php"
);

require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCatalog/CCatalogStore.class.php"
);
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCatalog/CCatalogOffer.class.php"
);
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CDB/CDB.class.php"
);
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/rus.lib.php"
);
/*
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
*/
require_once(
$_SERVER["DOCUMENT_ROOT"]
."/local/libs/classes/CAGShop/CSync/CSync.class.php"
);


use AGShop\Order as Order;
use AGShop\Catalog as Catalog;
use AGShop\Sync as Sync;

if(!$USER->IsAdmin())die;


$arResult["ORDER"] = [];
if(isset($_REQUEST["STATUS_ID"]) && $_REQUEST["STATUS_ID"])
    $arResult["ORDER"]["STATUS_ID"] = htmlspecialchars(
        $_REQUEST["STATUS_ID"]
    );
if(isset($_REQUEST["STORE_ID"]) && $_REQUEST["STORE_ID"])
    $arResult["ORDER"]["STORE_ID"] = intval( $_REQUEST["STORE_ID"]);

if(isset($_REQUEST["PHONE"]) && $_REQUEST["PHONE"])
    $arResult["ORDER"]["PHONE"] = htmlspecialchars(
        $_REQUEST["PHONE"]
    );

if(isset($_REQUEST["OFFER_NAME"]) && $_REQUEST["OFFER_NAME"])
    $arResult["ORDER"]["OFFER_NAME"] = htmlspecialchars(
        $_REQUEST["OFFER_NAME"]
    );

if(isset($_REQUEST["AMOUNT"]) && $_REQUEST["AMOUNT"])
   $arResult["ORDER"]["AMOUNT"] = intval( $_REQUEST["AMOUNT"]);

if(isset($_REQUEST["PRICE"]) && $_REQUEST["PRICE"])
    $arResult["ORDER"]["PRICE"] = intval( $_REQUEST["PRICE"]);

if(
    isset($_REQUEST["DATE_ADD"]) 
    && $_REQUEST["DATE_ADD"]
)
    $arResult["ORDER"]["DATE_ADD"] = $_REQUEST["DATE_ADD"];

if(isset($_REQUEST["TROYKA"]))
    $arResult["ORDER"]["TROYKA"] = ( $_REQUEST["TROYKA"]);

if(isset($_REQUEST["TROYKA_TRANSACT"]) && $_REQUEST["TROYKA_TRANSACT"])
    $arResult["ORDER"]["TROYKA_TRANSACT"] = ( 
        $_REQUEST["TROYKA_TRANSACT"]
    );

if(isset($_REQUEST["PARKING_TRANSACT"]) && $_REQUEST["PARKING_TRANSACT"])
    $arResult["ORDER"]["PARKING_TRANSACT"] = ( 
        $_REQUEST["PARKING_TRANSACT"]
    );

////////////////////////// Обработка ошибок ////////////////////////
$arResult["ERROR"] = '';
$arUser = [];
if($_POST && !$arResult["ORDER"]["PHONE"]){
    $arResult["ERROR"] = 'Укажите номер телефона';
}
elseif($_POST){
    $arUser = \CUser::GetByLogin(
        'u'.$arResult["ORDER"]["PHONE"]
    )->Fetch();
}

if($_POST && !$arResult["ERROR"] && !$arUser){
    $arResult["ERROR"] = 'Не существует пользователя с номером телефона '
        ."&laquo;".$arResult["ORDER"]["PHONE"]."&raquo;";
}


$arOffer = [];
if($_POST && !$arResult["ERROR"] && !$arResult["ORDER"]["OFFER_NAME"]){
    $arResult["ERROR"] = 'Укажите название поощрения';
}
elseif($_POST && !$arResult["ERROR"]){
    $arOffer =\CIBlockElement::GetList([],[
        "IBLOCK_ID" =>  OFFER_IB_ID,
        "NAME"     =>  $arResult["ORDER"]["OFFER_NAME"],
        "ACTIVE"    => "Y"
    ],false,["nTopCount"=>1],["ID"]
    )->Fetch();
}
    
if($_POST && !$arResult["ERROR"] && !$arOffer){
    $arResult["ERROR"] = 'Не существует товара с названием '
        ."&laquo;".$arResult["ORDER"]["OFFER_NAME"]."&raquo;";
}
elseif($_POST && !$arResult["ERROR"]){
    $objOffer = new \Catalog\CCatalogOffer;
    $arOffer = $objOffer->getById($arOffer["ID"]);
}

$arStore = [];
if($_POST && !$arResult["ERROR"] && !$arResult["ORDER"]["STORE_ID"]){
    $arResult["ERROR"] = 'Укажите склад выдачи поощрения';
}
elseif($_POST && !$arResult["ERROR"]){
   $objStore = new \Catalog\CCatalogStore;
   $arStore = $objStore->getById($arResult["ORDER"]["STORE_ID"]);
}
 
$arStatus = [];
if($_POST && !$arResult["ERROR"] && !$arResult["ORDER"]["STATUS_ID"]){
    $arResult["ERROR"] = 'Укажите ЗНИ';
}
elseif($_POST && !$arResult["ERROR"]){
   $objStatus = new \Order\COrderStatus;
   $objStatus->fetch("ID", $arResult["ORDER"]["STATUS_ID"]);
   $arStatus = $objStatus->get();
}
 
if($_POST && !$arResult["ERROR"] && !$arStatus){
    $arResult["ERROR"] = 'Не существует статуса с ID '
        ."&laquo;".$arResult["ORDER"]["STATUS_ID"]."&raquo;";
}

if($_POST && !$arResult["ERROR"] && !$arStore){
    $arResult["ERROR"] = 'Не существует склада с ID '
        ."&laquo;".$arResult["ORDER"]["STORE_ID"]."&raquo;";
}

if($_POST && !$arResult["ERROR"] && !$arResult["ORDER"]["AMOUNT"]){
    $arResult["ERROR"] = 'Укажите количество';
}

if($_POST && !$arResult["ERROR"] && !$arResult["ORDER"]["PRICE"]){
    $arResult["ORDER"]["PRICE"] = 
        $arOffer["PRODUCT_PROPERTIES"]["MINIMUM_PRICE"];
}

if(
    $_POST 
    && !$arResult["ERROR"] 
    && !preg_match(
        "#^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$#",
        $arResult["ORDER"]["DATE_ADD"]
    )
){
    $arResult["ERROR"] = 
    'Укажите корректную дату добавления в формате <b>ДД.ММ.ГГГГ ЧЧ:МИ:СС</b>';
}

$CDB = new \DB\CDB;
$arTroykaLink = [];
if($_POST && !$arResult["ERROR"] 
    && $arResult["ORDER"]["OFFER_NAME"] 
    && strpos($arResult["ORDER"]["OFFER_NAME"],"Тройка")!==false
    &&
    !$arTroykaLink = $CDB->searchOne(
        "int_troika_link",
        ["cardnum"=>$arResult["ORDER"]["TROYKA"]]
    )
){
    $arResult["ERROR"] = "Нет привязанных карт с номером "
        ."&laquo;".$arResult["ORDER"]["TROYKA"]."&raquo;";
}

if($_POST && !$arResult["ERROR"] 
    && $arResult["ORDER"]["OFFER_NAME"] 
    && mb_strpos($arResult["ORDER"]["OFFER_NAME"],"Тройка")!==false
    && !preg_match("#^\d+$#",$arResult["ORDER"]["TROYKA_TRANSACT"])
){
    $arResult["ERROR"] = "Некорректный номер транзакции тройки "
        ."&laquo;".$arResult["ORDER"]["TROYKA_TRANSACT"]."&raquo;";
}

if($_POST && !$arResult["ERROR"] 
    && $arResult["ORDER"]["OFFER_NAME"] 
    && mb_strpos($arResult["ORDER"]["OFFER_NAME"],"Парков")!==false
    && !preg_match("#^[\d\w]+$#",$arResult["ORDER"]["PARKING_TRANSACT"])
){
    $arResult["ERROR"] = "Некорректный номер транзакции парковки "
        ."&laquo;".$arResult["ORDER"]["PARKING_TRANSACT"]."&raquo;";
}

if($_POST && !$arResult["ERROR"]){
    $objOrder = new \Order\COrder;
    $nOrderId = $objOrder->createFromAuction(
        $arUser["ID"],
        $arOffer["MAIN"]["ID"],
        $arStore["ID"],
        $arResult["ORDER"]["PRICE"],
        $arResult["ORDER"]["AMOUNT"],
        [
            "ZNI"=>$arResult["ORDER"]["STATUS_ID"],
            "PREFIX"=>"Б-",
            "DATE_ADD"=>$arResult["ORDER"]["DATE_ADD"]
        ]
    );
    if($nOrderId){
        if($arResult["ORDER"]["TROYKA_TRANSACT"])
            $objOrder->saveProperty(
                "TROIKA_TRANSACT_ID", 
                $arResult["ORDER"]["TROYKA_TRANSACT"]
            );
        if($arResult["ORDER"]["TROYKA"])
            $objOrder->saveProperty(
                "TROIKA", 
                $arResult["ORDER"]["TROYKA"]
            );
        if($arResult["ORDER"]["PARKING_TRANSACT"])
            $objOrder->saveProperty(
                "PARKING_TRANSACT_ID", 
                $arResult["ORDER"]["PARKING_TRANSACT"]
            );
        // Обновляем индексную таблицу
        $objCSync = new \Sync\CSync;
        $objCSync->syncUser($arUser["ID"]);
        $objCSync->syncOrder($nOrderId);
        LocalRedirect("/partners/orders/$nOrderId/");
        die;
    }
    else{
        $arResult["ERROR"] = 'Ошибка создания заказа '
            .print_r($objOrder->getErrors(),1);
    }
}



// Данные формы
$arResult["FORM"]["OFFERS"] = [];
$resOffer = \CIBlockElement::GetList(
    [],["IBLOCK_ID"=>OFFER_IB_ID,"ACTIVE"=>"Y"],false,false,["NAME"]
);
while($arOffer = $resOffer->Fetch()){
    $arResult["FORM"]["OFFERS"][] = $arOffer["NAME"];
}
$arResult["FORM"]["STATUSES"] = \Order\COrderStatus::getAll();
foreach($arResult["FORM"]["STATUSES"] as $nKey=>$arStatus){
    $arResult["FORM"]["STATUSES"][$nKey]["NAME"] = 
        getStatusAlias($arStatus["NAME"]);
}
$objStore = new \Catalog\CCatalogStore;
$arResult["FORM"]["STORES"] = $objStore->getAllActive();


$this->IncludeComponentTemplate();



