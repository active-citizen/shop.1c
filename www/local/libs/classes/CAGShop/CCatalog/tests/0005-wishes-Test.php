<?php
    require_once(realpath(__DIR__."/..")."/CCatalogProduct.class.php");
    require_once(realpath(__DIR__."/..")."/CCatalogWishes.class.php");
    require_once(realpath(__DIR__."/../..")."/COrder/COrder.class.php");
    use AGPhop\Catalog as Catalog;
    use AGPhop\Order as Order;
    
    class agshopCatalogWishesTest extends PHPUnit_Framework_TestCase{

        function testWishes(){
            $objCProduct = new \Catalog\CCatalogProduct;
            $objCWishes = new \Catalog\CCatalogWishes;
            $objCOrder = new \Order\COrder;
            $this->assertTrue(boolval($arExists = $objCProduct->getAnyExists()));
            $this->assertArrayHasKey("ID",$arExists);
            $this->assertArrayHasKey("NAME",$arExists);
            $this->assertArrayHasKey("CODE",$arExists);
            $this->assertArrayHasKey("XML_ID",$arExists);
            
            $arExists = $objCWishes->getCountByCatalogId($arExists["ID"]);
            $arExists = $objCWishes->isWished($arExists["ID"],CUser::GetID());
            
            //$objCOrder->getMounthProductCount(1,$arExists["ID"]);
        }
        

    }
