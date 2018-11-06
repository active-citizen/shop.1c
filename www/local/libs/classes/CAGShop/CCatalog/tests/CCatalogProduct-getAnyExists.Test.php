<?php
require_once(realpath(__DIR__."/..")."/CCatalogProduct.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProduct_getAnyExists_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetAnyExists(){
        $objProduct = new \Catalog\CCatalogProduct;
        $this->assertTrue(boolval($objProduct));
        
        $objProduct->getAnyExists();
        
    }
}
