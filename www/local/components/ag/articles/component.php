<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->StartResultCache(false)) {
    $RU = $_SERVER["REQUEST_URI"];
    // Значения по умолчанию

    CModule::IncludeModule('iblock');

    $arResult = CIBlockElement::GetList(
        array("SORT"=>"ASC"),
        array(
            "IBLOCK_CODE"   =>  "content_articles",
            "ACTIVE"        =>  "Y",
            "ID"            =>  $arParams["PRODUCT_CODE"]
        )
    )->GetNext();

    $this->IncludeComponentTemplate();
}
