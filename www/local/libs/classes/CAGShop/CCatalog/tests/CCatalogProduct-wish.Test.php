<?php
require_once(realpath(__DIR__."/..")."/CCatalogProduct.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProduct_wish_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testWish(){
        $objProduct = new \Catalog\CCatalogProduct;
        $this->assertTrue(boolval($objProduct));
        
        $objProduct->wish($nProductId = 0, $sAct = 'add', $nUserId = 0);
        
    }
}
