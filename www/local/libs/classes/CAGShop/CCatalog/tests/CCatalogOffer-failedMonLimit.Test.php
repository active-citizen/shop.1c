<?php
require_once(realpath(__DIR__."/..")."/CCatalogOffer.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogOffer_failedMonLimit_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testFailedMonLimit(){
        $objOffer = new \Catalog\CCatalogOffer;
        $this->assertTrue(boolval($objOffer));
        $objOffer->failedMonLimit($nUserId = 0, $nOfferId = 0, $nAmount=1);
    }
}
