<?php
require_once(realpath(__DIR__."/..")."/CCatalogPrice.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogPrice_GetProductsByPrice_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetProductsByPrice(){
        $objPrice = new \Catalog\CCatalogPrice;
        $this->assertTrue(boolval($objPrice));

        // Получаем продукт с утановленной ценой
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objPrice->IBLOCKS["CATALOG"],
                "!PROPERTY_MINIMUM_PRICE"=>false
            ],false,["nTopCount"=>1],
            ["ID","CODE","PROPERTY_MINIMUM_PRICE"]
        )->Fetch();
        
        $arIds = $objPrice->getProductsByPrice(
            $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]-1,
            $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]+1,
            [$arProduct["ID"]]
        );
////        $this->assertTrue(boolval(
////            isset($arIds[0]) && $arIds[0]==$arProduct["ID"]
////        ));


        $arIds = $objPrice->getProductsByPrice(
            $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]+1,
            $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]+2,
            [$arProduct["ID"]]
        );
///////        $this->assertFalse(boolval($arIds));

        
        /*

        // Получаем продукт с утановленным интересом
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objProperty->IBLOCKS["CATALOG"],
                "!PROPERTY_INTERESTS"=>false
            ],false,["nTopCount"=>1],
            ["ID","CODE","PROPERTY_INTERESTS"]
        )->Fetch();

        // Проверяем на наличие интереса
        $arIds = $objInterests->getProductsByIds(
            [$arProduct["PROPERTY_INTERESTS_VALUE"]],
            [$arProduct["ID"]]
        );
        
        // Проверяем на отсутствие интереса
        $arIds = $objInterests->getProductsByIds(
            [99999999999999999999999999999],
            [$arProduct["ID"]]
        );
        $this->assertFalse(boolval($arIds));
        */
    }
}
