<?php
require_once(realpath(__DIR__."/..")."/CCatalogOffer.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogOffer_failedDailyLimit_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testFailedDailyLimit(){
        $objOffer = new \Catalog\CCatalogOffer;
        $this->assertTrue(boolval($objOffer));
        $objOffer->failedDailyLimit($nOfferId = 0, $nAmount=1);
    }
}
