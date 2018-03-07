<?
    // Включаем безбитриксовое кеширование
    require($_SERVER["DOCUMENT_ROOT"]."/local/libs/customcache.lib.php");
    // Запись в ручной кэш (в обход битрикса)
    //customCache();
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    require("mobile.filter.params.php");

    $arParams["AJAX"] = true;
    $APPLICATION->IncludeComponent(
        "ag:mobile.teasers", 
        "", 
        $arParams,
        false
    );
?>



