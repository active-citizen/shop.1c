<?php
    class phpTest extends PHPUnit_Framework_TestCase{

        /**
        *   Проверка основных настроек php
        */
        function testVariables(){
            
            $this->assertEquals('UTF-8',ini_get("mbstring.internal_encoding"), "Check mbstring.internal_encoding");
            $this->assertEquals('2',ini_get("mbstring.func_overload"), "Check mbstring.func_overload");
            $this->assertEquals('10000',ini_get("pcre.recursion_limit"), "Check pcre.recursion_limit");
        }
        
    }
