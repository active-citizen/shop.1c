<?php
    class phpTest extends PHPUnit_Framework_TestCase{

        /**
        *   Проверка mbstring.internal_encoding
        */
        function testMbstringInternalEncoding(){
            $this->assertEquals('UTF-8',ini_get("mbstring.internal_encoding"), "Проверка mbstring.internal_encoding");
        }
        
        /**
        *   Проверка mbstring.func_overload
        */
        function testMbstringFuncOverload(){
            $this->assertEquals('2',ini_get("mbstring.func_overload"), "Проверка mbstring.func_overload");
        }
        
        /**
        *   Проверка pcre.recursion_limit
        */
        function testPcreRecursionLimit(){
            $this->assertEquals('10000',ini_get("pcre.recursion_limit"), "Проверка pcre.recursion_limit");
        }
        
        
    }
