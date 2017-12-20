<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//if ($this->StartResultCache(false,CUser::GetID())) {

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProduct.class.php"
    );
    use AGShop\Catalog as Catalog;
    
    $objProduct = new \Catalog\CCatalogProduct;
    
    // Получаем IDs продуктов, подходящих под условия
    $arProductIds = $objProduct->getTeasers($arParams);
    $arResult["PRODUCTS"] = $objProduct->getProductsForTeasersByIds(
        $arProductIds["items"]
    );

    $this->IncludeComponentTemplate();
//}
