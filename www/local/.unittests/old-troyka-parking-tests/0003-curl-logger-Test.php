<?php
    class curlLoggerWriteReadTest extends PHPUnit_Framework_TestCase{

        var $classFilename = '';
        var $url = false;

        function __construct(){
            $this->classFilename = 
                $_SERVER["DOCUMENT_ROOT"]
                ."/.integration/classes/curllogger.class.php";
            require_once(
                $_SERVER["DOCUMENT_ROOT"]
                ."/.integration/classes/curl.class.php"
            );
            $this->url = 'https://ya.ru';
        }

        function testClassFileExists(){
           $this->assertFileExists($this->classFilename); 
        }

        function testClassInclude(){
            $this->assertTrue(boolval(include_once($this->classFilename)));
        }

        function testCreateLoggerObject(){
            $this->objLogger = new CCurlLogger();
            $this->assertInternalType('object',$this->objLogger);
        }

        function testCreateCurlObject(){
            $this->objCurl = new curlTool();
            $this->assertInternalType('object',$this->objCurl);
        }

        function testAddLog(){
            $objLogger = new CCurlLogger();

            $arLogParam = array();
            $nlogID = $objLogger->addLog( $arLogParam);
            $this->assertFalse(
                boolval(intval($nlogID)),
                'Не должен добавляться лог без номера заказа'
            );
            $this->assertEquals(
                $objLogger->error,
                "Не указан номер заказа",
                "Контроль текста ошибки отсутствия номера заказа"
            );

            $arLogParam = array("ORDER_NUM"=>"SDCERSDF");
            $nlogID = $objLogger->addLog( $arLogParam);
            $this->assertFalse(
                boolval(intval($nlogID)),
                'Не должен добавляться лог с некорректным домером заказа'
            );
            $this->assertEquals(
                $objLogger->error,
                "Некорректный номер заказа",
                "Контроль текста ошибки некорректного номера заказа"
            );

            $arLogParam = array("ORDER_NUM"=>"123123123");
            $nlogID = $objLogger->addLog( $arLogParam);
            $this->assertFalse(
                boolval(intval($nlogID)),
                'Не должен добавляться лог с не указанным URL'
            );
            $this->assertEquals(
                $objLogger->error,
                "Не указан URL",
                "Контроль текста ошибки не указанного url"
            );

            $arLogParam = array("ORDER_NUM"=>"123123123","URL"=>"aaaaaa");
            $nlogID = $objLogger->addLog( $arLogParam);
            $this->assertFalse(
                boolval(intval($nlogID)),
                'Не должен добавляться лог с некорректно указанным URL'
            );
            $this->assertEquals(
                $objLogger->error,
                "Некорректный URL",
                "Контроль текста ошибки некорректно указанного url"
            );

            $arLogParam = array("ORDER_NUM"=>"123123123","URL"=>"https://ya.ru/");
            $nlogID = $objLogger->addLog( $arLogParam);
            $this->assertFalse(
                boolval(intval($nlogID)),
                'Не должен добавляться лог с неуказанными данными'
            );
            $this->assertEquals(
                $objLogger->error,
                "Не указаны данные",
                "Контроль текста ошибки неуказанных данных"
            );

            $arLogParam = array("ORDER_NUM"=>"123123123","URL"=>"https://ya.ru/",
                "DATA"=>"Content data","POST_DATA"=>"Post data"
            );
            $nLogID = $objLogger->addLog( $arLogParam);
            $this->assertTrue(
                boolval(intval($nLogID)),
                'Добавление лога и получение его ID'
            );

            $this->assertFalse(
                boolval($objLogger->getById(0)),
                'Получение лога с некорректным ID'
            );
            $this->assertEquals(
                $objLogger->error,
                "Некорректный ID лога для выборки",
                "Контроль ошибки получения лога с некорректным ID"
            );


            $arLog = $objLogger->getById($nLogID);
            $this->assertTrue(
                boolval($arLog),
                'Получение лога с корректным ID='.$nLogID
            );
            unset($arLog["ctime"]);
            $this->assertEquals(
                $arLog["id"],
                $nLogID,
                "Проверка эквивалентности id"
            );
            $this->assertEquals(
                $arLog["DATA"],
                $arParams["DATA"],
                "Проверка эквивалентности DATA"
            );
            $this->assertEquals(
                $arLog["POST_DATA"],
                $arParams["POST_DATA"],
                "Проверка эквивалентности POST_DATA"
            );
            $this->assertEquals(
                $arLog["URL"],
                $arParams["URL"],
                "Проверка эквивалентности URL"
            );
            $this->assertEquals(
                $arLog["ORDER_NUM"],
                $arParams["ORDER_NUM"],
                "Проверка эквивалентности ORDER_NUM"
            );

            $this->assertFalse(boolval($objLogger->remove(0)),
                "Попытка удалить лог с некорректным ID"
            );
            $this->assertEquals(
                $objLogger->error,
                "Некорректный ID лога для удаления",
                "Контроль ошибки удаления лога с некорректным ID"
            );

            $this->assertTrue(boolval($objLogger->remove($nLogID)),
                "Попытка удалить лог с корректным ID=".$nLogID
            );
            $this->assertEquals(
                $objLogger->error,
                "",
                "Контроль ошибки удаления лога с корректным ID"
            );

            $arLog = $objLogger->getById($nLogID);
            $this->assertFalse(
                boolval($arLog),
                'Получение удалённого лога с корректным ID='.$nLogID
            );
        }

        function testAddLogs(){
            $objLogger = new CCurlLogger();

            $arLogs = array(
                array(
                    "ORDER_NUM" =>  'Б-999999991',
                    "URL"       =>  'https://ya.ru/1?get=sql',
                    "POST_DATA" =>  '',
                    "DATA"      =>  ''
                ),
                array(
                    "ORDER_NUM" =>  '999999991',
                    "URL"       =>  'https://yandex.ru/maps',
                    "POST_DATA" =>  'Post data 01',
                    "DATA"      =>  'Data 01'
                ),
                array(
                    "ORDER_NUM" =>  '999999991',
                    "URL"       =>
                'https://yandex.ru/maps/51/samara/?ll=50.125165%2C53.197532&z=14',
                    "POST_DATA" =>  'Post data 01',
                    "DATA"      =>  'Data 02'
                ),
                array(
                    "ORDER_NUM" =>  '999999991',
                    "URL"       => 'http://perpetum-mobile.ru/',
                    "POST_DATA" =>  'Post data 03',
                    "DATA"      =>  'Data 03'
                ),
            );

            foreach($arLogs as $arLog){
                $nLogID = $objLogger->addLog( $arLog);
                $this->assertTrue(
                    boolval(intval($nLogID)),
                    'Добавление лога и получение его ID'
                );
            }

            $arAnswer = $objLogger->getByOrderNum('999999991');
            $this->assertEquals(3,count($arAnswer),
                "Проверка количества ответов"
            );

            $arAnswer = $objLogger->getByOrderNum('Б-999999991');
            $this->assertEquals(1,count($arAnswer),
                "Проверка количества ответов"
            );

            // Удаление логов по их номеру заказа
            foreach($arLogs as $arLog){
                $objLogger->removeByOrderNum($arLog["ORDER_NUM"]);
                $this->assertFalse(
                    boolval($objLogger->error),
                    'Проверка отсутствия ошибок добавления'
                );
            }

        }
       
        
    }
