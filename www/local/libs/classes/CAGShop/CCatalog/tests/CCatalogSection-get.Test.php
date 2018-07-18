<?php
require_once(realpath(__DIR__."/..")."/CCatalogSection.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogSection_Get_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGet(){
        $objSection = new \Catalog\CCatalogSection;
        $this->assertTrue(boolval($objSection));
        
        $arSections = $objSection->get();
        $this->assertTrue(boolval($arSections));
        
        $this->assertArrayHasKey("ID",$arSections[0]);
        $this->assertArrayHasKey("NAME",$arSections[0]);
        $this->assertArrayHasKey("CODE",$arSections[0]);
        $this->assertArrayHasKey("ACTIVE",$arSections[0]);
        
    }
}
