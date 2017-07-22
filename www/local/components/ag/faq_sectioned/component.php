<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->StartResultCache(false)) {
    $RU = $_SERVER["REQUEST_URI"];
    // Значения по умолчанию

    CModule::IncludeModule('iblock');

    // Фильтр для разделов и элементов
    $nSectionId = intval($arParams["SECTION_ID"]);
    $arFilter = array();
    $arFilter["ACTIVE"] = "Y";
    $arFilter["IBLOCK_CODE"] = "content_sectioned_faq";
    // Сортировка для разделов и элементов
    $arSort = array("SORT"=>"ASC");

    // Получаем разделы
    $res = CIBlockSection::GetList( $arSort, $arFilter);   
    $arResult["sections"] = array();
    while($arSection = $res->GetNext()){
        $arSection["childs"] = array();
        // Получаем пункты
        $arFilter["SECTION_ID"] = $arSection["ID"];
        $resFAQ = CIBlockElement::GetList($arSort, $arFilter);
        while($arFAQ = $resFAQ->GetNext())
            $arSection["childs"][] = $arFAQ;
        
        $arResult["sections"][] = $arSection;
    }
    


    $this->IncludeComponentTemplate();
}
