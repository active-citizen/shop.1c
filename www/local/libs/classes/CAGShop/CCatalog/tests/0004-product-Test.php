<?php
    require_once(realpath(__DIR__."/..")."/CCatalogStore.class.php");
    use AGPhop\Catalog as Catalog;
    
    class agshopCatalogProductTest extends PHPUnit_Framework_TestCase{

        function testGetAnyExists(){
            $objCProduct = new \Catalog\CCatalogProduct;
            $this->assertTrue(boolval($arExists = $objCProduct->getAnyExists()));
            $this->assertArrayHasKey("ID",$arExists);
            $this->assertArrayHasKey("NAME",$arExists);
            $this->assertArrayHasKey("CODE",$arExists);
            $this->assertArrayHasKey("XML_ID",$arExists);
            
            $this->assertTrue(boolval($arProduct = $objCProduct->get($arExists["ID"])));
            $this->assertArrayHasKey("ID", $arProduct);
            $this->assertArrayHasKey("NAME", $arProduct);
            $this->assertArrayHasKey("CODE", $arProduct);
            $this->assertArrayHasKey("XML_ID", $arProduct);
            
            $this->assertEquals($arProduct["ID"],$arExists["ID"]);
            $this->assertEquals($arProduct["CODE"],$arExists["CODE"]);
            $this->assertEquals($arProduct["XML_ID"],$arExists["XML_ID"]);
            $this->assertEquals($arProduct["NAME"],$arExists["NAME"]);
            
            $this->assertTrue(boolval($arProductByCode = $objCProduct->getByCode($arExists["CODE"])));
            $this->assertArrayHasKey("ID", $arProductByCode);
            $this->assertArrayHasKey("NAME", $arProductByCode);
            $this->assertArrayHasKey("CODE", $arProductByCode);
            $this->assertArrayHasKey("XML_ID", $arProductByCode);
            
            $this->assertEquals($arProductByCode["ID"],$arExists["ID"]);
            $this->assertEquals($arProductByCode["CODE"],$arExists["CODE"]);
            $this->assertEquals($arProductByCode["XML_ID"],$arExists["XML_ID"]);
            $this->assertEquals($arProductByCode["NAME"],$arExists["NAME"]);
            
            $this->assertTrue(boolval(
                $arProductProperties = 
                    $objCProduct->getPropertiesForCard($arExists["ID"])
            ));
            
        }
        

    }
