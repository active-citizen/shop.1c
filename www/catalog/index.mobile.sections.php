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
        
        $arResult[] = [
            "category"=>$arStore["TITLE"],
            "url"=>"/catalog/?productDelivery$sKey=".$arStore["ID"]
        ];
    }

    // Фильтр по флагам
    $arResult[] = [
        "category"=>"Хит", "url"=>"/catalog/?productHitCheckbox=1"
    ];
    $arResult[] = [
        "category"=>"Новинки", "url"=>"/catalog/?productNewCheckbox=2"
    ];
    $arResult[] = [
        "category"=>"Акции", "url"=>"/catalog/?productSaleCheckbox=3"
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
