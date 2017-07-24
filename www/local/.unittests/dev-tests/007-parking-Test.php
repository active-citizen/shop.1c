<?php
    class integrationSettingsTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
            require_once(
                $_SERVER["DOCUMENT_ROOT"].
                "/.integration/classes/integrationSettings.class.php"
            );
            require_once(
                $_SERVER["DOCUMENT_ROOT"].
                "/.integration/classes/parking.class.php"
            );
        }

        function testTroykaSettings(){
            $sPhone = '000sd00000';
            $objParking = new CParking($sPhone);
            $this->assertTrue(
                boolval($objParking->error),
                "Контроль ввода неверного номера телефона "
                    .$objParking->error
            );
            unset($objParking);
        
            $sPhone = '0000000000';
            $objParking = new CParking($sPhone);
            $this->assertFalse(
                boolval($objParking->error),
                "Контроль ввода неверного номера телефона "
                    .$objParking->error
            );
        }

  
    }
