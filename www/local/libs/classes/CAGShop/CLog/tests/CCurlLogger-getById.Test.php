<?php
require_once(realpath(__DIR__."/..")."/CCurlLogger.class.php");
use AGPhop\Log as Log;

class CSSAGLog_GetById_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetById(){
        $objLog = new \Log\CCurlLogger;
        $this->assertTrue(boolval($objLog));
        
        $objLog->getByid(0);
    }
}
