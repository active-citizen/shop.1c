<?php
require_once(realpath(__DIR__."/..")."/CCatalogWishes.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogWishes_set_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testSet(){
        $objWish = new \Catalog\CCatalogWishes;
        $this->assertTrue(boolval($objWish));
        
        $objWish->set(0,0,0);
    }
}
