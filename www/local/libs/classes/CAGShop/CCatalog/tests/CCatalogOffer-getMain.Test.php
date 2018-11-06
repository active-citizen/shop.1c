<?php
require_once(realpath(__DIR__."/..")."/CCatalogOffer.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogOffer_getMain_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetMain(){
        $objOffer = new \Catalog\CCatalogOffer;
        $this->assertTrue(boolval($objOffer));
        $objOffer->getMain(0);
    }
}
