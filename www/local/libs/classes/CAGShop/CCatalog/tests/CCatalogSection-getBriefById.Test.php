<?php
require_once(realpath(__DIR__."/..")."/CCatalogSection.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogSection_getBriefById_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetBriefById(){
        $objSection = new \Catalog\CCatalogSection;
        $this->assertTrue(boolval($objSection));
        
        $arSections = $objSection->getBriefById($nSectionId=0);
    }
}
