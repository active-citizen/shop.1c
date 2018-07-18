<?php
require_once(realpath(__DIR__."/..")."/CCurlLogger.class.php");
use AGPhop\Log as Log;

class CCurlLogger_AddLog_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testAddLog(){
        $objLog = new \Log\CCurlLogger;
        $this->assertTrue(boolval($objLog));
        
        $this->assertFalse(is_numeric($objLog->addLog([
            "ORDER_NUM"=>0,
            "URL"=>"",
            "DATA"=>""
        ])));
        
        $this->assertFalse(is_numeric($objLog->addLog([
            "ORDER_NUM"=>1,
            "URL"=>"",
            "DATA"=>""
        ])));


        $this->assertFalse(is_numeric($objLog->addLog([
            "ORDER_NUM"=>1,
            "URL"=>"asdasd",
            "DATA"=>"asdasd"
        ])));

        $this->assertFalse(is_numeric($objLog->addLog([
            "ORDER_NUM"=>1,
            "URL"=>"http://yandex.ru",
            "DATA"=>"asdasd"
        ])));


        $this->assertTrue(is_numeric($objLog->addLog([
            "ORDER_NUM"=>1,
            "URL"=>"http://yandex.ru/",
            "DATA"=>"asdasd"
        ])));
        
    }
}
