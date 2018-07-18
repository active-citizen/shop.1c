<?php
require_once(realpath(__DIR__."/..")."/CCatalogElement.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogElement_GetByCode_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetByCode(){
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
        
        $arElementProduct = $objElement->getByCode($arProduct["CODE"]);
        
        $this->assertTrue(boolval($arElementProduct["ID"]==$arProduct["ID"]));
        $this->assertTrue(boolval($arElementProduct["ACTIVE"]==$arProduct["ACTIVE"]));
        $this->assertTrue(boolval($arElementProduct["CODE"]==$arProduct["CODE"]));
    }
}
