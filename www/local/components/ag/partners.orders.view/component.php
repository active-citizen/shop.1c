<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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
            "PROPERTY_SEND_CERT","ID","NAME","CODE","PREVIEW_PICTURE",
            "PROPERTY_MINIMUM_PRICE","IBLOCK_SECTION_ID","PROPERTY_QUANT"
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
    "ORDER_UPDATED"         =>  "Изменение заказа",
    "ORDER_ADDED"           =>  "Добавление заказа"
);



$arResult["ORDER"]["HISTORY"] = array();
$resHistory = CSaleOrderChange::GetList(
    array("ID"=>"DESC"),
    array(
        "ORDER_ID"=>$arResult["ORDER"]["ID"]
    ),
    false,
    array("nTopCount"=>10)

);
while($arHistoryItem = $resHistory->Fetch()){
    if($arHistoryItem["ENTITY"] != "ORDER")continue;
    $arHistoryItem["DATA"] = unserialize($arHistoryItem["DATA"]);
    if(!isset($arResult["HISTORY_TYPES"][$arHistoryItem["TYPE"]]))continue;
    $arResult["ORDER"]["HISTORY"][] = $arHistoryItem;
}



$this->IncludeComponentTemplate();


