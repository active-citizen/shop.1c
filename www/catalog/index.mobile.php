<?
    $arParams = ["CACHE_TIME"      =>  COMMON_CACHE_TIME];
    $arParams["filter"] = [];
    $arParams["pagination"] = ["offset"=>0,"onpage"=>12];
    if(isset($_REQUEST["productPriceMin"]) && $_REQUEST["productPriceMin"])
        $arParams["filter"]["price_min"] = $_REQUEST["productPriceMin"];
    if(isset($_REQUEST["productPriceMax"]) && $_REQUEST["productPriceMax"])
        $arParams["filter"]["price_max"] = $_REQUEST["productPriceMax"];
        
        
    foreach($_REQUEST as $sKey=>$sValue)
        if(preg_match("#^productInterest#",$sKey))
            $arParams["filter"]["interest"] = $sValue;
            
    foreach($_REQUEST as $sKey=>$sValue)
        if(preg_match("#^productDelivery#",$sKey))
            $arParams["filter"]["store"] = $sValue;

    $tmp = explode("/",$_SERVER["REQUEST_URI"]);
    if(isset($tmp[3]))$arParams["filter"]["section_code"] = trim($tmp[3]);
    
    if(isset($_REQUEST["productHitCheckbox"]))$arParams["filter"]["hit"] = 1;
    if(isset($_REQUEST["productNewCheckbox"]))$arParams["filter"]["new"] = 1;
    if(isset($_REQUEST["productSaleCheckbox"]))$arParams["filter"]["sale"] = 1;

        
    $APPLICATION->IncludeComponent(
        "ag:mobile.teasers", 
        "", 
        $arParams,
        false
    );
?>



