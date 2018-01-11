<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCatalog/CCatalogStore.class.php"
);
use AGShop\Catalog as Catalog; 

if ($this->StartResultCache(false)) {
    $objStore = new \Catalog\CCatalogStore;
    $arResult = $objStore->getForSite();
    $this->IncludeComponentTemplate();
}
