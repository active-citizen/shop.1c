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
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
            $objTroyka = new CTroyka($TROYKA_CARD);
            $this->assertFileExists($objTroyka->pemPath,
                "Проверка наличия pem-сертификата");
        }

        function testGetBindings(){
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");

            $objTroyka = new CTroyka($TROYKA_CARD);
            $this->assertTrue(
                boolval($arCards = $objTroyka->getBindings($TROYKA_PHONE)),
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

        function testLinkOrder(){
            $nTroykaNum = sprintf("%010d",rand(0,1000000000));
            $objTroyka = new CTroyka($nTroykaNum);
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
                array("ADDITIONAL_INFO")
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
            $this->assertTrue(
                boolval($objTroyka->payment($arOrder["ADDITIONAL_INFO"])),
                "Платёж по тройке"
            );

        }
        
    }
