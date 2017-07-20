<?php
    class troykaPaymentTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
            require_once(
                $_SERVER["DOCUMENT_ROOT"].
                "/.integration/classes/troyka.class.php"
            );
        }

        /**
            Проверка наличия сертификатов
        */
        function testCertsExists(){
            $objTroyka = new CTroyka('');
            $this->assertFileExists($objTroyka->pemPath,
                "Проверка наличия pem-сертификата");
        }

        function testGetBindings(){
            $objTroyka = new CTroyka('3951086363');
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->getBindings('0000000000')
                ),
                "Получение прикреплунных карт"
            );
            
            foreach($arCards as $key=>$arCard){
                $this->assertArrayHasKey("bindingId",$arCard);
                $this->assertArrayHasKey("cardType",$arCard);
                $this->assertArrayHasKey("createdDate",$arCard);
                $this->assertArrayHasKey("updatedDate",$arCard);
                $this->assertArrayHasKey("maskedPan",$arCard);
            }

        }

        function testCheckProviders(){
            $objTroyka = new CTroyka('3951086363');
            $this->assertTrue(
                boolval(
                    $arProviders = $objTroyka->checkProviders('0000000000')
                ),
                "Проверка необходимости обновления перечня поставщиков "
            );
            $this->assertArrayHasKey("errorCode",$arProviders);
            $this->assertArrayHasKey("updateRequired",$arProviders);
            $this->assertArrayHasKey("actual",$arProviders);
        }

        function testGetProviders(){
            $objTroyka = new CTroyka('3951086363');
            $this->assertTrue(
                boolval(
                    $arProviders = $objTroyka->getProviders('0000000000')
                ),
                "Проверка Получение перечня поставщиков "
            );
            $this->assertArrayHasKey("errorCode",$arProviders);
            $this->assertArrayHasKey("iconsURL",$arProviders);
            $this->assertArrayHasKey("version",$arProviders);
            $this->assertArrayHasKey("categories",$arProviders);
            $this->assertArrayHasKey("categoryName",$arProviders["categories"]);
            $this->assertArrayHasKey("categoryTitle",$arProviders["categories"]);
            $this->assertArrayHasKey("number",$arProviders["categories"]);
            $this->assertArrayHasKey("categoryHidden",$arProviders["categories"]);
            $this->assertArrayHasKey("icon",$arProviders["categories"]);
            $this->assertArrayHasKey("payees",$arProviders["categories"]);
        }

        function testGetPaymentCapabilities(){
            $objTroyka = new CTroyka('3951086363');
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->getBindings('0000000000')
                ),
                "Получение прикреплунных карт"
            );
             $this->assertTrue(
                boolval(
                    $arProviders =
                    $objTroyka->getPaymentCapabilities('0000000000')
                ),
                "Проверка Запрос  расчета комиссии и лимитов на платеж"
            );
            $this->assertArrayHasKey("mdOrder",$arProviders);
            $this->assertArrayHasKey("bindingId",$arProviders);
            $this->assertArrayHasKey("mnemonic",$arProviders);
            $this->assertArrayHasKey("maskedPan",$arProviders);
            $this->assertArrayHasKey("cardType",$arProviders);
            $this->assertArrayHasKey("userSelected",$arProviders);
            $this->assertArrayHasKey("cvcRequired",$arProviders);
            $this->assertArrayHasKey("transactionAmount",$arProviders);
        }

        function testPayment(){
            $objTroyka = new CTroyka('3951086363');
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->payment('0000000000')
                ),
                "Получение прикреплунных карт"
            );
        }
  
    }
