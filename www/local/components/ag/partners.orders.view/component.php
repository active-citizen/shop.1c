<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CModule::IncludeModule('catalog');
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

$arParams["ORDER_ID"] = 
    isset($arParams["ORDER_ID"]) && intval($arParams["ORDER_ID"])
    ?
        intval($arParams["ORDER_ID"])
    :
    0;

$arResult["ORDER"] = CSaleOrder::GetList(
    array(),array("ID"=>$arParams["ORDER_ID"]),
    false,array("nTopCount"=>1),
    array(
    )
)->Fetch();

if(!$arResult["ORDER"]){
    echo "Order is not exists";
    die;
}
// ID группы свойств
$arPropGroup = CSaleOrderPropsGroup::GetList(
    array(),
    $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
    false,
    array("nTopCount"=>1)
)->GetNext();
$nPropGroup = $arPropGroup["ID"];
// Получаем свойства заказа
$resPropValues = CSaleOrderProps::GetList(
    array("SORT" => "ASC"),
    array(
            "ORDER_ID"       => $arOrder["ID"],
            "PERSON_TYPE_ID" => 1,
            "PROPS_GROUP_ID" => $nPropGroup,
        ),
    false,
    false,
    array("ID","CODE","NAME")
);
$arOrder["ORDER"]["PROPERTIES"] = array();
$arResult["PROPERTIES"] = array();
while($arProp = $resPropValues->GetNext()){
    $arResult["PROPERTIES"][$arProp["CODE"]] = $arProp;
    $arResult["ORDER"]["PROPERTIES"][$arProp["CODE"]] = 
        CSaleOrderPropsValue::GetList(
            array(),
            $arFilterProp = array(
                "ORDER_ID"=>$arResult["ORDER"]["ID"],
                "ORDER_PROPS_ID"=>$arProp["ID"]
            )
        )->GetNext();
}

if(isset($_REQUEST["chansge_status"])){

    $arProperties =  orderGetProperties($arResult["ORDER"]["ID"],["CHANGE_REQUEST"]);
    $error = true;
    if(
        !isset($arProperties["CHANGE_REQUEST"]["VALUE"])
        ||
        !trim($arProperties["CHANGE_REQUEST"]["VALUE"])
    ){
        orderSetZNI(
            $arResult["ORDER"]["ID"],
            $_REQUEST["status_id"],
            $arResult["ORDER"]["STATUS_ID"]
        );
        $error = false;
    }
    
    LocalRedirect(
        "/partners/orders/".$arResult["ORDER"]["ID"]."/".
        ($error?"?error=1":"")
    );
}


// Склад
$arResult["ORDER"]["STORE_INFO"] = CCatalogStore::GetList(
    array(),
    array("ID"=>$arResult["ORDER"]["STORE_ID"]),
    false,array("nTopCount"=>1)
)->Fetch();


//echo "<pre>";
//print_r($arResult);
//die;

$arResult["STATUSES"] = array();
$resStatuses = CSaleStatus::GetList();
while($arStatus = $resStatuses->Fetch())
    $arResult["STATUSES"][$arStatus["ID"]] = $arStatus;
    


$resBasket = CSaleBasket::GetList(
    array(),
    array("ORDER_ID"=>$arResult["ORDER"]["ID"]),
    false,
    array("nTopCount"=>1)
);

$arResult["ORDER"]["BASKET"] = array();

