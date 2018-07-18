<?php
require_once(realpath(__DIR__."/..")."/CContent.class.php");
use AGPhop\Content as Content;

class CContent_GetFAQForSite_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetFAQForSite(){
        $objContent = new \Content\CContent;
        $this->assertTrue(boolval($objContent));
        
        $arContent = $objContent->getFAQForSite();
        $this->assertTrue(boolval(count($arContent)));
    }
}
