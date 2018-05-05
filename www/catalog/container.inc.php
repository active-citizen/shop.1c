<?
    // Вычисляем ID раздела
    $arParams["SECTION_ID"] = $arCatalogMeta["ID"];
    $APPLICATION->IncludeComponent("ag:mobile.teasers",
        (IS_MOBILE || IS_PHONE)?".default":"desktop",
        $arParams,false
    );?> 

