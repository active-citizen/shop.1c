<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Значения по умолчанию
if(!isset($arParams["BACK_URL"]))$arParams["BACK_URL"] = '/partners/users/list/';

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



if(!isset($_REQUEST["DELETE"]) && intval($_REQUEST["ID"])){
    $arUser = CUser::GetList(
        ($by="ID"), ($order="desc"),
        array(
            "ID"=>intval($_REQUEST["ID"])
        ),
        array(
            "SELECT"    => array(
                "UF_USER_STORAGE_ALL",
                "UF_USER_STORAGE_ID",
                "UF_USER_MAN_ALL",
                "UF_USER_MAN_ID"
            ),
            "NAV_PARAMS"=>array("nTopCount"=>1)
        )
    )->GetNext();
    $_REQUEST["LOGIN"]      =   $arUser["LOGIN"];
    $_REQUEST["NAME"]       =   $arUser["NAME"];
    $_REQUEST["LAST_NAME"]  =   $arUser["LAST_NAME"];
    $_REQUEST["EMAIL"]      =   $arUser["EMAIL"];
    $_REQUEST["STORES"]     =   $arUser["UF_USER_STORAGE_ID"];
    $_REQUEST["MANS"]       =   $arUser["UF_USER_MAN_ID"];


    $_REQUEST["GROUPS_ID"]  = array();

    // Списки пользователей в группах
    $arGroupPartners = CGroup::GetGroupUser(PARTNERS_GROUP_ID);
    $arGroupOperators = CGroup::GetGroupUser(OPERATORS_GROUP_ID);

    if(in_array($_REQUEST["ID"], $arGroupPartners))
        $_REQUEST["GROUPS_ID"][PARTNERS_GROUP_ID] = 'on';
    elseif(isset($_REQUEST["GROUPS_ID"][PARTNERS_GROUP_ID]))
        unset($_REQUEST["GROUPS_ID"][PARTNERS_GROUP_ID]);

    if(in_array($_REQUEST["ID"], $arGroupOperators))
        $_REQUEST["GROUPS_ID"][OPERATORS_GROUP_ID] = 'on';
    elseif(isset($_REQUEST["GROUPS_ID"][OPERATORS_GROUP_ID]))
        unset($_REQUEST["GROUPS_ID"][OPERATORS_GROUP_ID]);



    if($arUser["UF_USER_STORAGE_ALL"])
        $_REQUEST["ALL_STORES"] = 'on';
    elseif(isset($_REQUEST["ALL_STORES"]))
        unset($_REQUEST["ALL_STORES"]);

    if($arUser["UF_USER_MAN_ALL"])
        $_REQUEST["ALL_MANS"] = 'on';
    elseif(isset($_REQUEST["ALL_MANS"]))
        unset($_REQUEST["ALL_MANS"]);

}

if($_REQUEST["DELETE"]){
    if(CUser::Delete($_REQUEST["ID"])){
        $url = explode("?",$arParams["BACK_URL"]);
        LocalRedirect($url[0]."?SUCCESS=Пользователь удалён");
        die;
    }
    else{
        $arResult["ERRORS"][] = 'Ошибка редактирования пользователя: '
        .$USER->LAST_ERROR
        ;
        
    }
}

$this->IncludeComponentTemplate();


