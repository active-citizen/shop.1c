<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["PAGE"] = 
    isset($_REQUEST["PAGEN_1"])
    &&
    intval($_REQUEST["PAGEN_1"])
    ?
    intval($_REQUEST["PAGEN_1"])
    :
    1;
$arParams["ON_PAGE"]=10;

$arOrder = array();
$arOrder["DATE_INSERT"] = "DESC";
$arFilter = array(
    "%ADDITIONAL_INFO"=>"Б"
);
$arSelect = array(
    "ID",
    "STATUS_ID",
    "ADDITIONAL_INFO",
    "USER_LAST_NAME",
    "USER_NAME",
    "DATE_INSERT",
    "USER_EMAIL",
    "USER_LOGIN"

);


$arResult["resOrders"] = CSaleOrder::GetList(
    $arOrder,
    $arFilter,
    false,
    array(
       "nPageSize"  =>  $arParams["ON_PAGE"],
       "iNumPage"   =>  $arParams["PAGE"]
    ),
    $arSelect
);

$arResult["ORDERS"] = array();
while($arOrder = $arResult["resOrders"]->GetNext()){
    $arResult["ORDERS"][] = $arOrder;    
}

//echo "<pre>";
//print_r($arResult["ORDERS"]);
//echo "</pre>";
    
$this->IncludeComponentTemplate();


