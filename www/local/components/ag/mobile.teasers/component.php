<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//if ($this->StartResultCache(false,CUser::GetID())) {

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProduct.class.php"
    );
    use AGShop\Catalog as Catalog;
    
    $objProduct = new \Catalog\CCatalogProduct;
    
    // Получаем IDs продуктов, подходящих под условия
    $arProducts = $objProduct->getTeasers($arParams);$arResult["PRODUCTS"];
    $arResult["PRODUCTS"] = $arProducts["items"];
    
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
