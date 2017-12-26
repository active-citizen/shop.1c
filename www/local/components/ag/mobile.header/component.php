<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//if ($this->StartResultCache(false,CUser::GetID())) {

    // Соответствие кодов категорий классам css
    $sSectionIconDefault = "icon-header-category--else";
    $arIconsClasses = [
        "muzei"=>"icon-header-category--museum",
        "suveniry"=>"icon-header-category--souvenirs",
        "meropriyatiya"=>"icon-header-category--events",
        "elektronnyy-kontent"=>"icon-header-category--web",
        "transport"=>"icon-header-category--transport",
    ];
    $arResult = [];
    
    // Ссылки на разделы сайта
    $arPages = [
        [
            "URL"   =>  "/profile/order/",
            "CLASSNAME"=>"icon-header-other--orders",
            "TITLE" =>  "Мои заказы"
        ],
        [
            "URL"   =>  "/profile/wishes/",
            "CLASSNAME"=>"icon-header-other--favourite",
            "TITLE" =>  "Избранное"
        ],
        [
            "URL"   =>  "/rules/hiw/",
            "CLASSNAME"=>"icon-header-other--rules",
            "TITLE" =>  "Правила"
        ],
        [
            "URL"   =>  "/rules/stores/",
            "CLASSNAME"=>"icon-header-other--adreses",
            "TITLE" =>  "Адреса"
        ],
        [
            "URL"   =>  "/rules/faq/",
            "CLASSNAME"=>"icon-header-other--faq",
            "TITLE" =>  "FAQ"
        ],
    ];
    foreach($arPages as $nKey=>$arPage)
        if(preg_match("#^".$arPage["URL"]."#",$_SERVER["REQUEST_URI"]))
            $arPages[$nKey]["CURRENT"] = true;
        


    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogSection.class.php"
    );
    use AGShop\Catalog as Catalog;
    
    $objSection = new \Catalog\CCatalogSection;
    $arSections = $objSection->get([
        "ACTIVE"=>"Y",
        "ONLY_WITH_PRODUCTS"=>true,
        "ONLY_WITH_PRESENT_PRODUCTS"=>true
    ]);
    $arResult["CURRENT_SECTION"] = '';
    foreach($arSections as $nKey=>$arSection){
        if(isset($arIconsClasses[$arSection["CODE"]]))
            $arSections[$nKey]["CLASSNAME"]=$arIconsClasses[$arSection["CODE"]];
        else
            $arSections[$nKey]["CLASSNAME"]=$sSectionIconDefault;
        if(preg_match("#^/catalog/".$arSection["CODE"]."/#",$_SERVER["REQUEST_URI"])){
            $arSections[$nKey]["CURRENT"]=true;
            $arResult["CURRENT_SECTION"] = $arSection["CODE"];
        }
        
    }
    
    $arResult["SECTIONS"] = $arSections;
    $arResult["PAGES"] = $arPages;

    $this->IncludeComponentTemplate();
//}
