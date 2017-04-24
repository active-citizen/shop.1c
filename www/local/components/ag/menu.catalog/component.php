<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


if ($this->StartResultCache(false)) {
    // Получаем корневых разделов
    CModule::IncludeModule("iblock");
    $res = CIBlockSection::GetList(
        array(),
        array("ACTIVE"=>"Y","IBLOCK_ID"=>CATALOG_IB_ID,"SECTION_ID"=>0),
        false,
        false
    );
    $arResult["SECTIONS"] = array();
    while($section = $res->getNext()){
        $arResult["SECTIONS"][$section["ID"]] = $section;
        $res1 = CIBlockElement::GetList(
            array(),array("SECTION_ID"=>$section["ID"],"ACTIVE"=>"Y"),false
        );
        $arResult["SECTIONS"][$section["ID"]]["products"]=$res1->SelectedRowsCount();
    }
 
    $this->IncludeComponentTemplate();
}

