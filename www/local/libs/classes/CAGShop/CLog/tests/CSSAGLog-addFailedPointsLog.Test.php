<?php
require_once(realpath(__DIR__."/..")."/CSSAGLog.class.php");
use AGPhop\Log as Log;

class CSSAGLog_AddFailedPointsLog_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testAddFailedPointsLog(){
        $objLog = new \Log\CSSAGLog;
        $this->assertTrue(boolval($objLog));
        
        $this->assertTrue(is_numeric($objLog->addFailedPointsLog("url","request","answer")));
    }
}
