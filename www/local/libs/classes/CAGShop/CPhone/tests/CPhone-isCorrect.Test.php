<?php
require_once(realpath(__DIR__."/..")."/CPhone.class.php");
use AGPhop\Phone as Phone;

class CPhone_IsCorrect_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testIsCorrect(){
        $objPhone = new \Phone\CPhone;
        $this->assertTrue(boolval($objPhone));
        
        $this->assertTrue($objPhone->isCorrect("79171189696"));
        $this->assertFalse($objPhone->isCorrect("7-917-118-9696"));
        
    }
}
