<?php
require_once(realpath(__DIR__."/..")."/CCatalogProduct.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProduct_IsActive_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testIsActive(){
        $objProduct = new \Catalog\CCatalogProduct;
        $this->assertTrue(boolval($objProduct));
        
        // Получение активного раздела
        $arSection = \CIBlockSection::GetList(["id"=>"desc"],[
            "IBLOCK_ID"=>$objProduct->IBLOCKS["CATALOG"],
            "ACTIVE"=>"Y"
        ],false,[
            "ACTIVE","ID","NAME",
        ])->Fetch();
        
        // Получение активного товара
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objProduct->IBLOCKS["CATALOG"],
                "IBLOCK_SECTION_ID"=>$arSection["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME","IBLOCK_ID"]
        )->Fetch();
        $this->assertTrue(boolval($objProduct->isActive($arProduct["ID"])));
        
        
        // Получение НЕактивного раздела
        $arSection = \CIBlockSection::GetList(["id"=>"desc"],[
            "IBLOCK_ID"=>$objProduct->IBLOCKS["CATALOG"],
            "ACTIVE"=>"N"
        ],false,[
            "ACTIVE","ID","NAME",
        ])->Fetch();
        
        // Получение активного товара из НЕактивного раздела
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objProduct->IBLOCKS["CATALOG"],
                "IBLOCK_SECTION_ID"=>$arSection["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();
        
        $this->assertFalse(boolval($objProduct->isActive($arProduct["ID"])));


        // Получение НЕактивного раздела
        $arSection = \CIBlockSection::GetList(["id"=>"desc"],[
            "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
            "ACTIVE"=>"N"
        ],false,[
            "ACTIVE","ID","NAME",
        ])->Fetch();
        
        // Получение НЕактивного товара из НЕактивного раздела
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
                "IBLOCK_SECTION_ID"=>$arSection["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();
        
        $this->assertFalse(boolval($objProduct->isActive($arProduct["ID"])));
        
    }
}
