<?php
    require_once(realpath(__DIR__."/..")."/CSSAGHistory.class.php");
    use AGPhop\SSAG as SSAG;
    
    class agshopCatalogSectionTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            
        */
        function testAGHistory(){
            $objSSAGHistory = new \SSAG\CSSAGHistory($sSessionId);
            $arHistory = $objSSAGHistory->get(); 
        }

    }
