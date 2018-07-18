<?php
require_once(realpath(__DIR__."/..")."/CCatalogFilter.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogFilter_GetIBlockFilter_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetIBlockFilter(){
        $objFilter = new \Catalog\CCatalogFilter;
        $this->assertTrue(boolval($objFilter));
        
        $arOptions = [
            "interest"  =>[1,2],
            "price_min" =>100,
            "price_max" =>200,
            "hit"       =>1,
            "new"       =>0,
            "sale"      =>1,
            "query"     =>"",
            "section_code"=>"test"
            
        ];
        
        $arFilter = $objFilter->getIBlockFilter($arOptions);
        
        
        $this->assertEquals($arFilter,[
            "interest"  =>[1,2],
            "price_min" =>100,
            "price_max" =>200,
            "hit"       =>1,
            "sale"      =>1,
            "section_code"=>"test"
        ]);
    }
}
