<?php
require_once(realpath(__DIR__."/..")."/CCatalogWishes.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogWishes_wishRecalcForProduct_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testWishRecalcForProduct(){
        $objWish = new \Catalog\CCatalogWishes;
        $this->assertTrue(boolval($objWish));
        
        $objWish->wishRecalcForProduct(0);
    }
}
