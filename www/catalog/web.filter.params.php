<?

    $arParams = ["CACHE_TIME"      =>  COMMON_CACHE_TIME];
    $arParams["filter"] = [];
    $arParams["sorting"] = [];
    $arParams["pagination"] = ["page"=>1,"onpage"=>12];

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
    if($sCode && (!$_GET || count($_GET)==1) && isset($_SESSION["TEASER_FILTER"][$sCode]))
        $arParams["filter"] = $_SESSION["WEB_TEASER_FILTER"][$sCode];
   
    if(isset($_REQUEST['page']) && intval($_REQUEST['page']))
        $arParams["pagination"]["page"] = intval($_REQUEST['page']);
    if(isset($_REQUEST['onpage']) && intval($_REQUEST['onpage']))
        $arParams["pagination"]["onpage"] = intval($_REQUEST['onpage']);
    
    if(isset($_REQUEST["productPriceMin"]) && $_REQUEST["productPriceMin"])
        $arParams["filter"]["price_min"] = $_REQUEST["productPriceMin"];
    if(isset($_REQUEST["productPriceMax"]) && $_REQUEST["productPriceMax"])
        $arParams["filter"]["price_max"] = $_REQUEST["productPriceMax"];

    if(isset($_REQUEST["filter_interest"]))
        $arParams["filter"]["interest"] = $_REQUEST["filter_interest"];

    if(isset($_REQUEST["flag"]) && ($_REQUEST["flag"]=='actions'))
        $arParams["filter"]["sale"] = true;

    if(isset($_REQUEST["flag"]) && ($_REQUEST["flag"]=='news'))
        $arParams["filter"]["new"] = true;

    if(isset($_REQUEST["flag"]) && ($_REQUEST["flag"]=='populars'))
        $arParams["filter"]["hit"] = true;

    if(isset($_REQUEST["sorting"]) && ($_REQUEST["sorting"]=='rating-desc')){
        $arParams["sorting"]["param"] = 'hit';
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

    if(!isset($_SESSION["WEB_TEASER_FILTER"]))$_SESSION["WEB_TEASER_FILTER"] = [];
    $_SESSION["WEB_TEASER_FILTER"][$sCode] = $arParams["filter"];

    if(!isset($_SESSION["WEB_TEASER_SORTING"]))$_SESSION["TEASER_SORTING"] = [];
    $_SESSION["WEB_TEASER_SORTING"][$sCode] = $arParams["sorting"];
    
