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

$arResult["STATUSES"] = array();
$resStatuses = CSaleStatus::GetList();
while($arStatus = $resStatuses->Fetch())
    $arResult["STATUSES"][$arStatus["ID"]] = $arStatus;
    


$arResult["ORDER"]["BASKET"] = CSaleBasket::GetList(
    array(),
    array("ORDER_ID"=>$arResult["ORDER"]["ID"]),
    false,
    array("nTopCount"=>1)
)->Fetch();

$arResult["ORDER"]["OFFER"] = CIBlockElement::GetList(
    array(),
    array(
        "IBLOCK_ID"=>OFFER_IB_ID,
        "ID"=>$arResult["ORDER"]["BASKET"]["PRODUCT_ID"]
    ),
    false,
    array("nTopCount"=>1),
    array("PROPERTY_CML2_LINK")
)->Fetch();

$arResult["ORDER"]["PRODUCT"] = CIBlockElement::GetList(
    array(),
    array(
        "IBLOCK_ID"=>CATALOG_IB_ID,
        "ID"=>$arResult["ORDER"]["OFFER"]["PROPERTY_CML2_LINK_VALUE"]
    ),
    false,
    array("nTopCount"=>1),
    array("PROPERTY_SEND_CERT")
)->Fetch();

//echo "<pre>";
//print_r($arResult["ORDER"]["PRODUCT"]);
//echo "</pre>";




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
    if($arHistoryItem["TYPE"]!="ORDER_STATUS_CHANGED")continue;
    $arResult["ORDER"]["HISTORY"][] = $arHistoryItem;
}



$this->IncludeComponentTemplate();


