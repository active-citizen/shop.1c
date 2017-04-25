<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->StartResultCache(false)) {
    $RU = $_SERVER["REQUEST_URI"];
    // Значения по умолчанию

    CModule::IncludeModule('iblock');


    $resFAQ = CIBlockElement::GetList(
        array("SORT"=>"ASC"),
        array(
            "IBLOCK_CODE"   =>  "content_faq",
            "ACTIVE"        =>  "Y"
        )
    );

    $arResult["faq"] = array();
    while($arFAQ = $resFAQ->GetNext()){
        $arResult["faq"][] = $arFAQ;
    }

    $this->IncludeComponentTemplate();
}
