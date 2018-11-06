<?php
require_once(realpath(__DIR__."/..")."/CCurlLogger.class.php");
use AGPhop\Log as Log;

class CSSAGLog_remove_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testRemove(){
        $objLog = new \Log\CCurlLogger;
        $this->assertTrue(boolval($objLog));
        
        $objLog->remove(0);
    }
}
