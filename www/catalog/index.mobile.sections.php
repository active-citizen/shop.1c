<?
    /**
     * Формирование json для поиска
    */
    
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    require("mobile.filter.params.php");

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProductTag.class.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogStore.class.php"
    );
    use AGShop\Catalog as Catalog;

    $arResult = [];

    // Фильтр по интересам
    $objTag = new \Catalog\CCatalogProductTag(INTEREST_PROPERTY_ID);
    $arTags = $objTag->getAllTags();
    foreach($arTags as $arTag){
        $sKey = CUtil::translit($arTag["NAME"],"ru",[
            "change_case"   =>  false,
            "replace_space" =>  "",
            "replace_other" =>  ""
        ]);
        
        $arResult[] = [
            "category"=>$arTag["NAME"],
            "url"=>"/catalog/?productInterest$sKey=".$arTag["ID"]
                ."&mobileFiltersSubmit="
        ];
    }


    // Фильтр по складам
    $objStore = new \Catalog\CCatalogStore;
    $arStores = $objStore->getForSite(false);
    foreach($arStores['stores'] as $arStore){
        $sKey = CUtil::translit($arStore["TITLE"],"ru",[
            "change_case"   =>  false,
            "replace_space" =>  "",
            "replace_other" =>  ""
        ]);
        
        $arStore["TITLE"] = str_replace("«","",$arStore["TITLE"]);
        $arStore["TITLE"] = str_replace("»","",$arStore["TITLE"]);
        $arStore["TITLE"] = str_replace("\"","",$arStore["TITLE"]);
        $arStore["TITLE"] = str_replace("'","",$arStore["TITLE"]);
        $arStore["TITLE"] = str_replace("-"," ",$arStore["TITLE"]);
        $arResult[] = [
            "category"=>$arStore["TITLE"],
            "url"=>"/catalog/?productDelivery$sKey=".$arStore["ID"]
                ."&mobileFiltersSubmit="
        ];
    }

    // Фильтр по флагам
    $arResult[] = [
        "category"=>"Хит",
        "url"=>"/catalog/?productHitCheckbox=1&mobileFiltersSubmit="
    ];
    $arResult[] = [
        "category"=>"Новинки",
        "url"=>"/catalog/?productNewCheckbox=2&mobileFiltersSubmit="
    ];
    $arResult[] = [
        "category"=>"Акции",
        "url"=>"/catalog/?productSaleCheckbox=3&mobileFiltersSubmit="
    ];
    


    echo json_encode($arResult);


/*
[
{ "category": "Музеи" },
{ "category": "Сувениры" },
{ "category": "Мероприятия" },
{ "category": "Электронный контент" },
{ "category": "Транспорт" },
{ "category": "Другое" },
{ "category": "новинки" },
{ "category": "хит" },
{ "category": "акция" }
]
*/
