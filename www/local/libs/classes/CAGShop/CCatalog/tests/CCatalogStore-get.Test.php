<?php
require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogStore_get_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGet(){
        $objStore = new \Catalog\CCatalogStore;
        $this->assertTrue(boolval($objStore));
        $arStores = $objStore->get($nStoreId);
    }
}
