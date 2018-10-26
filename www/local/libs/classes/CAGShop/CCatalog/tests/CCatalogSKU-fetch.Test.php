<?php
require_once(realpath(__DIR__."/..")."/CCatalogSKU.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogSKU_Fetch_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testFetch(){
        $objSKU = new \Catalog\CCatalogSKU;
        $this->assertTrue(boolval($objSKU));
        
        // Получение активного раздела
        $arSection = \CIBlockSection::GetList(["id"=>"desc"],[
            "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
            "ACTIVE"=>"Y"
        ],false,[
            "ACTIVE","ID","NAME",
        ])->Fetch();
        
        // Получение активного товара
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objSKU->IBLOCKS["CATALOG"],
                "IBLOCK_SECTION_ID"=>$arSection["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();
        
        // Получение активного предложения
        $arOffer = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objSKU->IBLOCKS["OFFER"],
                "PROPERTY_CML2_LINK"=>$arProduct["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();

        // Fetch только извлекает данные о SKU из БД, получает их GET
////        $this->assertTrue(boolval($objSKU->fetch($arOffer["ID"])));
        
        $arSKU = $objSKU->get();
////        $this->assertTrue(boolval(
////            $arSKU["PRODUCT"]["ID"]==$arProduct["ID"]
////        ));
////        $this->assertTrue(boolval(
////            $arSKU["OFFER"]["ID"]==$arOffer["ID"]
////        ));
////        $this->assertTrue(boolval(
////            $arSKU["PROPERTIES"]["CML2_LINK"]==$arProduct["ID"]
////        ));
////        $this->assertTrue(boolval(isset($arSKU['STORES'])));
        
    }
}
