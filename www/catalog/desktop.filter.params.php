<?
    /**
        Сопряжение URL-параметров фильрации и сортировки для 
        плитки desktop-версии
    */

    $arParams = ["CACHE_TIME"      =>  COMMON_CACHE_TIME];
    $arParams["filter"] = [];
    $arParams["sorting"] = ["param" => 'wishes',"direction"=>"desc"];
    $arParams["pagination"] = ["page"=>1,"onpage"=>12];

    // Определяем код раздела
    $tmp = explode("/",$_SERVER["REQUEST_URI"]);
    if(isset($_REQUEST["catalog_name"]) && $_REQUEST["catalog_name"])
        $arParams["filter"]["section_code"] = trim($_REQUEST["catalog_name"]);
    elseif(
        isset($tmp[1]) && $tmp[1]=='catalog'
        && isset($tmp[2]) && preg_match("#^[\w\d\_\-]+$#",$tmp[2])
    )$arParams["filter"]["section_code"] = trim($tmp[2]);
    
    $sCode = '';
    if(isset($arParams["filter"]["section_code"]))
        $sCode = $arParams["filter"]["section_code"];

    // Загружаем параметры фильтрации и сортировки из сессии
    if($sCode && (!$_GET || count($_GET)==1) && isset($_SESSION["WEB_TEASER_FILTER"][$sCode]))
        $arParams["filter"] = $_SESSION["WEB_TEASER_FILTER"][$sCode];
    if($sCode && (!$_GET || count($_GET)==1) &&
    isset($_SESSION["WEB_TEASER_SORTING"][$sCode]))
        $arParams["sorting"] = $_SESSION["WEB_TEASER_SORTING"][$sCode];
    
    // Определяем номер страницы
    if(isset($_REQUEST['page']) && intval($_REQUEST['page']))
        $arParams["pagination"]["page"] = intval($_REQUEST['page']);
    if(isset($_REQUEST['onpage']) && intval($_REQUEST['onpage']))
        $arParams["pagination"]["onpage"] = intval($_REQUEST['onpage']);
    
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
        $arParams["filter"]["only_exists"] = true;
    else
        $arParams["filter"]["only_exists"] = false;

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

    if(!isset($_SESSION["WEB_TEASER_FILTER"]))$_SESSION["WEB_TEASER_FILTER"] = [];
    $_SESSION["WEB_TEASER_FILTER"][$sCode] = $arParams["filter"];

    if(!isset($_SESSION["WEB_TEASER_SORTING"]))$_SESSION["WEB_TEASER_SORTING"] = [];
    $_SESSION["WEB_TEASER_SORTING"][$sCode] = $arParams["sorting"];

