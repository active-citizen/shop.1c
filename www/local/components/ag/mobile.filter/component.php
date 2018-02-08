<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//if ($this->StartResultCache(false,CUser::GetID())) {

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogStore.class.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProductTag.class.php"
    );

    $arResult["GRID"] = 
        isset($_REQUEST["productGridCheckbox"]) || (
            isset($_COOKIE["smallicons"])
            &&
            $_COOKIE["smallicons"]
        )
        ?
        true
        :
        false;
    $arResult["HIT"] = isset($_REQUEST["productHitCheckbox"])?true:false;
    $arResult["HIT"] = 
        isset($arParams["filter"]['hit'])
        &&
        $arParams["filter"]['hit']
        ?true:$arResult["HIT"];

    $arResult["NEW"] = isset($_REQUEST["productNewCheckbox"])?true:false;
    $arResult["NEW"] = 
        isset($arParams["filter"]['new'])
        &&
        $arParams["filter"]['new']
        ?true:$arResult["NEW"];

    $arResult["SALE"] = isset($_REQUEST["productSaleCheckbox"])?true:false;
    $arResult["SALE"] = 
        isset($arParams["filter"]['sale'])
        &&
        $arParams["filter"]['sale']
        ?true:$arResult["SALE"];
        

    $arResult["SORTING"] = [
        [
            "NAME"=>"Цена (дорогие сначала)",
            "VALUE"=>"price_desc",
            "CODE"=>"PriceHigh",
            "CLASSNAME"=>"icon-aside-filter--price"
        ],
        [
            "NAME"=>"Цена (дешевые сначала)",
            "VALUE"=>"price_asc",
            "CODE"=>"PriceLow",
            "CLASSNAME"=>"icon-aside-filter--price"
        ],
        [
            "NAME"=>"Рейтинг",
            "VALUE"=>"rating_desc",
            "CODE"=>"PriceRating",
            "CLASSNAME"=>"icon-aside-filter--rating"
        ],
        [
            "NAME"=>"Избранное",
            "VALUE"=>"wants_desc",
            "CODE"=>"PriceFavourite",
            "CLASSNAME"=>"icon-aside-filter--favourites"
        ],
        [
            "NAME"=>"Дата добавления",
            "VALUE"=>"fresh_desc",
            "CODE"=>"SortPriceFresh",
            "CLASSNAME"=>"icon-aside-filter--new"
        ],
        /*
        [
            "NAME"=>"Хит",
            "VALUE"=>"hit_desc",
            "CODE"=>"SortPriceHit",
            "CLASSNAME"=>"icon-aside-filter--hit"
        ],
        [
            "NAME"=>"Новинки",
            "VALUE"=>"new_desc",
            "CODE"=>"SortPriceNew",
            "CLASSNAME"=>"icon-aside-filter--new"
        ],
        [
            "NAME"=>"Акции",
            "VALUE"=>"sale_desc",
            "CODE"=>"SortPriceSale",
            "CLASSNAME"=>"icon-aside-filter--sale"
        ],
        */
    ];

    $bIsSorting = false;
    foreach($arResult["SORTING"] as $nKey=>$arSort){
        $tmp = explode("_",$arSort["VALUE"]);
        if(
            isset($tmp[0]) && isset($tmp[1])
            &&
            isset($arParams["sorting"]["param"])
            &&
            isset($arParams["sorting"]["direction"])
            &&
            $arParams["sorting"]["param"] == $tmp[0]
            &&
            $arParams["sorting"]["direction"] == $tmp[1]
        ){
            $bIsSorting = true;
            $arResult["SORTING"][$nKey]["CHECKED"] = true;
        }
        
        if(
            isset($_REQUEST["productSortPrice"]) 
            && $_REQUEST["productSortPrice"]==$arSort["VALUE"]
        )$arResult["SORTING"][$nKey]["CHECKED"] = true;
    }
    // Если сортировка не выбрана - ставим умолчальную
    if(!$bIsSorting)foreach($arResult["SORTING"] as $nKey=>$arVal)
        if($arVal["VALUE"]=='wants_desc')
            $arResult["SORTING"][$nKey]["CHECKED"] = true;

    
    /*******************************************
     * МФЦ
    ********************************************/
    use AGShop\Catalog as Catalog;
    $objStore = new \Catalog\CCatalogStore;
    $arStores = $objStore->getAllActive();
    $arResult["STORE_CHECKED"] = false;
    foreach($arStores as $nKey=>$arStore){
        $arStore["CODE"] = CUtil::translit($arStore["TITLE"],"ru",[
            "change_case"   =>  false,
            "replace_space" =>  "",
            "replace_other" =>  ""
        ]);
        $arStores[$nKey]["CODE"] = $arStore["CODE"];
        if(
            in_array($arStore["ID"], $arParams['filter']['store'])
            ||
            (
                isset($_REQUEST["productDelivery".$arStore["CODE"]])
                &&
                $_REQUEST["productDelivery".$arStore["CODE"]]==$arStore["ID"]
            )
        )
        $arResult["STORE_CHECKED"] = $arStores[$nKey]["CHECKED"] = true;
    }
    $arResult["STORES"] = $arStores;
   
    $objTag = new \Catalog\CCatalogProductTag(INTEREST_PROPERTY_ID);
    $arObjInterests = $objTag->getAllTags($arParams["filter"]["section_code"]);
    $arInterests = array_merge([[
            "NAME"  =>  "Все",
            "CODE"  =>  "All",
            "ID"    =>  0,
            "CLASS" => "dropdown-checkbox-all",
            "CHECKED"=> count($arParams["filter"]["interest"])?false:true
        ]],
        $arObjInterests
    );
   
    /*******************************************
     * Интересы
    ********************************************/
    $arResult["INTERESTS_CHECKED"] = false;
    foreach($arInterests as $nKey=>$arInterest){
        $arInterest["CODE"] = CUtil::translit($arInterest["NAME"],"ru",[
            "change_case"   =>  false,
            "replace_space" =>  "",
            "replace_other" =>  ""
        ]);
        $arInterests[$nKey]["CODE"] = $arInterest["CODE"];
        if(
            (
                isset($arParams['filter']['interest'])
                &&
                $arParams['filter']['interest']==$arInterest["ID"]
            )
            ||
            (
                isset($_REQUEST["productInterest".$arInterest["CODE"]])
                &&
                $_REQUEST["productInterest".$arInterest["CODE"]]==$arInterest["ID"]
            )
        )
        $arResult["INTERESTS_CHECKED"] = $arInterests[$nKey]["CHECKED"] = true;
    }
    $arResult["INTERESTS"] = $arInterests;
    
    
    if(isset($_REQUEST["productPriceMin"]) && intval($_REQUEST["productPriceMin"]))
        $arResult["MIN_PRICE"] = $_REQUEST["productPriceMin"];
    else
        $arResult["MIN_PRICE"] = '';
        
    $arResult["MIN_PRICE"] = 
        isset($arParams["filter"]['price_min'])
        &&
        $arParams["filter"]['price_min']
        ?$arParams["filter"]['price_min']:$arResult["MIN_PRICE"];
    

    if(isset($_REQUEST["productPriceMax"]) && intval($_REQUEST["productPriceMax"]))
        $arResult["MAX_PRICE"] = $_REQUEST["productPriceMax"];
    else
        $arResult["MAX_PRICE"] = '';

    $arResult["MAX_PRICE"] = 
        isset($arParams["filter"]['price_max'])
        &&
        $arParams["filter"]['price_max']
        ?$arParams["filter"]['price_max']:$arResult["MAX_PRICE"];



    $this->IncludeComponentTemplate();
//}
