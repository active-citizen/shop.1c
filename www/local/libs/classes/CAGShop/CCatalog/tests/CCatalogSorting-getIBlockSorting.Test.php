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
    }
}
