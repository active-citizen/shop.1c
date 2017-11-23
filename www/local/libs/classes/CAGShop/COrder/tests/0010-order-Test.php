<?php
    require_once(realpath(__DIR__."/..")."/COrder.class.php");
    require_once(realpath(__DIR__."/../..")."/CCatalog/CCatalogSKU.class.php");
    require_once(realpath(__DIR__."/../..")."/CCatalog/CCatalogStore.class.php");
    use AGPhop\Order as Order;
    use AGPhop\Catalog as Catalog;
    
    class agshopOrderTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        function testOrderParams(){
            $objCOrder = new \Order\COrder;
            
            $num = "1".rand(1,1000000000);
            $this->assertFalse($objCOrder->setParam("Num", "Б-"));
            $this->assertFalse($objCOrder->setParam("Num", $num));
            $this->assertTrue($objCOrder->setParam("Num", "Б-".$num));
            
            $this->assertEquals($objCOrder->getOrderType(),"Б");
            $this->assertEquals($objCOrder->getParam("Num"),"Б-".$num);
            
            $this->assertFalse($objCOrder->setParam("DateInsert", "Дата"));
            $this->assertTrue($objCOrder->setParam("DateInsert", "22.11.2017 12:13:14"));
            $this->assertEquals($objCOrder->getParam("DateInsert"),"2017-11-22 12:13:14");

            $this->assertFalse($objCOrder->setParam("DateUpdate", "Дата"));
            $this->assertTrue($objCOrder->setParam("DateUpdate", "23.12.2016 12:3:1"));
            $this->assertEquals($objCOrder->getParam("DateUpdate"),"2016-12-23 12:03:01");
        }

        function testOrderUser(){
            $objCOrder = new \Order\COrder;
            
            $this->assertFalse($objCOrder->objUser->fetch("ID", "Б-"));
            $this->assertTrue($objCOrder->objUser->fetch("ID", 1));
            $this->assertTrue(boolval($arUser = $objCOrder->objUser->get()));
            
            $this->assertArrayHasKey("ID",$arUser);
            $this->assertArrayHasKey("LOGIN",$arUser);
            $this->assertEquals("admin", $arUser["LOGIN"]);
        }
        
        
        function testOrderProperty(){
            $objCOrder = new \Order\COrder;
            
            $this->assertFalse($objCOrder->setPropertyByCode(md5(rand()),1));
            $this->assertTrue($objCOrder->setPropertyByCode("FIO","Иван Иваныч"));
            $this->assertFalse($objCOrder->getPropertyByCode(md5(rand())));
            $this->assertTrue(boolval($sPropValue = $objCOrder->getPropertyByCode("FIO")));
            $this->assertEquals($sPropValue, "Иван Иваныч");
        }
        
        function testAddUpdateDeleteOrder(){
            $objCOrder = new \Order\COrder;
            $objCSKU = new \Catalog\CCatalogSKU;
            $objCStore = new \Catalog\CCatalogStore;
            
            $nUserId = 1;
            
            // Получаем ID SKU что присутствует на складе
            $this->assertTrue(boolval($arStoreExists = $objCStore->getAnyExists()));
            
            // Получаем информацию по этому SCU
            $this->assertTrue(boolval($objCSKU->fetch($arStoreExists["PRODUCT_ID"])));
            $this->assertTrue(boolval($arSKU = $objCSKU->get()));
            
            // Подгружаем пользователя от которого будем делать заказ
            $this->assertTrue($objCOrder->setParam("UserId", $nUserId));
            
            // Добавляем в заказ одну единицу товара c полученного склада
            $this->assertTrue($objCOrder->addSKU(
                $arStoreExists["PRODUCT_ID"], 
                $arStoreExists["STORE_ID"], 
                $nAmount = intval(rand(1,3))
            ));
            $arSKUs = $objCOrder->getSKUs();
            
            $this->assertArrayHasKey(0,$arSKUs);
            $this->assertArrayHasKey("AMOUNT",$arSKUs[0]);
            $this->assertEquals($nAmount, $arSKUs[0]["AMOUNT"]);
            $this->assertArrayHasKey("SKU",$arSKUs[0]);
            $this->assertArrayHasKey("OFFER",$arSKUs[0]["SKU"]);
            $this->assertArrayHasKey("ID",$arSKUs[0]["SKU"]["OFFER"]);
            $this->assertEquals($arStoreExists["PRODUCT_ID"], $arSKUs[0]["SKU"]["OFFER"]["ID"]);
            
            $this->assertTrue(
                boolval($nOrderId = $objCOrder->createFromSite(
                    "Б-90000".date("Ymd")
                )),
                print_r($objCOrder->getErrors(),1)
            );
        }
  
    }
