<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
    Компонент выводит список пользователей групп "партнёры" и "операторы МФЦ"
    с формой добавления, фильтром и ссылками на редактирование
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

$RU = $_SERVER["REQUEST_URI"];
// Значения по умолчанию
if(!isset($arParams["RECORDS_ON_PAGE"]))$arParams["RECORDS_ON_PAGE"] = 3;
if(!isset($arParams["PAGE_NUM"]))$arParams["PAGE_NUM"] =
    intval($arParams["PAGE_NUM"])
    ?
    intval($arParams["PAGE_NUM"])
    :
    1
    ;
if(!isset($arParams["BASE_URL"]))$arParams["BASE_URL"] = '/partners/users/';

if(!is_array($arParams["FILTER"]))$arParams["FILTER"] = array();
if(!isset($arParams["FILTER"]["LOGIN"]))
    $arParams["FILTER"]["LOGIN"] = 
        isset($_REQUEST["FILTER"]["LOGIN"])
        ?
        $_REQUEST["FILTER"]["LOGIN"]
        :
        "";
if(!isset($arParams["FILTER"]["SURNAME"]))
    $arParams["FILTER"]["SURNAME"] = 
        isset($_REQUEST["FILTER"]["SURNAME"])
        ?
        $_REQUEST["FILTER"]["SURNAME"]
        :
        "";
if(!isset($arParams["FILTER"]["NAME"]))
    $arParams["FILTER"]["NAME"] = 
        isset($_REQUEST["FILTER"]["NAME"])
        ?
        $_REQUEST["FILTER"]["NAME"]
        :
        "";
if(!isset($arParams["FILTER"]["TYPE"]))
    $arParams["FILTER"]["TYPE"] = 
        isset($_REQUEST["FILTER"]["TYPE"])
        ?
        $_REQUEST["FILTER"]["TYPE"]
        :
        "";
if(!isset($arParams["FILTER"]["MAN_ID"]))
    $arParams["FILTER"]["MAN_ID"] = 
        isset($_REQUEST["FILTER"]["MAN_ID"])
        ?
        $_REQUEST["FILTER"]["MAN_ID"]
        :
        "";
if(!isset($arParams["FILTER"]["STORES"]))
    $arParams["FILTER"]["STORES"] = 
        isset($_REQUEST["FILTER"]["STORES"])
        ?
        $_REQUEST["FILTER"]["STORES"]
        :
        "";

// Списки пользователей в группах
$arGroupPartners = CGroup::GetGroupUser(PARTNERS_GROUP_ID);
$arGroupOperators = CGroup::GetGroupUser(OPERATORS_GROUP_ID);

// Списки производителей
$resMans = CIBlockElement::GetList(
    array(),
    array("IBLOCK_ID"=>MANUFACTURER_IB_ID),
    false,
    false,
    array("ID","NAME")
);
$arResult["MANS"] = array();
while($arMan = $resMans->GetNext()){
    $arResult["MANS"][$arMan["ID"]] = $arMan;
}


// Списки складов
$resStores = CCatalogStore::GetList(
    array(),
    array(),
    false,
    false,
    array("ID","TITLE")
);
$arResult["STORES"] = array();
while($arStore = $resStores->GetNext()){
    $arResult["STORES"][$arStore["ID"]] = $arStore;
}


$arFilter = array();

$arFilter["GROUPS_ID"] = 
    intval($arParams["FILTER"]["TYPE"])
    ?
    $arParams["FILTER"]["TYPE"]
    :
    array(
        PARTNERS_GROUP_ID,
        OPERATORS_GROUP_ID
    )    
    ;
if($arParams["FILTER"]["MAN_ID"])$arFilter["UF_USER_MAN_ID"] = 
    intval($arParams["FILTER"]["MAN_ID"]);
if($arParams["FILTER"]["STORES"])$arFilter["UF_USER_STORAGE_ID"] = 
    intval($arParams["FILTER"]["STORES"]);
    
if($arParams["FILTER"]["LOGIN"])$arFilter["LOGIN"] = 
    $arParams["FILTER"]["LOGIN"];
if($arParams["FILTER"]["SURNAME"])
    $arFilter["LAST_NAME"] = $arParams["FILTER"]["SURNAME"];
if($arParams["FILTER"]["NAME"])
    $arFilter["NAME"] = $arParams["FILTER"]["NAME"];


$resUser = CUser::GetList(
    ($by="personal_country"), ($order="desc"),
    $arFilter,
    array(
        "SELECT"    =>  array(
            "ID","ACTIVE","LOGIN","NAME","LAST_NAME",
            "UF_USER_STORAGE_ALL",
            "UF_USER_STORAGE_ID",
            "UF_USER_MAN_ALL",
            "UF_USER_MAN_ID"
        ),
        "NAV_PARAMS" =>  array(
            "nPageSize" =>  $arParams["RECORDS_ON_PAGE"],
            "iNumPage"  =>  $arParams["PAGE_NUM"]
        )
    )
);
$arResult["resUser"] = $resUser;

$arResult["USERS"] = array();
while($arUser = $resUser->GetNext()){
    $arUser["GROUPS"] = array(
        "PARTNER"   =>  in_array($arUser["ID"], $arGroupPartners),
        "OPERATOR"  =>  in_array($arUser["ID"], $arGroupOperators)
    );
    $arResult["USERS"][] = $arUser;
}


$this->IncludeComponentTemplate();


