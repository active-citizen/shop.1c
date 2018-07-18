<?php
require_once(realpath(__DIR__."/..")."/CCatalogEnums.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogEnums_GetAll_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetAll(){
        $objEnums = new \Catalog\CCatalogEnums;
        $this->assertTrue(boolval($objEnums));
        
        $arEnums = $objEnums->getAll();
        $this->assertTrue(boolval(count($arEnums)));
        
    }
}
