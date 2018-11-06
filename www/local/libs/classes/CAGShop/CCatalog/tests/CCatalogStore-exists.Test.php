<?php
require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogStore_exists_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testExists(){
        $objStore = new \Catalog\CCatalogStore;
        $this->assertTrue(boolval($objStore));
        $arStores = $objStore->exists($nProductId=0);
    }
}
