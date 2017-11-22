<?php
    require_once(realpath(__DIR__."/..")."/COrderProperty.class.php");
    use AGPhop\Order as Order;
    
    class agshopOrderPropertyTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        function testFetchByCode(){
            $objCOrderProperty = new \Order\COrderProperty;
            $this->assertFalse($objCOrderProperty->existsByCode(md5(rand())));
            $this->assertTrue($objCOrderProperty->existsByCode("EMAIL"));
            
        }

    }
