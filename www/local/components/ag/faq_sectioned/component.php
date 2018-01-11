<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CContent/CContent.class.php"
);
use AGShop\Content as Content;

if ($this->StartResultCache(false)) {
    // Фильтр для разделов и элементов
    $nSectionId = intval($arParams["SECTION_ID"]);
   
    $objContent = new \Content\CContent;
    $arResult = $objContent->getFAQForSite($nSectionId);

    $this->IncludeComponentTemplate();
}
