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


if(!isset($_REQUEST["EDIT"]) && intval($_REQUEST["ID"])){
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

$arResult["ERRORS"] = array();
if(
    !$_REQUEST["LOGIN"]
    ||
    !preg_match("#^[\d\w\.\-\_\@]+$#",$_REQUEST["LOGIN"])
){
    $arResult["ERRORS"][] = 'Некорректный формат поля Логин';
}
elseif(
    $_REQUEST["REPASSWORD"]!=$_REQUEST["PASSWORD"]
){
    $arResult["ERRORS"][] = 'Пароль и повтор пароля не совпадают';
}
elseif(
    !$_REQUEST["GROUPS_ID"]
){
    $arResult["ERRORS"][] = 'Необходимо указать хотя бы один из типов
    пользователя';
}
elseif(
    (
        !$_REQUEST["ALL_STORES"]
        &&
        !$_REQUEST["STORES"]
    )
    ||
    (
        !$_REQUEST["ALL_MANS"]
        &&
        !$_REQUEST["MANS"]
    )
){
    $arResult["ERRORS"][] = 'Указанные вами доступы к заказам не позволяют
    отобразить ни одного заказа';
}
elseif(
    (
        $_REQUEST["ALL_STORES"]
        &&
        $_REQUEST["STORES"]
        &&
        !in_array(0, $_REQUEST["STORES"])
    )
){
    $arResult["ERRORS"][] = 'При выбранном глобальном доступе ко всем складам
    необходимо снять пометку доступа к конкретным складам(пункт "-нет-")';
}
elseif(
    (
        $_REQUEST["ALL_MANS"]
        &&
        $_REQUEST["MANS"]
        &&
        !in_array(0, $_REQUEST["MANS"])
    )
){
    $arResult["ERRORS"][] = 'При выбранном глобальном доступе ко всем
    производителям  необходимо снять пометку доступа к конкретным
    производителям(пункт "-нет-")';
}
elseif($_REQUEST["EDIT"]){
    $arFields = array(
        "LOGIN"         =>  $_REQUEST["LOGIN"],
        "EMAIL"         =>  $_REQUEST["EMAIL"],
        "NAME"          =>  $_REQUEST["NAME"],
        "LAST_NAME"     =>  $_REQUEST["LAST_NAME"],
        "UF_USER_ORDER_CANCEL"  =>  true,
        "UF_USER_ORDER_DONE"    =>  true
    );

    if($_REQUEST["PASSWORD"])
        $arFields["PASSWORD"] = $_REQUEST["PASSWORD"];
    if($_REQUEST["REPASSWORD"])
        $arFields["REPASSWORD"] = $_REQUEST["REPASSWORD"];

    $arFields["GROUP_ID"] = array(6);
    if(isset($_REQUEST["GROUPS_ID"][PARTNERS_GROUP_ID]))
        $arFields["GROUP_ID"][] = PARTNERS_GROUP_ID;
    if(isset($_REQUEST["GROUPS_ID"][OPERATORS_GROUP_ID]))
        $arFields["GROUP_ID"][] = OPERATORS_GROUP_ID;

    if(isset($_REQUEST["ALL_STORES"]))
        $arFields["UF_USER_STORAGE_ALL"] = true;
    else
        $arFields["UF_USER_STORAGE_ALL"] = false;

    if(isset($_REQUEST["ALL_MANS"]))
        $arFields["UF_USER_MAN_ALL"] = true;
    else
        $arFields["UF_USER_MAN_ALL"] = false;
         
    if(isset($_REQUEST["MANS"]) && !in_array(0, $_REQUEST["MANS"]))
        $arFields["UF_USER_MAN_ID"] = $_REQUEST["MANS"];
    else
        $arFields["UF_USER_MAN_ID"] = array();
          
    if(isset($_REQUEST["MANS"]) && !in_array(0, $_REQUEST["MANS"]))
        $arFields["UF_USER_MAN_ID"] = $_REQUEST["MANS"];
    else
        $arFields["UF_USER_MAN_ID"] = array();

    if(isset($_REQUEST["STORES"]) && !in_array(0, $_REQUEST["STORES"]))
        $arFields["UF_USER_STORAGE_ID"] = $_REQUEST["STORES"];
    else
        $arFields["UF_USER_STORAGE_ID"] = array();

    if($USER->Update($_REQUEST["ID"], $arFields)){
        $url = explode("?",$arParams["BACK_URL"]);
        LocalRedirect($url[0]."?SUCCESS=Пользователь отредактирован");
        die;
    }
    else{
        $arResult["ERRORS"][] = 'Ошибка редактирования пользователя: '
        .$USER->LAST_ERROR
        ;
        
    }

}






$this->IncludeComponentTemplate();


