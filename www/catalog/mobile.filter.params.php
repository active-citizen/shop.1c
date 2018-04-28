<?
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCache/CFilterSettings.class.php");
    use AGShop\Cache as Cache;
    
    $arParams = ["CACHE_TIME"      =>  COMMON_CACHE_TIME];
    $arParams["filter"] = [];
    $arParams["sorting"] = ["param" => 'fresh',"direction"=>"desc"];
    $arParams["pagination"] = ["page"=>1,"onpage"=>12];

    $tmp = explode("/",$_SERVER["REQUEST_URI"]);
    $sCode = '';
    if(isset($_REQUEST["section_code"]) && $_REQUEST["section_code"])
        $sCode = trim($_REQUEST["section_code"]);
    elseif(
        isset($tmp[1]) && $tmp[1]=='catalog'
        && isset($tmp[2]) && preg_match("#^[\w\d\_\-]+$#",$tmp[2])
    )$sCode = trim($tmp[2]);
    
    if(isset($_REQUEST['page']) && intval($_REQUEST['page']))
        $arParams["pagination"]["page"] = intval($_REQUEST['page']);
    if(isset($_REQUEST['onpage']) && intval($_REQUEST['onpage']))
        $arParams["pagination"]["onpage"] = intval($_REQUEST['onpage']);

    // Получение состояния фильтра из настроек пользователя
    $objFilterSettings = new \Cache\CFilterSettings($sCode);

    $arParams["filter"] =  $objFilterSettings->getFilter();
    $arParams["sorting"] = $objFilterSettings->getSorting();
    $arParams["smallicons"] = $objFilterSettings->getSmallIcons();
    if($sCode)
        $arParams["filter"]["section_code"] = $sCode;

    
    /*
    if($sCode && (!$_GET || count($_GET)==1) &&
    isset($_SESSION["TEASER_PAGINATION"][$sCode]))
        $arParams["pagination"] = $_SESSION["TEASER_PAGINATION"][$sCode];
    */

    //print_r($_SESSION["TEASER_FILTER"]);
    // print_r($arParams);
    
    

    if(isset($_REQUEST["mobileFiltersSubmit"])){
        
        if(count($_GET)>2 && isset($_REQUEST["productGridCheckbox"])){
            $arParams["smallicons"] = true;
    //      setcookie("smallicons",1);
        }
        elseif(count($_GET)>2) {
            $arParams["smallicons"] = false;
    //        setcookie("smallicons",0);
        }

        
        $arParams["filter"]["price_min"] = $_REQUEST["productPriceMin"];
        $arParams["filter"]["price_max"] = $_REQUEST["productPriceMax"];

        if(
            $arParams["filter"]["price_max"] && $arParams["filter"]["price_min"]
            &&
            $arParams["filter"]["price_max"] < $arParams["filter"]["price_min"]
        ){
            $nMinPrice = $arParams["filter"]["price_min"];
            $arParams["filter"]["price_min"] = $arParams["filter"]["price_max"];
            $arParams["filter"]["price_max"] = $nMinPrice;
        }

        if($arParams["filter"]["price_min"]<0)unset($arParams["filter"]["price_min"]);
        if($arParams["filter"]["price_max"]<0)unset($arParams["filter"]["price_max"]);
        
        $arParams["filter"]["interest"] = [];
        foreach($_REQUEST as $sKey=>$sValue)
            if(preg_match("#^productInterest#",$sKey))
                $arParams["filter"]["interest"][] = $sValue;
        $arParams["filter"]["interest"] = 
            array_unique ( $arParams["filter"]["interest"]);
               
        $arParams["filter"]["store"] = [];
        foreach($_REQUEST as $sKey=>$sValue)
            if(preg_match("#^productDelivery#",$sKey)){
                if(!isset($arParams["filter"]["store"]))
                    $arParams["filter"]["store"] = [];
                $arParams["filter"]["store"][] = $sValue;
            }
        $arParams["filter"]["store"] = 
            array_unique ($arParams["filter"]["store"]);
        
        if(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='price_desc'){
            $arParams["sorting"]["param"] = "price";
            $arParams["sorting"]["direction"] = "desc";
        }
        elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='rating_desc'){
            $arParams["sorting"]["param"] = "rating";
            $arParams["sorting"]["direction"] = "desc";
        }
        elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='fresh_desc'){
            $arParams["sorting"]["param"] = "fresh";
            $arParams["sorting"]["direction"] = "desc";
        }
        elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='price_asc'){
            $arParams["sorting"]["param"] = "price";
            $arParams["sorting"]["direction"] = "asc";
        }
        elseif(isset($_REQUEST["productSortPrice"]) && $_REQUEST["productSortPrice"]=='wants_desc'){
            $arParams["sorting"]["param"] = "wishes";
            $arParams["sorting"]["direction"] = "desc";
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
        
        $arParams["filter"]["hit"] = isset($_REQUEST["productHitCheckbox"]);
        $arParams["filter"]["new"] = isset($_REQUEST["productNewCheckbox"]);
        $arParams["filter"]["sale"] = isset($_REQUEST["productSaleCheckbox"]);

        $objFilterSettings->setFilter($arParams["filter"]);
        $objFilterSettings->setSorting($arParams["sorting"]);
        $objFilterSettings->setSmallIcons($arParams["smallicons"]);
    }

    /*
    if(!isset($_SESSION["TEASER_FILTER"]))$_SESSION["TEASER_FILTER"] = [];
    $_SESSION["TEASER_FILTER"][$sCode] = $arParams["filter"];

    if(!isset($_SESSION["TEASER_SORTING"]))$_SESSION["TEASER_SORTING"] = [];
    $_SESSION["TEASER_SORTING"][$sCode] = $arParams["sorting"];
    */

    /*
    if(!isset($_SESSION["TEASER_PAGINATION"]))$_SESSION["TEASER_PAGINATION"] = [];
    $_SESSION["TEASER_PAGINATION"][$sCode] = $arParams["pagination"];    
    */


    if(
        isset($tmp[1]) && $tmp[1]=='profile'
        && isset($tmp[2])  && $tmp[2]=='wishes'
    ){
        $arParams["filter"]["wishes_user"] = $USER->GetID();
    }
