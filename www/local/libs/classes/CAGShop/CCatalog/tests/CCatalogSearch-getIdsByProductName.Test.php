<?php
require_once(realpath(__DIR__."/..")."/CCatalogSearch.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogSearch_GetIdsByProductName_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetIdsByProductName(){
        $objSearch = new \Catalog\CCatalogSearch;
        $this->assertTrue(boolval($objSearch));

        // Получаем продукт с утановленной ценой
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objSearch->IBLOCKS["CATALOG"],
                "!NAME"=>false
            ],false,["nTopCount"=>1],
            ["ID","CODE","NAME"]
        )->Fetch();
        
        // Ищем этот товар
        $arIds = $objSearch->getIdsByProductName(
            $arProduct["NAME"],[$arProduct["ID"]]
        );
        $this->assertTrue(boolval(
            isset($arIds[0]) && $arIds[0]==$arProduct["ID"]
        ));

        //Ищем то, что точно не найдётся
        $arIds = $objSearch->getIdsByProductName(
            $arProduct["NAME"].md5(rand(1,100000)),[$arProduct["ID"]]
        );
        $this->assertFalse(boolval($arIds));
    }
}
