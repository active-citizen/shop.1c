<?php
    require_once(realpath(__DIR__."/..")."/COrder.class.php");
    use AGPhop\Order as Order;
    
    class agshopOrderTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            Тест получения базовых форм слова
        */
        function testOrderParams(){
            $objCOrder = new \Order\COrder;
            
            $num = "1".rand(1,1000000000);
            $this->assertFalse($objCOrder->setParam("Num", "Б-"));
            $this->assertFalse($objCOrder->setParam("Num", $num));
            $this->assertTrue($objCOrder->setParam("Num", "Б-".$num));
            
            $this->assertEquals($objCOrder->getOrderType(),"Б");
            $this->assertEquals($objCOrder->getParam("Num"),"Б-".$num);
            
        }


  
    }
