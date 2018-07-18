<?php
require_once(realpath(__DIR__."/..")."/CCatalogSorting.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogSorting_GetIBlockSorting_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetIBlockSorting(){
        $objSorting = new \Catalog\CCatalogSorting;
        $this->assertTrue(boolval($objSorting));

        // По умолчанию сортировка по времени правки по убыванию
        $arSorting = $objSorting->getIBlockSorting([]);
        $this->assertEquals($arSorting,["TIMESTAMP_X"=>"DESC"]);
        
        $arSorting = $objSorting->getIBlockSorting(["param"=>"price"]);
        $this->assertEquals($arSorting,["PROPERTY_MINIMUM_PRICE"=>"DESC"]);

        $arSorting = $objSorting->getIBlockSorting(["param"=>"price","direction"=>"asc"]);
        $this->assertEquals($arSorting,["PROPERTY_MINIMUM_PRICE"=>"ASC"]);

        $arSorting = $objSorting->getIBlockSorting(["param"=>"hit"]);
        $this->assertEquals($arSorting,["PROPERTY_SALELEADER"=>"DESC"]);

        $arSorting = $objSorting->getIBlockSorting(["param"=>"hit"]);
        $this->assertEquals($arSorting,["PROPERTY_SALELEADER"=>"DESC"]);

        $arSorting = $objSorting->getIBlockSorting(["param"=>"sale"]);
        $this->assertEquals($arSorting,["PROPERTY_SPECIALOFFER"=>"DESC"]);

        $arSorting = $objSorting->getIBlockSorting(["param"=>"new"]);
        $this->assertEquals($arSorting,["PROPERTY_NEWPRODUCT"=>"DESC"]);
        
        $arSorting = $objSorting->getIBlockSorting(["param"=>"wishes"]);
        $this->assertEquals($arSorting,["PROPERTY_WISHES_QUANTITY"=>"DESC"]);
        
        $arSorting = $objSorting->getIBlockSorting(["param"=>"rating"]);
        $this->assertEquals($arSorting,["PROPERTY_RATING"=>"DESC"]);
        
        $arSorting = $objSorting->getIBlockSorting(["param"=>"fresh"]);
        $this->assertEquals($arSorting,["TIMESTAMP_X"=>"DESC"]);
    }
}
