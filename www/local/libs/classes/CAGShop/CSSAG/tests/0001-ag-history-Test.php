<?php
    require_once(realpath(__DIR__."/..")."/CSSAGHistory.class.php");
    use AGPhop\SSAG as SSAG;
    
    class agshopCatalogSectionTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            
        */
        function testAGHistory(){

            $sSessionId = "35e725eb58af883e669ab9b92a267a59";

            $objSSAGHistory = new \SSAG\CSSAGHistory($sSessionId);
            $arHistory = $objSSAGHistory->get(); 
        }

    }
