<?php
require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogStore_GetAllActive_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetAllActive(){
        $objStore = new \Catalog\CCatalogStore;
        $this->assertTrue(boolval($objStore));
        $arStores = $objStore->getAllActive();
        $this->assertTrue(boolval(count(($arStores))));
        $this->assertArrayHasKey("ID",$arStores[0]);
        $this->assertArrayHasKey("TITLE",$arStores[0]);
        $this->assertArrayHasKey("ACTIVE",$arStores[0]);
        $this->assertArrayHasKey("DATE_MODIFY",$arStores[0]);
        $this->assertArrayHasKey("DATE_CREATE",$arStores[0]);
        $this->assertArrayHasKey("XML_ID",$arStores[0]);
        $this->assertArrayHasKey("SORT",$arStores[0]);
        $this->assertArrayHasKey("ISSUING_CENTER",$arStores[0]);
        $this->assertArrayHasKey("SHIPPING_CENTER",$arStores[0]);
    }
}
