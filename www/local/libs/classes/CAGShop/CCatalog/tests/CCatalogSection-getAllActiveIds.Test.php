<?php
require_once(realpath(__DIR__."/..")."/CCatalogSection.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogSection_getAllActiveIds_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetAllActiveIds(){
        $objSection = new \Catalog\CCatalogSection;
        $this->assertTrue(boolval($objSection));
        
        $arSections = $objSection->getAllActiveIds();
    }
}
