<?php
require_once(realpath(__DIR__."/..")."/CCatalogProperties.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProperties_GetById_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetById(){
        $objProperty = new \Catalog\CCatalogProperties;
        $this->assertTrue(boolval($objProperty));
        
        // Получение активного товара
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objProperty->IBLOCKS["CATALOG"],
                "!PROPERTY_MINIMUM_PRICE"=>false,
                "!IBLOCK_SECTION_ID"=>false,
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME","PROPERTY_MINIMUM_PRICE"]
        )->Fetch();
        
        $arProperties = $objProperty->getById($arProduct["ID"]);
        $this->assertTrue(boolval(
            $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]
            ==
            $arProperties["MINIMUM_PRICE"]
        ));
        
        // Получение активного предложения
        $arOffer = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objProperty->IBLOCKS["OFFERS"],
                "PROPERTY_CML2_LINK"=>$arProduct["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME","PROPERTY_CML2_LINK"]
        )->Fetch();
        
        $arProperties = $objProperty->getById($arOffer["ID"]);
        $this->assertTrue(boolval(
            $arOffer["PROPERTY_CML2_LINK_VALUE"]
            ==
            $arProduct["ID"]
        ));
        $this->assertTrue(boolval(
            $arOffer["PROPERTY_CML2_LINK_VALUE"]
            ==
            $arProperties["CML2_LINK"]
        ));
        
    }
}
