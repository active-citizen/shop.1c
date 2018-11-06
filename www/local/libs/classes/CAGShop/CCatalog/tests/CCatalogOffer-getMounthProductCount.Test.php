<?php
require_once(realpath(__DIR__."/..")."/CCatalogOffer.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogOffer_getMounthProductCount_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetMounthProductCount(){
        $objOffer = new \Catalog\CCatalogOffer;
        $this->assertTrue(boolval($objOffer));
        $objOffer->getMounthProductCount($nUserId = 0,$nProductId=0);
    }
}
