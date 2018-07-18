<?php
require_once(realpath(__DIR__."/..")."/CCatalogInterests.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogInterests_ProductsByIds_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testgetProductsByIds(){
        $objInterests = new \Catalog\CCatalogInterests;
        $this->assertTrue(boolval($objInterests));

        // Получаем продукт с утановленным интересом
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objInterests->IBLOCKS["CATALOG"],
                "!PROPERTY_INTERESTS"=>false
            ],false,["nTopCount"=>1],
            ["ID","CODE","PROPERTY_INTERESTS"]
        )->Fetch();

        // Проверяем на наличие интереса
        $arIds = $objInterests->getProductsByIds(
            [$arProduct["PROPERTY_INTERESTS_VALUE"]],
            [$arProduct["ID"]]
        );
        $this->assertTrue(boolval(
            isset($arIds[0]) && $arIds[0]==$arProduct["ID"]
        ));
        
        // Проверяем на отсутствие интереса
        $arIds = $objInterests->getProductsByIds(
            [99999999999999999999999999999],
            [$arProduct["ID"]]
        );
        $this->assertFalse(boolval($arIds));
    }
}
