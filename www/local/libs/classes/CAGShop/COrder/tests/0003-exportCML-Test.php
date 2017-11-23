<?php
    require_once(realpath(__DIR__."/..")."/COrderExportCML.class.php");
    use AGPhop\Order as Order;
    
    class agshopOrderExpordCMLTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        function testExportCML(){
            $objCOrderExportCML = new \Order\COrderExportCML;
            
            $sSessionId = md5(rand());
            $nLockedSeconds = 1;
            
            $this->assertFalse(boolval(
                $arExport = $objCOrderExportCML->getLastZNI()
            ),print_r($objCOrderExportCML->getErrors(), 1));

            $this->assertTrue(boolval(
                $arExport = $objCOrderExportCML->getLastZNI(
                    $sSessionId,$nLockedSeconds
                )
            ),print_r($objCOrderExportCML->getErrors(), 1));
        }
        
    }
