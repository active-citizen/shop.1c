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
    $arResult["TOTAL"] = $arProducts["total"];
    
    if(isset($_REQUEST["productGridCheckbox"]))$arResult["SMALL_TEASERS"] = 1;

    $this->IncludeComponentTemplate();
//}
