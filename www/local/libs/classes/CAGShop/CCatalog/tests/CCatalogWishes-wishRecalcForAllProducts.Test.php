<?php
require_once(realpath(__DIR__."/..")."/CCatalogWishes.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogWishes_wishRecalcForAllProducts_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testWishRecalcForAllProducts(){
        $objWish = new \Catalog\CCatalogWishes;
        $this->assertTrue(boolval($objWish));
        
        $objWish->wishRecalcForAllProducts();
    }
}
