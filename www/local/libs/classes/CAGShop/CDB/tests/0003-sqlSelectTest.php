<?php
    require_once(realpath(__DIR__."/../CDB.class.php"));
    use AGShop\DB as DB;


    class sqlSelectTest extends PHPUnit_Framework_TestCase{

        /**
        *   Тест выполнения SQL-запроса SELECT
        */
        function testRunSql(){

            $objCDB = new \DB\CDB;

            $sWrongSQlQuery = "SELECT * FREN";
            $sRightSQLQuery = "SHOW TABLES;";

            $this->assertFalse(
                $objCDB->sqlSelect($sWrongSQlQuery),
                print_r($objCDB, 1)
            );

            $this->assertTrue(
                boolval($arResult = $objCDB->sqlSelect($sRightSQLQuery)),
                print_r($objCDB, 1)
            );

        }
        
    }
