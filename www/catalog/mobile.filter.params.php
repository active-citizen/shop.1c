<?

    $arParams = ["CACHE_TIME"      =>  COMMON_CACHE_TIME];
    $arParams["filter"] = [];
    $arParams["sorting"] = ["param" => 'wishes',"direction"=>"desc"];
    $arParams["pagination"] = ["page"=>1,"onpage"=>12];

    $tmp = explode("/",$_SERVER["REQUEST_URI"]);
    if(isset($_REQUEST["section_code"]) && $_REQUEST["section_code"])
        $arParams["filter"]["section_code"] = trim($_REQUEST["section_code"]);
    elseif(
        isset($tmp[1]) && $tmp[1]=='catalog'
        && isset($tmp[2]) && preg_match("#^[\w\d\_\-]+$#",$tmp[2])
    )$arParams["filter"]["section_code"] = trim($tmp[2]);
    
    $sCode = '';
    if(isset($arParams["filter"]["section_code"]))
        $sCode = $arParams["filter"]["section_code"];
    if($sCode && (!$_GET || count($_GET)==1) && isset($_SESSION["TEASER_FILTER"][$sCode]))
        $arParams["filter"] = $_SESSION["TEASER_FILTER"][$sCode];
    
    if(isset($_REQUEST['page']) && intval($_REQUEST['page']))
        $arParams["pagination"]["page"] = intval($_REQUEST['page']);
    if(isset($_REQUEST['onpage']) && intval($_REQUEST['onpage']))
        $arParams["pagination"]["onpage"] = intval($_REQUEST['onpage']);
    
    if(isset($_REQUEST["productPriceMin"]) && $_REQUEST["productPriceMin"])
        $arParams["filter"]["price_min"] = $_REQUEST["productPriceMin"];
    if(isset($_REQUEST["productPriceMax"]) && $_REQUEST["productPriceMax"])
        $arParams["filter"]["price_max"] = $_REQUEST["productPriceMax"];
        
        
    foreach($_REQUEST as $sKey=>$sValue)
        if(preg_match("#^productInterest#",$sKey))
            $arParams["filter"]["interest"] = $sValue;
            
    foreach($_REQUEST as $sKey=>$sValue)
        if(preg_match("#^productDelivery#",$sKey)){
            if(!isset($arParams["filter"]["store"]))
                $arParams["filter"]["store"] = [];
            $arParams["filter"]["store"][] = $sValue;
        }
    
    if(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='price_desc'){
        $arParams["sorting"]["param"] = "price";
        $arParams["sorting"]["direction"] = "desc";
    }
    elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='price_asc'){
        $arParams["sorting"]["param"] = "price";
        $arParams["sorting"]["direction"] = "asc";
    }
    elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='hit_desc'){
        $arParams["sorting"]["param"] = "hit";
        $arParams["sorting"]["direction"] = "desc";
    }
    elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='new_desc'){
        $arParams["sorting"]["param"] = "new";
        $arParams["sorting"]["direction"] = "desc";
    }
    elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='sale_desc'){
        $arParams["sorting"]["param"] = "sale";
        $arParams["sorting"]["direction"] = "desc";
    }
    if($sCode && (!$_GET || count($_GET)==1) && isset($_SESSION["TEASER_SORTING"][$sCode]))
        $arParams["sorting"] = $_SESSION["TEASER_SORTING"][$sCode];
    
    if(isset($_REQUEST["productHitCheckbox"]))$arParams["filter"]["hit"] = 1;
    if(isset($_REQUEST["productNewCheckbox"]))$arParams["filter"]["new"] = 1;
    if(isset($_REQUEST["productSaleCheckbox"]))$arParams["filter"]["sale"] = 1;
    
    if(!isset($_SESSION["TEASER_FILTER"]))$_SESSION["TEASER_FILTER"] = [];
    $_SESSION["TEASER_FILTER"][$sCode] = $arParams["filter"];

    if(!isset($_SESSION["TEASER_SORTING"]))$_SESSION["TEASER_SORTING"] = [];
    $_SESSION["TEASER_SORTING"][$sCode] = $arParams["sorting"];
    
