<?php
require_once(realpath(__DIR__."/..")."/CCatalogProduct.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProduct_getPropertiesForCard_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetPropertiesForCard(){
        $objProduct = new \Catalog\CCatalogProduct;
        $this->assertTrue(boolval($objProduct));
        
        $objProduct->getPropertiesForCard($nElementId = 0);
        
    }
}
