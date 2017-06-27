<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CModule::IncludeModule('catalog');
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

$arParams["DUMP_FORDER"] =  
    isset($arParams["DUMP_FORDER"])
    ?
    $arParams["DUMP_FORDER"]
    :
    $_SERVER["DOCUMENT_ROOT"]."/dump";

$this->IncludeComponentTemplate();


