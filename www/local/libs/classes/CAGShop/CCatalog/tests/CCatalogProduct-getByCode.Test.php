<?php
require_once(realpath(__DIR__."/..")."/CCatalogProduct.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProduct_getByCode_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetByCode(){
        $objProduct = new \Catalog\CCatalogProduct;
        $this->assertTrue(boolval($objProduct));
        
        $objProduct->getByCode($sCode = 'a');
        
    }
}
