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
            $sOrderNum = $this->getValidOrderNum();        
            $objTroyka = new CTroyka('3951086363');
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->getBindings($sOrderNum)
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
            $sOrderNum = $this->getValidOrderNum();
            $this->assertTrue(
                boolval(
                    $arProviders = $objTroyka->checkProviders($sOrderNum)
                ),
                "Проверка необходимости обновления перечня поставщиков "
            );
            $this->assertArrayHasKey("errorCode",$arProviders);
            $this->assertArrayHasKey("updateRequired",$arProviders);
            $this->assertArrayHasKey("actual",$arProviders);
        }

        function testGetProviders(){
            $objTroyka = new CTroyka('3951086363');
            $sOrderNum = $this->getValidOrderNum();
            $this->assertTrue(
                boolval(
                    $arProviders = $objTroyka->getProviders($sOrderNum)
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
            $sOrderNum = $this->getValidOrderNum();
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->getBindings($sOrderNum)
                ),
                "Получение прикреплунных карт"
            );
            $sOrderNum = $this->getValidOrderNum();
            $this->assertTrue(
                boolval(
                    $arProviders =
                    $objTroyka->getPaymentCapabilities($sOrderNum)
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
            $sOrderNum = $this->getValidOrderNum();
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->payment($sOrderNum)
                ),
                "Получение прикреплунных карт"
            );
        }
         function getValidOrderNum(){
            $arOrder = CSaleOrder::GetList(
                array("ID"=>"ASC"),
                array("!ADDITIONAL_INFO"=>false),
                false,
                array("nTopCount"=>1),
                array("ADDITIONAL_INFO")
            )->Fetch();
            return $arOrder["ADDITIONAL_INFO"];
        }
 
    }
