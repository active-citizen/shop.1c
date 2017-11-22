<?php
    require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
    use AGPhop\Catalog as Catalog;
    
    class agshopCatalogStoreTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            
        */
        function testGetAnyExists(){
            $objCStore = new \Catalog\CCatalogStore;
            $this->assertTrue(boolval($arExists = $objCStore->getAnyExists()));
            
            $this->assertArrayHasKey("PRODUCT_ID", $arExists);
            $this->assertArrayHasKey("AMOUNT", $arExists);
            $this->assertArrayHasKey("STORE_ID", $arExists);
            
            $this->assertTrue(boolval($arExists["PRODUCT_ID"]));
            $this->assertTrue(boolval($arExists["AMOUNT"]));
            $this->assertTrue(boolval($arExists["STORE_ID"]));
            
            
            $this->assertTrue(boolval($objCStore->fetch($arExists["STORE_ID"])));
            $this->assertTrue(boolval($arStore = $objCStore->get()));
            $this->assertArrayHasKey("ID", $arStore);
            $this->assertArrayHasKey("TITLE", $arStore);
        }

    }
