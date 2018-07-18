<?php
require_once(realpath(__DIR__."/..")."/CCatalogTeasers.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogTeasers_GetAllIds_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetAllIds(){
        $objTeasers = new \Catalog\CCatalogTeasers;
        $this->assertTrue(boolval($objTeasers));
        
        $arTeasers = $objTeasers->getAllIds();
        $this->assertArrayHasKey("IDS",$arTeasers);
        $this->assertArrayHasKey("EXISTS",$arTeasers);

        $this->assertTrue(boolval(count($arTeasers["EXISTS"])));
        $this->assertTrue(boolval(count($arTeasers["IDS"])));

    }
}
