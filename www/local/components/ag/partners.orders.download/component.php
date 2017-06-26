<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('catalog');
$arUser = CUser::GetList(
    ($by="personal_country"), ($order="desc"),
    array("ID"=>CUser::GetId()),
    array(
        "SELECT"=>array(
            "UF_USER_ALL_POINTS",
            "UF_USER_STORAGE_ALL",
            "UF_USER_STORAGE_ID",
            "UF_USER_MAN_ALL",
            "UF_USER_MAN_ID"
        ),
        "NAV_PARAMS"=>array("nTopCount"=>1)
    )
    
)->getNext();

// Список доступных пользователю произвдителей
$arManFilter = array();
if(!$arUser["UF_USER_MAN_ALL"] && count($arUser["UF_USER_MAN_ID"]))
    $arManFilter["ID"] = $arUser["UF_USER_MAN_ID"];
elseif(!$arUser["UF_USER_MAN_ALL"] && !count($arUser["UF_USER_MAN_ID"]))
    $arManFilter["ID"] = 0;

$arManFilter["IBLOCK_ID"] = MANUFACTURER_IB_ID;


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


