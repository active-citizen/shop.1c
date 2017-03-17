<?
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    CModule::IncludeModule("iblock");

    // Узнаём ID инфоблока
    $res = CIBlock::GetList(array(),array("CODE"=>"clothes"));
    $arrIblock = $res->GetNext();
    $ibId = $arrIblock["ID"];
    $arrFilter["IBLOCK_ID"] = $ibId;
    
    $res = CIBlockElement::GetList(
        array(),
        $arrFilter,
        false,
        false,
        array()
    );

    $resOffersIblock = CIBlockElement::GetList(array(),array("CODE"=>"clothes_offers"));
    $arrIblock = $resOffersIblock->GetNext();
    
    
    
    while($product = $res->GetNext()){
        
        $resOffer = CIBlockElement::GetList(array(),array("IBLOCK_ID"=>3,"PROPERTY_CML2_LINK"=>$product["ID"]),false,array("nTopCount"=>1),array("PROPERTY_CML2_LINK","CATALOG_GROUP_1"));
        $offer = $resOffer->GetNext();
        CIBlockElement::SetPropertyValueCode($product["ID"],"MINIMUM_PRICE",$offer["CATALOG_PRICE_1"]);
    }



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>