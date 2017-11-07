<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


if ($this->StartResultCache(false)) {

    // Получаем список банеров
    $res = CIBlockElement::GetList(array("SORT"=>"ASC"),array(
        "ACTIVE"        =>  "Y",
        "IBLOCK_CODE"   =>  "baners_on_main",
        "ACTIVE_DATE"   =>  "Y"
    ),false,false);
    
    
    $arResult["BANNERS"] = array();
    while($baner = $res->getNext()){
        $arResult["BANNERS"][$baner["ID"]] = $baner;
        $arResult["BANNERS"][$baner["ID"]]["PROPERTIES"] = array();
        $res1 = CIBlockElement::GetProperty($baner["IBLOCK_ID"],$baner["ID"]);
        while($prop = $res1->getNext()){
            if($prop["PROPERTY_TYPE"]=='F')$prop["URL"] = CFile::GetPath($prop["VALUE"]);
            $arResult["BANNERS"][$baner["ID"]]["PROPERTIES"][$prop["CODE"]] = $prop;
            $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"] = array();
            if($prop["CODE"]=='BANER_CATALOG_LINK' && $prop["VALUE"]){
                $resCatalogLinkItem = CIBlockElement::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"   =>  "clothes",
                        "ID"            =>  $prop["VALUE"]
                    ),false,array("nTopCount"=>1),array(
                        "ID",
                        "PROPERTY_MINIMUM_PRICE",
                        "PROPERTY_RATING",
                        "PROPERTY_NEWPRODUCT",
                        "PROPERTY_SALELEADER",
                        "PROPERTY_SPECIALOFFER",
                        "PREVIEW_TEXT",
                        "PREVIEW_PICTURE",
                        "IBLOCK_SECTION_ID",
                        "NAME",
                        "DETAIL_PAGE_URL"
                    )
                );
                $arCatalogLinkItem = $resCatalogLinkItem->GetNext();

                // ID продукт
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["ID"] = 
                    $arCatalogLinkItem["ID"];
                // Вычисляем цену
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["PRICE"] = 
                    $arCatalogLinkItem["PROPERTY_MINIMUM_PRICE_VALUE"];
                // Вычисляем ссылку
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["URL"] = 
                    $arCatalogLinkItem["DETAIL_PAGE_URL"];
                // Вычисляем рейтинг
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["RATING"] = 
                    round($arCatalogLinkItem["PROPERTY_RATING_VALUE"],2);
                // Вычисляем ИМЯ
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["NAME"] = 
                    $arCatalogLinkItem["NAME"];
                // Вычисляем новинку
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["NEWPRODUCT"] = 
                    $arCatalogLinkItem["PROPERTY_NEWPRODUCT_VALUE"];
                // Вычисляем хит продаж
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["SALELEADER"] = 
                    $arCatalogLinkItem["PROPERTY_SALELEADER_VALUE"];
                // Вычисляем хит спецпредложение
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["SPECIALOFFER"] = 
                    $arCatalogLinkItem["PROPERTY_SPECIALOFFER_VALUE"];
                // Вычисляем описание
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["PREVIEW_TEXT"] = 
                    $arCatalogLinkItem["PREVIEW_TEXT"];
                // Вычисляем адрес картинки
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["PREVIEW_PICTURE"] = 
                    CFile::GetPath($arCatalogLinkItem["PREVIEW_PICTURE"]);
                    
                // Вычисляем хотелки
                $resWishItem = CIBlockElement::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"           =>  "whishes",
                        "PROPERTY_WISH_PRODUCT" =>  $prop["VALUE"]
                    ),
                    false,array(),array("ID")
                );
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["WISHES"] = 
                    $resWishItem->SelectedRowsCount();
                // Вычисляем моя ли это хотелка
                $resWishItem = CIBlockElement::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"           =>  "whishes",
                        "PROPERTY_WISH_PRODUCT" =>  $prop["VALUE"],
                        "PROPERTY_WISH_USER"    =>  $USER->GetID(),
                    ),
                    false,array(),array("ID")
                );
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["MY_WISH"] = 
                    $resWishItem->GetNext()?1:0;
                    
                // Вычисляем раздел
                $resCatalogSection = CIBlockSection::GetList(
                    array(),
                    array(
                        "IBLOCK_CODE"   =>  "clothes",
                        "ID"=>$arCatalogLinkItem["IBLOCK_SECTION_ID"]
                    ),
                    false,
                    array("nTopCount"=>1),
                    array("NAME")
                );
                $arCatalogSection = $resCatalogSection->GetNext();
                $arResult["BANNERS"][$baner["ID"]]["CATALOG_LINK_DATA"]["SECTION_NAME"] = 
                    $arCatalogSection["NAME"];
            }
        }
    }

    // Троеточим описания
    $nMaxTextLength = 350;
    foreach($arResult["BANNERS"] as $nBannerId => $arBanner){
        $sText = $arBanner["PROPERTIES"]["BANER_DESC"]["VALUE"];
        $sNewText = '';
        $arWords = explode(" ",$sText);
        foreach($arWords as $sWord){
            if(mb_strlen($sNewText)+mb_strlen($sWord)>$nMaxTextLength)break;
            $sNewText .=" ".$sWord;
        }
        $arResult["BANNERS"][$nBannerId]["PROPERTIES"]["BANER_DESC"]["VALUE"] =
            $sNewText."...";
       
    }

    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/integrationSettings.class.php");

    $objSettings = new CIntegrationSettings;
    $objSettings->code = 'BANNERS';
    $arSettings = $objSettings->get();

    $arResult["AUTOPLAY_DELAY"] =
       $arSettings["BANNERS_CHANGE_TIME"]["VALUE"]*1000;

    $this->IncludeComponentTemplate();
}

