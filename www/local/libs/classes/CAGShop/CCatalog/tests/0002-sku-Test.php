<?php
    require_once(realpath(__DIR__."/..")."/CCatalogSKU.class.php");
    use AGPhop\Catalog as Catalog;
    
    class agshopCatalogSKUTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            
        */
        function testList(){
            $objCSKU = new \Catalog\CCatalogSKU;
            $this->assertTrue($objCSKU->fetch());
            $arSKU = $objCSKU->get();
            
            $this->assertArrayHasKey("OFFER",$arSKU);
            $this->assertArrayHasKey("PROPERTIES",$arSKU);
            $this->assertArrayHasKey("STORES",$arSKU);
            $this->assertArrayHasKey("PRODUCT",$arSKU);
            
            $this->assertTrue(boolval($arSKU["PRODUCT"]));
            $this->assertTrue(boolval($arSKU["OFFER"]));
            $this->assertTrue(boolval($arSKU["PROPERTIES"]));
        }

    }
