<?php
require_once(realpath(__DIR__."/..")."/CCurlLogger.class.php");
use AGPhop\Log as Log;

class CSSAGLog_getByOrderNum_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testgetByOrderNum(){
        $objLog = new \Log\CCurlLogger;
        $this->assertTrue(boolval($objLog));
        
        $objLog->getByOrderNum(0);
    }
}
