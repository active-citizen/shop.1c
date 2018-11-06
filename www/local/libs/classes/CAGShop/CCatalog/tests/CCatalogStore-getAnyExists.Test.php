<?php
require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogStore_getAnyExists_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetAnyExists(){
        $objStore = new \Catalog\CCatalogStore;
        $this->assertTrue(boolval($objStore));
        $arStores = $objStore->getAnyExists();
    }
}
