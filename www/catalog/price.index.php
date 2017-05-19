<?
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    CModule::IncludeModule("iblock");

    $arrFilter["IBLOCK_ID"] = CATALOG_IB_ID;
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
        
        $resOffer = CIBlockElement::GetList(
            array(),
            array("IBLOCK_ID"=>OFFER_IB_ID,"PROPERTY_CML2_LINK"=>$product["ID"]),
            false,
            array("nTopCount"=>1),
            array("PROPERTY_CML2_LINK","CATALOG_GROUP_1")
        );
        $offer = $resOffer->GetNext();
        CIBlockElement::SetPropertyValueCode($product["ID"],"MINIMUM_PRICE",$offer["CATALOG_PRICE_1"]);
    }



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
