<?php
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    use AGShop;

    class agshopTest extends PHPUnit_Framework_TestCase{

        /**
        *   Тест метода обработки ошибок
        */
        function testErrors(){

            $objAGShop = new \AGShop\CAGShop;

            $objAGShop->addError("Error 1");
            $this->assertEquals(
                $objAGShop->getErrors(),
                ['Error 1']
            );

            $objAGShop->addError(["Error 2","Error 3"]);
            $this->assertEquals(
                $objAGShop->getErrors(),
                ['Error 1','Error 2','Error 3']
            );

            $objAGShop->clearError();
            $this->assertEquals(
                $objAGShop->getErrors(),
                []
            );

        }

        function testDateParse(){
            $objAGShop = new \AGShop\CAGShop;
            
            $this->assertFalse($objAGShop->getDateISO("Дата"));
            $this->assertEquals($objAGShop->getDateISO("23.4.2017 12:3:1")
                ,"2017-04-23 12:03:01"
            );
            $this->assertEquals($objAGShop->getDateISO("24.4.2017")
                ,"2017-04-24 00:00:00"
            );

            $this->assertFalse($objAGShop->getDateHum("Дата"));
            $this->assertEquals($objAGShop->getDateHum("2017-4-23 12:3:1")
                ,"23.04.2017 12:03:01"
            );
            $this->assertEquals($objAGShop->getDateHum("2017-4-24")
                ,"24.04.2017 00:00:00"
            );
        }
        
    }
