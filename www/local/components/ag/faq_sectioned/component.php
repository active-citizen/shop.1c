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
    $arFilter["SECTION_ID"] = $nSectionId;
    // Сортировка для разделов и элементов
    $arSort = array("SORT"=>"ASC");

    // Получаем разделы
    $res = CIBlockSection::GetList( $arSort, $arFilter);   
    $arResult["sections"] = array();
    while($arSection = $res->GetNext())
       $arResult["sections"][] = $arSection;
    
    // Получаем пункты
    $nSectionId = intval($arParams["SECTION_ID"]);
    $resFAQ = CIBlockElement::GetList($arSort, $arFilter);
    $arResult["faq"] = array();
    while($arFAQ = $resFAQ->GetNext())
        $arResult["faq"][] = $arFAQ;

    // Получаем информацию о разделе
    if($nSectionId){
        unset($arFilter["SECTION_ID"]);
        $arFilter["ID"] = $nSectionId;
        $arResult["section"] = CIBlockSection::GetList(
            $arSort, $arFilter,false,array(
                "ID","NAME","DESCRIPTION"
            ),
            array("nTopCount"=>1)
        )->GetNext();
    }

    $this->IncludeComponentTemplate();
}
