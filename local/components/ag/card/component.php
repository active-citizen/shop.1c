<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$RU = $_SERVER["REQUEST_URI"];
// Значения по умолчанию

/*
if(!isset($arParams["ALL_TITLE"]))$arParams["ALL_TITLE"] = "Все начисления и списания";
if(!isset($arParams["DEBIT_TITLE"]))$arParams["DEBIT_TITLE"] = "Все начисления";
if(!isset($arParams["CREDIT_TITLE"]))$arParams["CREDIT_TITLE"] = "Все списания";


if(!isset($arParams["ALL_FOLDER"]))$arParams["ALL_FOLDER"] = "all";
if(!isset($arParams["DEBIT_FOLDER"]))$arParams["DEBIT_FOLDER"] = "debit";
if(!isset($arParams["CREDIT_FOLDER"]))$arParams["CREDIT_FOLDER"] = "credit";

if(!isset($arParams["SELF_FOLDER"]))$arParams["CREDIT_FOLDER"] = "/points/";
if(!isset($arParams["USER_ID"]))$arParams["USER_ID"] = CUser::GetID();
if(!isset($arParams["SORT"]))$arParams["SORT"] = array("TRANSACT_DATE"=>"DESC");
*/
if(!isset($arParams["PRODUCT_CODE"]))$arParams["PRODUCT_CODE"] = '';
if(!isset($arParams["CATALOG_IBLOCK_ID"]))$arParams["CATALOG_IBLOCK_ID"] = 2;
if(!isset($arParams["OFFER_IBLOCK_ID"]))$arParams["OFFER_IBLOCK_ID"] = 3;
if(!isset($arParams["USER_ID"]))$arParams["USER_ID"] = $USER->GetId();

//Определяем сумму на счету пользователя
CModule::IncludeModule("sale");
$res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>$arParams["USER_ID"]));
$arResult["ACCOUNT"] = $res->GetNext();


CModule::IncludeModule('iblock');
// Информация об элементе каталога
$resCatalog = CIBlockElement::GetList(
    array(),array(
        "IBLOCK_ID" =>  $arParams["CATALOG_IBLOCK_ID"],
        "CODE"      =>   $arParams["PRODUCT_CODE"]
    ),
    false,
    array("nTopCount"=>1)
);
$arResult["CATALOG_ITEM"] = $resCatalog->GetNext();

// Сколько у товара всего желающих
$arFilter = array("IBLOCK_CODE"=>"whishes", "PROPERTY_WISH_PRODUCT"=>$arResult["CATALOG_ITEM"]["ID"]);
$res1 = CIBlockElement::GetList(array(),$arFilter,false, array());
$arResult["WISHES"] = $res1->SelectedRowsCount();

// Входит ли товар с писок моих желаний
$arFilter = array(
    "IBLOCK_CODE"=>"whishes", 
    "PROPERTY_WISH_USER"=>$arParams["USER_ID"],
    "PROPERTY_WISH_PRODUCT"=>$arResult["CATALOG_ITEM"]["ID"]);
$res1 = CIBlockElement::GetList(array(),$arFilter,false, array("nTopCount"=>1));
$arResult["MYWISH"] = $res1->SelectedRowsCount();

// Свойства элемента каталога
$arResult["CATALOG_ITEM"]["PROPERTIES"] = array();
$resProps = CIBlockElement::GetProperty($arParams["CATALOG_IBLOCK_ID"],$arResult["CATALOG_ITEM"]["ID"]);
while($arProp = $resProps->GetNext()){
    if(!isset($arResult["CATALOG_ITEM"]["PROPERTIES"]))
        $arResult["CATALOG_ITEM"]["PROPERTIES"][$arProp["CODE"]] = array();
    if($arProp["PROPERTY_TYPE"]=='F')
        $arProp["FILE_PATH"] = CFile::GetPath($arProp["VALUE"]);
    $arResult["CATALOG_ITEM"]["PROPERTIES"][$arProp["CODE"]][] = $arProp;
}

// Вычисляем рейтинг
$arResult["CATALOG_ITEM"]["RATING"] = round($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING"][0]["VALUE"]*5,2);

// Торговые предложения
$resOffers = CIBlockElement::GetList(
    array(),$arFilter = array(
        "IBLOCK_ID"         =>  $arParams["OFFER_IBLOCK_ID"],
        "PROPERTY_CML2_LINK"=>  $arResult["CATALOG_ITEM"]["ID"]
    ),
    false
);
$arResult["OFFERS"] = array();
while($arOffer = $resOffers->GetNext()){
    $arOffer["PROPERTIES"] = array();
    $resProps = CIBlockElement::GetProperty($arParams["OFFER_IBLOCK_ID"],$arOffer["ID"]);
    while($arProp = $resProps->GetNext()){
        if(!isset($arOffer["PROPERTIES"][$arProp["CODE"]]))
            $arOffer["PROPERTIES"][$arProp["CODE"]] = array();
        if($arProp["PROPERTY_TYPE"]=='F')
            $arProp["FILE_PATH"] = CFile::GetPath($arProp["VALUE"]);
        if($arProp["PROPERTY_TYPE"]=='F' && !$arProp["FILE_PATH"])continue;
        $arOffer["PROPERTIES"][$arProp["CODE"]][] = $arProp;
    }
    
    
    $arOffer["RRICE_INFO"] = CPrice::GetList(array(),array("PRODUCT_ID"=>$arOffer["ID"]))->GetNext();
    
    $arResult["OFFERS"][] = $arOffer;
    
};
echo "<!-- ";
print_r($arResult["CATALOG_ITEM"]);
print_r($arResult["OFFERS"][0]);
echo " -->";


$this->IncludeComponentTemplate();
