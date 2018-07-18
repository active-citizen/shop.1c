<?php
require_once(realpath(__DIR__."/..")."/CContent.class.php");
use AGPhop\Content as Content;

class CContent_GetArticleForSite_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGetArticleForSite(){
        $objContent = new \Content\CContent;
        $this->assertTrue(boolval($objContent));
        
        $arContent = $objContent->getArticleForSite('o_proekte');
        $this->assertTrue(boolval(count($arContent)));
    }
}
