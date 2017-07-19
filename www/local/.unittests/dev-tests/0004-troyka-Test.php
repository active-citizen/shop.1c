<?php
    class troykaTest extends PHPUnit_Framework_TestCase{

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
            $objTroyka = new CTroyka();
            $this->assertFileExists($objTroyka->pemPath,
                "Проверка наличия pem-сертификата");
        }

        function testGetBindings(){
            $objTroyka = new CTroyka();
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
            $objTroyka = new CTroyka();
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
            $objTroyka = new CTroyka();
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
            $objTroyka = new CTroyka();
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->getBindings('0000000000')
                ),
                "Получение прикреплунных карт"
            );
             $this->assertTrue(
                boolval(
                    $arProviders = $objTroyka->getPaymentCapabilities('0000000000')
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
            $objTroyka = new CTroyka();
            $this->assertTrue(
                boolval(
                    $arCards =
                    $objTroyka->payment('0000000000')
                ),
                "Получение прикреплунных карт"
            );
            /*
            $this->assertArrayHasKey("mdOrder",$arProviders);
            $this->assertArrayHasKey("bindingId",$arProviders);
            $this->assertArrayHasKey("mnemonic",$arProviders);
            $this->assertArrayHasKey("maskedPan",$arProviders);
            $this->assertArrayHasKey("cardType",$arProviders);
            $this->assertArrayHasKey("userSelected",$arProviders);
            $this->assertArrayHasKey("cvcRequired",$arProviders);
            $this->assertArrayHasKey("transactionAmount",$arProviders);
            */
        }


        function testLinkOrder(){
            $nTroykaNum = sprintf("%010d",rand(0,1000000000));
            $objTroyka = new CTroyka();
            $this->assertFalse(boolval($objTroyka->error),
                "Проверка создания объекта тройки"
            );

            // Выбираем любой заказ с номером
            CModule::IncludeModule('sale');
            $arOrder = CSaleOrder::GetList(
                array(),
                array(
                    "!ADDITIONAL_INFO"=>false
                ),false,
                array("nTopCount"=>1),
                array("ADDITIONAL_INFO","ID")
            )->Fetch();
            $this->assertArrayHasKey("ADDITIONAL_INFO",$arOrder,
                "Проверяем наличие номера заказа"
            );
            $this->assertTrue(
                boolval(trim($arOrder["ADDITIONAL_INFO"])),
                "Проверяем непустоту номера заказа"
            );
            $objTroyka->linkOrder("asd");
            $this->assertTrue(boolval($objTroyka->error),
               "Контроль некорректного номера заказа" 
            );
            $objTroyka->linkOrder($arOrder["ADDITIONAL_INFO"]);
            $this->assertFalse(boolval(trim($objTroyka->error)),
               "Контроль корректного номера заказа" 
            );

            $this->assertEquals(
                $objTroyka->getTroykaNum($arOrder["ADDITIONAL_INFO"]),
                $objTroyka->number,
                "Проверка назначенного заказу номера карты тройка"
            );

            
            $objTroyka->linkOrder($arOrder["ADDITIONAL_INFO"],'');
            $this->assertFalse(boolval(trim($objTroyka->error)),
               "Контроль пустого номера заказа" 
            );

            $this->assertEquals(
                $objTroyka->getTroykaNum($arOrder["ADDITIONAL_INFO"]),
                '',
                "Проверка назначенного заказу пустого номера карты тройка"
            );
        }
        
    }
