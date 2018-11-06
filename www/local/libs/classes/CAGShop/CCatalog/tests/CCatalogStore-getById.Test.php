<?php
require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogStore_getById_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetById(){
        $objStore = new \Catalog\CCatalogStore;
        $this->assertTrue(boolval($objStore));
        $arStores = $objStore->getById($nStoreId=0);
    }
}
