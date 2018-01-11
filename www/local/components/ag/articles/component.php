<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CContent/CContent.class.php"
);
use AGShop\Content as Content;


if ($this->StartResultCache(false)) {

    if(!trim($arParams["CODE"]))$arParams["CODE"] = 'hiw';

    $objContent = new \Content\CContent;
    $arResult = $objContent->getArticleForSite($arParams["CODE"]);
    $this->IncludeComponentTemplate();
}
