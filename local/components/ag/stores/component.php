<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$RU = $_SERVER["REQUEST_URI"];
// Значения по умолчанию

CModule::IncludeModule('catalog');


$resStores = CCatalogStore::GetList();

$arResult["stores"] = array();
while($arStore = $resStores->GetNext()){
    $arResult["stores"][] = $arStore;
}




$this->IncludeComponentTemplate();
