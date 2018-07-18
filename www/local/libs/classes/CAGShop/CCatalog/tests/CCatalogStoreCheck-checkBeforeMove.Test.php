<?php
require_once(realpath(__DIR__."/..")."/CCatalogStoreCheck.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogStoreCheck_СheckBeforeMove_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testСheckBeforeMove(){
        $objCheck = new \Catalog\CCatalogStoreCheck;
        $this->assertTrue(boolval($objCheck));
        
        $this->assertTrue($objCheck->checkBeforeMove(1,1,1));
        $this->assertFalse($objCheck->checkBeforeMove(0,1,1));
        $this->assertFalse($objCheck->checkBeforeMove(1,0,1));
        $this->assertFalse($objCheck->checkBeforeMove(1,1,0));
        $this->assertFalse($objCheck->checkBeforeMove(0,1,0));
        $this->assertFalse($objCheck->checkBeforeMove(1,0,0));
        $this->assertFalse($objCheck->checkBeforeMove(0,0,0));
    }
}
