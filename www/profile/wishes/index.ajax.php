<?
// Включаем безбитриксовое кеширование
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/customcache.lib.php");
// Запись в ручной кэш (в обход битрикса)
customCache();
//customCacheClear();
//sleep(1);


define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(IS_MOBILE || IS_PHONE)
    require($_SERVER["DOCUMENT_ROOT"]."/catalog/mobile.filter.params.php");
else
    require($_SERVER["DOCUMENT_ROOT"]."/catalog/desktop.filter.params.php");
    

$arParams["AJAX"] = true;
$APPLICATION->IncludeComponent(
    "ag:mobile.teasers", 
    IS_MOBILE || IS_PHONE?".default":"desktop", 
    $arParams,
    false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
