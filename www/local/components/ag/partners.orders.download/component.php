<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Справочник статусов
$arResult["STATUSES"] = array();
$resStatuses = CSaleStatus::GetList(
    array("SORT"=>"ASC")
    ,array("!COLOR"=>"")
    ,false
    ,false
    ,array("ID","NAME","COLOR")
);
while($arStatus=$resStatuses->GetNext()){
    $arResult["STATUSES"][$arStatus["ID"]] = $arStatus;
}

$arManFilter["IBLOCK_ID"] = MANUFACTURER_IB_ID;


$resMans = CIBlockElement::GetList(
    array("ID"=>"ASC"),
    $arManFilter,
    false,
    false,
    array("NAME","ID")
);
$arResult["MANS"] = array();
$arParams["MY_MANS_IDS"] = array();
while($arMan = $resMans->GetNext()){
    $arParams["MY_MANS_IDS"][] = $arMan["ID"];
    $arResult["MANS"][$arMan["ID"]] = $arMan;
}

$this->IncludeComponentTemplate();


