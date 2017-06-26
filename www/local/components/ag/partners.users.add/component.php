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

$arResult["ERRORS"] = array();
if(
    !$_REQUEST["LOGIN"]
    ||
    !preg_match("#^[\d\w\.\-\_]+$#",$_REQUEST["LOGIN"])
){
    $arResult["ERRORS"][] = 'Некорректный формат поля Логин';
}
elseif(
    !$_REQUEST["PASSWORD"]
){
    $arResult["ERRORS"][] = 'Не указан пароль';
}
elseif(
    !$_REQUEST["REPASSWORD"]
){
    $arResult["ERRORS"][] = 'Не указан повтор пароля';
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
else{
    $arFields = array(
        "LOGIN"         =>  $_REQUEST["LOGIN"],
        "PASSWORD"      =>  $_REQUEST["PASSWORD"],
        "REPASSWORD"    =>  $_REQUEST["REPASSWORD"],
        "EMAIL"         =>  $_REQUEST["EMAIL"],
        "NAME"          =>  $_REQUEST["NAME"],
        "LAST_NAME"     =>  $_REQUEST["LAST_NAME"],
        "UF_USER_ORDER_CANCEL"  =>  true,
        "UF_USER_ORDER_DONE"    =>  true
    );
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

    if($USER->Add($arFields)){
        $url = explode("?",$arParams["BACK_URL"]);
        LocalRedirect($url[0]."?SUCCESS=Пользователь добавлен");
        die;
    }
    else{
        $arResult["ERRORS"][] = 'Ошибка добавления пользователя: '
        .$USER->LAST_ERROR
        ;
        
    }

}






$this->IncludeComponentTemplate();


