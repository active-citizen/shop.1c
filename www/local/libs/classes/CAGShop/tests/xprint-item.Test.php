<?php
require_once(realpath(__DIR__."/..")."/xprint.class.php");
use AGPhop\Catalog as Catalog;

class xprint_item_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testItem(){
        $objXPrint = new \xprint([]);
        $this->assertTrue(boolval($objXPrint));
        
        $objXPrint->item([]);
    }
}
