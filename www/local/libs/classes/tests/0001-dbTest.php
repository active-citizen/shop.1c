<?php
    class dbTest extends PHPUnit_Framework_TestCase{

        /**
        *   Проверка режима работы MySQL
        */
        function testSQLMode(){
            $varname = "DB";;
            global $$varname;
            $res = $$varname->Query("SHOW variables LIKE '%sql_mode%'");
            $data = $res->Fetch();
            $this->assertEquals('ALLOW_INVALID_DATES',$data['Value']);
        }
        
    }
