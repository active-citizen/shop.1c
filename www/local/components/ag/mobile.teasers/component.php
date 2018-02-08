<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//if ($this->StartResultCache(false,CUser::GetID())) {

    $bIsBack = false;
    if(
        isset($arParams["pagination"]["page"])
        && intval($arParams["pagination"]["page"])>1
        && isset($arParams["pagination"]["onpage"])
        && intval($arParams["pagination"]["onpage"])
        && (
            !isset($arParams["AJAX"])
            ||
            !$arParams["AJAX"]
        )
    ){
        $bIsBack = true;
    }

    

    if($bIsBack){
        $arParams["pagination"]["original_page"] = 
            $arParams["pagination"]["page"];
        $arParams["pagination"]["original_onpage"] = 
            $arParams["pagination"]["onpage"];

        $arParams["pagination"]["onpage"] = 
            $arParams["pagination"]["page"]*$arParams["pagination"]["onpage"];
        $arParams["pagination"]["page"]=1;
    }

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProduct.class.php"
    );
    use AGShop\Catalog as Catalog;
    
    $objProduct = new \Catalog\CCatalogProduct;
    
    // Получаем IDs продуктов, подходящих под условия
    $arProducts = $objProduct->getTeasers($arParams);$arResult["PRODUCTS"];
    $arResult["PRODUCTS"] = $arProducts["items"];
    
    if($bIsBack){

        $arParams["pagination"]["page"] = 
             $arParams["pagination"]["original_page"];
        $arParams["pagination"]["onpage"] = 
             $arParams["pagination"]["original_onpage"];

    }

    $arResult["PRODUCT_IDS"] = [];
    foreach($arResult["PRODUCTS"] as $arProduct)$arResult["PRODUCT_IDS"][] = 
        $arProduct["ID"];
    $arResult["TOTAL"] = $arProducts["total"];
    $arResult["PAGE"] = $arParams["pagination"]["page"];
    $arResult["ONPAGE"] = $arParams["pagination"]["onpage"];
    
    if(isset($_REQUEST["productGridCheckbox"]))$arResult["SMALL_TEASERS"] = 1;
    
    
    $sUrl = $_SERVER["QUERY_STRING"];
    $sUrl = preg_replace("#&page=\d+#","", $sUrl);
    $sUrl = preg_replace("#page=\d+#","", $sUrl);
    $sUrl = preg_replace("#&section_code=[\w\d\_\-\.]+#","", $sUrl);
    $sUrl = preg_replace("#section_code=[\w\d\_\-\.]+#","", $sUrl);
    $sUrl.='&page='.($arParams["pagination"]["page"]+1);
    $sUrl.="&section_code=".$arParams["filter"]["section_code"];
    $arResult["NEXT_PAGE_URL"] = $sUrl;


    $this->IncludeComponentTemplate();
//}
