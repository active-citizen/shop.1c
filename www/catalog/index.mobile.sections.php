<?
    /**
     * Формирование json для поиска
    */
    
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    require("mobile.filter.params.php");

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProductTag.class.php"
    );
    use AGShop\Catalog as Catalog;


    $objTag = new \Catalog\CCatalogProductTag(INTEREST_PROPERTY_ID);
    $arTags = $objTag->getAllTags();
    
    $arResult = [];
    foreach($arTags as $arTag)$arResult[] = [
        "category"=>$arTag["NAME"]
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
