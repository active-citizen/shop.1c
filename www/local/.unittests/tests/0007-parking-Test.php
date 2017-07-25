<?php
    class parkingTest extends PHPUnit_Framework_TestCase{

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
        
            $sPhone = '000000000';
            $sOrderNum = '123123';
            $objParking = new CParking($sPhone);
            $this->assertTrue(
                boolval($objParking->error),
                "Контроль ввода неверного номера телефона "
                    .$objParking->error
            );

            $sPhone = '0000000000';
            $sOrderNum = '123123';
            $sCodeProperty = "PARKING_TRANSACT_ID";
            $objParking = new CParking($sPhone);
            $this->assertFalse(
                boolval($objParking->error),
                "Контроль ввода верного номера телефона но в неверном для
                парковок виде"
                    .$objParking->error
            );

            // Имитируем неудачную транзакцию
            $objParking->emulation = 'failed';           
            $objParking->payment($sOrderNum);
            $this->assertTrue(
                boolval($objParking->error),
                "Контроль неудачного добавления платежа :".$objParking->error
            );
            // Имитируем удачную транзакцию
            $objParking->emulation = 'success';           
            $objParking->error = '';
            $objParking->payment($sOrderNum);
            $this->assertFalse(
                boolval($objParking->error),
                "Контроль удачного добавления платежа :".$objParking->error
            );
            $this->assertTrue(
                boolval(preg_match("#^[0-9a-f]{32}$#i",$objParking->transact)),
                "Кодтроль номера транзакции"
            );

            $sOldTransactNum = $objParking->transact;
            $sNewTransactNum = md5(rand(1,1000000));

            // Пытаемся привязать другую транзакцию к номеру заказа
            $objParking->transact = $sNewTransactNum;
            $objParking->linkOrderTransact($sOrderNum);
            $this->assertFalse(
                boolval($objParking->error),
                "Контроль привязки номера транзакции к заказу"
                    .$objParking->error
            );

            // Получаем ID Транзакции для заказа
            $sCheckTransact = $objParking->getPropertyByOrderNum(
                $sOrderNum,
                $sCodeProperty
            );

            $this->assertEquals(
                $sCheckTransact,
                $sNewTransactNum,
                "Контроль правильного номера транзакции"
                    .$objParking->error
            );

            // Пытаемся привязать прежнюю транзакцию к номеру заказа
            $objParking->transact = $sOldTransactNum;
            $objParking->linkOrderTransact($sOrderNum);
            $this->assertFalse(
                boolval($objParking->error),
                "Контроль привязки номера транзакции к заказу"
                    .$objParking->error
            );

            // Получаем ID Транзакции для заказа
            $sCheckTransact = $objParking->getPropertyByOrderNum(
                $sOrderNum,
                $sCodeProperty
            );

            $this->assertEquals(
                $sCheckTransact,
                $sOldTransactNum,
                "Контроль правильного номера транзакции"
                    .$objParking->error
            );


            

        }

  
    }
