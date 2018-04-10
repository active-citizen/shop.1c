<?
    /**
        Сопряжение URL-параметров фильрации и сортировки для 
        плитки desktop-версии
    */
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCache/CFilterSettings.class.php");
    use AGShop\Cache as Cache;

    $arParams = ["CACHE_TIME"      =>  COMMON_CACHE_TIME];
    $arParams["pagination"] = ["page"=>1,"onpage"=>12];

    // Определяем код раздела
    $tmp = explode("/",$_SERVER["REQUEST_URI"]);
    if(isset($_REQUEST["catalog_name"]) && $_REQUEST["catalog_name"])
        $arParams["filter"]["section_code"] = trim($_REQUEST["catalog_name"]);
    elseif(isset($_REQUEST["section_code"]) && $_REQUEST["section_code"]){
        $arParams["filter"]["section_code"] = trim($_REQUEST["section_code"]);
    }
    elseif(
        isset($tmp[1]) && $tmp[1]=='catalog'
        && isset($tmp[2]) && preg_match("#^[\w\d\_\-]+$#",$tmp[2])
    )$arParams["filter"]["section_code"] = trim($tmp[2]);

    $sCode = '';
    if(isset($arParams["filter"]["section_code"]))
        $sCode = $arParams["filter"]["section_code"];

     // Загружаем параметры фильтрации и сортировки из сессии
    /*
    if($sCode && (!$_GET || count($_GET)==1) && isset($_SESSION["WEB_TEASER_FILTER"][$sCode]))
        $arParams["filter"] = $_SESSION["WEB_TEASER_FILTER"][$sCode];
    if($sCode && (!$_GET || count($_GET)==1) &&
    isset($_SESSION["WEB_TEASER_SORTING"][$sCode]))
        $arParams["sorting"] = $_SESSION["WEB_TEASER_SORTING"][$sCode];
    */
    
    // Определяем номер страницы
    if(isset($_REQUEST['page']) && intval($_REQUEST['page']))
        $arParams["pagination"]["page"] = intval($_REQUEST['page']);
    if(isset($_REQUEST['onpage']) && intval($_REQUEST['onpage']))
        $arParams["pagination"]["onpage"] = intval($_REQUEST['onpage']);
    
    // Получение состояния фильтра из настроек пользователя
    $objFilterSettings = new
    \Cache\CFilterSettings($sCode,$USER->GetID(),'desktop');

    $arFilterCache = $objFilterSettings->getFilter();
    if($arFilterCache)$arParams["filter"]=$arFilterCache;
    
    $arSortingCache = $objFilterSettings->getSorting();
    if($arSortingCache) $arParams["sorting"] = $arSortingCache;

    $arSmalliconsCache = $objFilterSettings->getSmallIcons();
    if($arSmalliconsCache) $arParams["smallicons"] = $arSmalliconsCache;

    if(isset($_REQUEST["form"]) && $_REQUEST["form"]=='filter'){

        $arParams["filter"] = ["not_exists"=>false];
        $arParams["sorting"] = ["param" => 'wishes',"direction"=>"desc"];
        
        if($sCode)$arParams["filter"]["section_code"] = $sCode;

        // Определяем минимальную и максимальную цены
        if(isset($_REQUEST["productPriceMin"]) && $_REQUEST["productPriceMin"])
            $arParams["filter"]["price_min"] = $_REQUEST["productPriceMin"];
        if(isset($_REQUEST["productPriceMax"]) && $_REQUEST["productPriceMax"])
            $arParams["filter"]["price_max"] = $_REQUEST["productPriceMax"];

        // Определяем список интересов
        $arParams['filter']['interest'] = [];
        foreach($_REQUEST as $sKey=>$nValue)
            if(preg_match("#^interest.*?#i", $sKey) && intval($nValue))
                $arParams["filter"]["interest"][] = $nValue;
        if(!$arParams["filter"]["interest"])unset($arParams["filter"]["interest"]);

        // Определяем список складов
        $arParams['filter']['store'] = [];
        foreach($_REQUEST as $sKey=>$nValue)
            if(preg_match("#^delivery.*?#i", $sKey) && intval($nValue))
                $arParams["filter"]["store"][] = $nValue;
        if(!$arParams["filter"]["store"])unset($arParams["filter"]["store"]);


        // Определяем флаги 
        if(isset($_REQUEST["showProductsSale"]) && $_REQUEST["showProductsSale"])
            $arParams["filter"]["sale"] = true;
        if(isset($_REQUEST["showProductsNew"]) && $_REQUEST["showProductsNew"])
            $arParams["filter"]["new"] = true;
        if(isset($_REQUEST["showProductsHit"]) && $_REQUEST["showProductsHit"])
            $arParams["filter"]["hit"] = true;


        // Ставим флаг "только в наличии"
        if(isset($_REQUEST["showProductsAll"]) && $_REQUEST["showProductsAll"])
            $arParams["filter"]["not_exists"] = true;

        if(isset($_REQUEST["sorting"]) && ($_REQUEST["sorting"]=='rating-desc')){
            $arParams["sorting"]["param"] = 'wishes';
            $arParams["sorting"]["direction"] = 'desc';
        }

        if(isset($_REQUEST["sorting"]) && ($_REQUEST["sorting"]=='price-desc')){
            $arParams["sorting"]["param"] = 'price';
            $arParams["sorting"]["direction"] = 'desc';
        }

        if(isset($_REQUEST["sorting"]) && ($_REQUEST["sorting"]=='price-asc')){
            $arParams["sorting"]["param"] = 'price';
            $arParams["sorting"]["direction"] = 'asc';
        }

        if(isset($_REQUEST["sorting"]) && ($_REQUEST["sorting"]=='fresh-desc')){
            $arParams["sorting"]["param"] = 'fresh';
            $arParams["sorting"]["direction"] = 'desc';
        }

        $objFilterSettings->setFilter($arParams["filter"]);
        $objFilterSettings->setSorting($arParams["sorting"]);
        $objFilterSettings->setSmallIcons($arParams["smallicons"]);
    }


