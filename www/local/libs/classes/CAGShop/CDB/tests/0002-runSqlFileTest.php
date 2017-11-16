<?php
    require_once(realpath(__DIR__."/../CDB.class.php"));
    use AGShop\DB as DB;


    class runSqlFileTest extends PHPUnit_Framework_TestCase{

        /**
        *   Тест загрузки sql-файла
        */
        function testRunSql(){

            $objCDB = new \DB\CDB;

            $this->assertFalse(
                $objCDB->runSqlFile("data/missing-file.sql"),
                print_r($objCDB, 1)
            );

            $objCDB->clearError();

            $this->assertTrue(
                $objCDB->runSqlFile(realpath(__DIR__)."/data/drop.sql"),
                print_r($objCDB, 1)
            );

            $this->assertTrue(
                $objCDB->runSqlFile(realpath(__DIR__)."/data/create.sql"),
                print_r($objCDB, 1)
            );

            $objCDB->clearError();


            $this->assertEquals(
                $objCDB->sqlSelect("SELECT * FROM `rTd6sj3Ghsl9a`"),
                [["id"=>1,"name"=>"a"],["id"=>2,"name"=>"b"]]
            );

            $this->assertTrue(
                $objCDB->runSqlFile(realpath(__DIR__)."/data/drop.sql"),
                print_r($objCDB, 1)
            );

        }
        
    }
