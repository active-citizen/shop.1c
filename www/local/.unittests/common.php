<?
    if(!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
        $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");

    require($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

