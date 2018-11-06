<?php
require_once(realpath(__DIR__."/..")."/CCatalogProperties.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProperties_getPropEnum_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetPropEnum(){
        $objProperty = new \Catalog\CCatalogProperties;
        $this->assertTrue(boolval($objProperty));
        
        $arProperties = $objProperty->getPropEnum($sCode='',$nEnumId=0);
        
    }
}
