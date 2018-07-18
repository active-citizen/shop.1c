<?php
require_once(realpath(__DIR__."/..")."/CSSAGLog.class.php");
use AGPhop\Log as Log;

class CSSAGLog_AddLog_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testAddLog(){
        $objLog = new \Log\CSSAGLog;
        $this->assertTrue(boolval($objLog));
        
        $this->assertTrue(boolval($objLog->addLog("url","input","output")));
    }
}
