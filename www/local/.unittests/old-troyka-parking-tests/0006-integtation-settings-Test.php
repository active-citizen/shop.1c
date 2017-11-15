<?php
    class integrationSettingsTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){

            require_once(
                $_SERVER["DOCUMENT_ROOT"].
                "/.integration/classes/integrationSettings.class.php"
            );
        }

        function testTroykaSettings(){
            $objSettings = new CIntegrationSettings("TROYKA");
            $this->assertFalse(
                boolval($objSettings->error),
                "Проверка корректности кода настройки:".$objSettings->error
            );
            $arSettings = $objSettings->get();
            $this->assertTrue(
                boolval($arSettings),
                "Проверка наличия настроек:".$objSettings->error
            );

            $this->assertArrayHasKey("TROYKA_EMULATION",$arSettings);
            $this->assertArrayHasKey("TROYKA_PHONE",$arSettings);
            $this->assertArrayHasKey("TROYKA_CARD",$arSettings);
            $this->assertArrayHasKey("TROYKA_BINDING_ID",$arSettings);
            $this->assertArrayHasKey("TROYKA_CVC",$arSettings);
            $this->assertArrayHasKey("TROYKA_URL",$arSettings);
            $this->assertArrayHasKey("TROYKA_CURRENT_VERSION",$arSettings);
            $this->assertArrayHasKey("TROYKA_IP",$arSettings);
            $this->assertArrayHasKey("TROYKA_SERVICE_ID",$arSettings);
            $this->assertArrayHasKey("TROYKA_CURRENCY",$arSettings);
            $this->assertArrayHasKey("TROYKA_AMOUNT",$arSettings);

            // Новые данные для записи
            $arSettings2 = array(
                "TROYKA_EMULATION" => array(
                    "TITLE"=>"Режим эмуляции",
                    "VALUE"=>'success'
                ),
                "TROYKA_PHONE" => array(
                    "TITLE"=>"Номер телефона для обращения к шлюзу",
                    "VALUE"=>rand(0,100000000)
                ),
                "TROYKA_CARD" => array(
                    "TITLE"=>"Номер карты тройки по умолчанию(для автотестов)",
                    "VALUE"=>rand(0,100000000)
                ),
                "TROYKA_BINDING_ID" => array(
                    "TITLE"=>"Номер банковской карты",
                    "VALUE"=>rand(0,1000000000000000000)
                ),
                "TROYKA_CVC" => array(
                    "TITLE"=>"CVC банковской карты",
                    "VALUE"=>rand(0,1000)
                ),
                "TROYKA_URL" => array(
                    "TITLE"=>"URL шлюза тройки",
                    "VALUE"=>"https://bmmobile.bm.ru/bm/api/ws/3.2/actions?wsdl"
                ),
                "TROYKA_CURRENT_VERSION" => array(
                    "TITLE"=>"Номер версии протокола",
                    "VALUE"=>rand(0,10000)
                ),
                "TROYKA_IP" => array(
                    "TITLE"=>"Внешний IP адрес сайта",
                    "VALUE"=>long2ip(rand(0,pow(2,32)))
                ),
                "TROYKA_SERVICE_ID" => array(
                    "TITLE"=>"ID сервиса",
                    "VALUE"=>rand(0,1000)
                ),
                "TROYKA_CURRENCY" => array(
                    "TITLE"=>"Код валюты(рубля)",
                    "VALUE"=>rand(0,1000)
                ),
                "TROYKA_AMOUNT" => array(
                    "TITLE"=>"Квант пополнения (руб)",
                    "VALUE"=>rand(0,1000)
                ),
            );

            $objSettings->set($arSettings2);
            $arSettingsTest = $objSettings->get();
            $this->assertEquals(
                $arSettings2,$arSettingsTest
            ); 

            $objSettings->set($arSettings);
            $arSettingsTest = $objSettings->get();
            $this->assertEquals(
                $arSettings,$arSettingsTest
            ); 
        }

  
    }
