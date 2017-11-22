<?php
    require_once(realpath(__DIR__."/..")."/COrderStatus.class.php");
    use AGShop\Order as Order;

    class agshopTest extends PHPUnit_Framework_TestCase{

        function testFetch(){

            $objCStatus = new \Order\COrderStatus;
            
            $this->assertFalse($objCStatus->fetch("ID","ZZ"));
            
            $this->assertTrue($objCStatus->fetch("ID","F"));
            $arStatus = $objCStatus->get();
            $this->assertArrayHasKey("NAME",$arStatus);
            $this->assertEquals("Выполнен", $arStatus["NAME"]);
            
            $this->assertTrue($objCStatus->fetch("NAME","Выполнен"));
            $arStatus = $objCStatus->get();
            $this->assertArrayHasKey("ID",$arStatus);
            $this->assertEquals("F", $arStatus["ID"]);

        }

    }
