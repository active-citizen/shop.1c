<?php
require_once(realpath(__DIR__."/..")."/CCatalogOffer.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogOffer_getDailyProductCount_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetDailyProductCount(){
        $objOffer = new \Catalog\CCatalogOffer;
        $this->assertTrue(boolval($objOffer));
        $objOffer->getDailyProductCount($nProductId=0);
    }
}
