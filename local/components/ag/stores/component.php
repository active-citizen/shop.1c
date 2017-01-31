<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$RU = $_SERVER["REQUEST_URI"];
// Значения по умолчанию

CModule::IncludeModule('catalog');


$resStores = CCatalogStore::GetList();

$arResult["stores"] = array();
while($arStore = $resStores->GetNext()){
// Вычисляем остатки на складе
//    $res = CCatalogStoreProduct::GetList(array(),array("STORE_ID"=>$arStore["ID"]));
//    if(!$res->result->num_rows)continue;
    $arResult["stores"][] = $arStore;
}

$this->IncludeComponentTemplate();
