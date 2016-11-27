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


CModule::IncludeModule('iblock');

$resCatalog = CIBlockElement::GetList(
    array(),array(
        "IBLOCK_ID" =>  $arParams["CATALOG_IBLOCK_ID"],
        "CODE"      =>   $arParams["PRODUCT_CODE"]
    ),
    false,
    array("nTopCount"=>1)
);
$arResult["CATALOG_ITEM"] = $resCatalog->GetNext();

$arResult["CATALOG_ITEM"]["PROPERTIES"] = array();
$resProps = CIBlockElement::GetProperty($arParams["CATALOG_IBLOCK_ID"],$arResult["CATALOG_ITEM"]["ID"]);
while($arProp = $resProps->GetNext())$arResult["CATALOG_ITEM"]["PROPERTIES"][$arProp["CODE"]] = $arProp;

if($arResult["CATALOG_ITEM"]["DETAIL_PICTURE"])
    $arResult["CATALOG_ITEM"]["DETAIL_PICTURE_PATH"] = CFile::GetPath($arResult["CATALOG_ITEM"]["DETAIL_PICTURE"]);


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
    while($arProp = $resProps->GetNext())$arOffer["PROPERTIES"][$arProp["CODE"]] = $arProp;
    if($arOffer["DETAIL_PICTURE"])
        $arOffer["DETAIL_PICTURE_PATH"] = CFile::GetPath($arOffer["DETAIL_PICTURE"]);
    else
        $arOffer["DETAIL_PICTURE_PATH"] = $arResult["CATALOG_ITEM"]["DETAIL_PICTURE_PATH"];
    $arResult["OFFERS"][] = $arOffer;
    
};
echo "<pre>";
print_r($arResult["OFFERS"]);
echo "</pre>";


$this->IncludeComponentTemplate();

