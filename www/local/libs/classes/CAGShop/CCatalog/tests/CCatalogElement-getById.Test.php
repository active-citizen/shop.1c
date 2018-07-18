<?php
require_once(realpath(__DIR__."/..")."/CCatalogElement.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogElement_GetById_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetById(){
        $objElement = new \Catalog\CCatalogElement();
        $this->assertTrue(boolval($objElement));
        
        // Получение активного товара
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objElement->IBLOCKS["CATALOG"],
                "!IBLOCK_SECTION_ID"=>false,
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","CODE"]
        )->Fetch();
        
        // Получение активного предложения
        $arOffer = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objElement->IBLOCKS["OFFERS"],
                "PROPERTY_CML2_LINK"=>$arProduct["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","CODE"]
        )->Fetch();
        
        $arElementProduct = $objElement->getById($arProduct["ID"]);
        $arElementOffer = $objElement->getById($arOffer["ID"]);
        
        $this->assertTrue(boolval($arElementProduct["ID"]==$arProduct["ID"]));
        $this->assertTrue(boolval($arElementProduct["ACTIVE"]==$arProduct["ACTIVE"]));
        $this->assertTrue(boolval($arElementProduct["CODE"]==$arProduct["CODE"]));
        
        $this->assertTrue(boolval($arElementOffer["ID"]==$arOffer["ID"]));
        $this->assertTrue(boolval($arElementOffer["ACTIVE"]==$arOffer["ACTIVE"]));
        $this->assertTrue(boolval($arElementOffer["CODE"]==$arOffer["CODE"]));

    }
}
