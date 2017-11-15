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
        
    }