while($arBasket = $resBasket->Fetch()){
    $arOffer = CIBlockElement::GetList(
        array(),
        array(
            "IBLOCK_ID"=>OFFER_IB_ID,
            "ID"=>$arBasket["PRODUCT_ID"]
        ),
        false,
        array("nTopCount"=>1),
        array("PROPERTY_CML2_LINK")
    )->Fetch();

    $arProduct = CIBlockElement::GetList(
        array(),
        array(
            "IBLOCK_ID"=>CATALOG_IB_ID,
            "ID"=>$arOffer["PROPERTY_CML2_LINK_VALUE"]
        ),
        false,
        array("nTopCount"=>1),
        array(
            "PROPERTY_SEND_CERT","ID","NAME","CODE","PREVIEW_PICTURE","DETAIL_TEXT"
            ,"PROPERTY_MINIMUM_PRICE","IBLOCK_SECTION_ID","PROPERTY_QUANT"
            ,"PROPERTY_RECEIVE_RULES","PROPERTY_CANCEL_RULES"

        )
    //  array()
    )->Fetch();

    $arSection = CIBlockSection::GetList(
        array(),
        array("ID"=>$arProduct["IBLOCK_SECTION_ID"]),
        false,
        array(),
        array("nTopCount"=>1)
    )->Fetch();


    $arProduct["IMAGE"] = CFile::GetPath(
        $arProduct["PREVIEW_PICTURE"]
    );
    $arResult["ORDER"]["BASKET"][] = array(
        "BASKET_ITEM"   =>  $arBasket,
        "PRODUCT"       =>  $arProduct,
        "SECTION"       =>  $arSection
    );
}


$arResult["HISTORY_TYPES"] = array(
    "ORDER_STATUS_CHANGED"  =>  "Изменение статуса заказа",
//    "ORDER_UPDATED"         =>  "Изменение заказа",
    "ORDER_ADDED"           =>  "Добавление заказа",
    "ORDER_ZNI"             =>  "Запрос на изменение статуса",
    "ORDER_ZNI_CHECK"             =>  "Заказ выгружен в 1С",
    "ORDER_CANCELED"        =>  "Заказ отменён"
);



$arResult["ORDER"]["HISTORY"] = array();
$resHistory = CSaleOrderChange::GetList(
    array("ID"=>"DESC"),
    array(
        "ORDER_ID"=>$arResult["ORDER"]["ID"]
    ),
    false,
    false//array("nTopCount"=>10)

);
while($arHistoryItem = $resHistory->Fetch()){
//    echo "<pre>";
//    print_r($arHistoryItem);
//    echo "</pre>";
    if(
        $arHistoryItem["ENTITY"] != "ORDER"
    )continue;
    $arHistoryItem["DATA"] = unserialize($arHistoryItem["DATA"]);
    if(!isset($arResult["HISTORY_TYPES"][$arHistoryItem["TYPE"]]))continue;
    // Имя пользователя
    $arHistoryItem["USER_INFO"] =
    CUser::GetById($arHistoryItem["USER_ID"])->Fetch();
    $arResult["ORDER"]["HISTORY"][
        $arHistoryItem["TYPE"].mb_substr($arHistoryItem["DATE_CREATE"],0,16)
    ] = $arHistoryItem;
}

//echo "<!-- ";
//print_r($arResult);
//echo " -->";

// Админам доступны логи обмена
if(
$USER->isAdmin()
||
in_array(SHOP_ADMIN, $USER->GetUserGroupArray())
){
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/.integration/classes/curllogger.class.php"
    );
    $objCurlLogger = new CCurlLogger();
    $arResult["ORDER"]["CURL_LOG"] = $objCurlLogger->getByOrderNum(
        $arResult["ORDER"]["ADDITIONAL_INFO"]
    );

    foreach($arResult["ORDER"]["CURL_LOG"] as $arLog)break;
    $objLog = json_decode($arLog["data"]);
    if(
        $objLog
        &&
        is_object($objLog)
        &&
        property_exists($objLog,"errorCode")
        &&
        property_exists($objLog,"errorDesc")
    ){
        
        $arResult["ERROR_CODE"] = $objLog->errorCode;
        $arResult["ERROR_DESC"] = $objLog->errorDesc;
    }

    // Добавляем информацию о письме в индекс
    require_once(
        $_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/CMailIndex.class.php"
    );
    $obMail = new CMailIndex;
      
    $arResult["MAILS"] = $obMail->getByOrderId($arParams["ORDER_ID"]);
}


$this->IncludeComponentTemplate();


