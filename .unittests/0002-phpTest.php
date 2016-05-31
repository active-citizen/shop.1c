<?php
    class phpTest extends PHPUnit_Framework_TestCase{

        /**
        *   Проверка основных настроек php
        */
        function testMbstringInternalEncoding(){
            $this->assertEquals('UTF-80',ini_get("mbstring.internal_encoding"), "Проверка mbstring.internal_encoding");
        }
        
        function testMbstringFuncOverload(){
            $this->assertEquals('2',ini_get("mbstring.func_overload"), "Проверка mbstring.func_overload");
        }
        
        function testPcreRecursionLimit(){
            $this->assertEquals('100000',ini_get("pcre.recursion_limit"), "Проверка pcre.recursion_limit");
        }
        
        
    }
