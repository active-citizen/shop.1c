<?php
require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogStore_getProductAmount_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetProductAmount(){
        $objStore = new \Catalog\CCatalogStore;
        $this->assertTrue(boolval($objStore));
        $arStores = $objStore->getProductAmount($nProductId=0,$nStoreId=0);
    }
}
