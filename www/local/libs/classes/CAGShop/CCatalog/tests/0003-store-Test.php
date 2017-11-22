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

        function testGetTitleById(){
            $objCStore = new \Catalog\CCatalogStore;
            $this->assertTrue(boolval($arExists = $objCStore->getAnyExists()));

            $sTitle = $objCStore->getTitleById($arExists["STORE_ID"]);
            $this->assertTrue(boolval($sTitle));
        }

        function testMove(){
            $objCStore = new \Catalog\CCatalogStore;
            $this->assertTrue(boolval($arExists = $objCStore->getAnyExists()));
            
            // Получаем изначальное число на складе
            $nStartCount = $objCStore->getProductAmount(
                $arExists["PRODUCT_ID"], $arExists["STORE_ID"]
            );
            $this->assertTrue($nStartCount==$arExists["AMOUNT"]);
            
            // Снимаем 2 и проверяем число
            $this->assertTrue(
                $objCStore->move(
                    $arExists["PRODUCT_ID"], $arExists["STORE_ID"], -2
                ),
                print_r($objCStore->getErrors(),1)
            );
            $nMiddleCount = $objCStore->getProductAmount(
                $arExists["PRODUCT_ID"], $arExists["STORE_ID"]
            );
            $this->assertTrue($nMiddleCount==$arExists["AMOUNT"]-2);

            // Кладём 1 и проверяем число
            $this->assertTrue(
                $objCStore->move(
                    $arExists["PRODUCT_ID"], $arExists["STORE_ID"], 1
                ),
                print_r($objCStore->getErrors(),1)
            );
            $nPreEndCount = $objCStore->getProductAmount(
                $arExists["PRODUCT_ID"], $arExists["STORE_ID"]
            );
            $this->assertTrue($nPreEndCount==$arExists["AMOUNT"]-1);


            // Кладём 1 и проверяем число (должно стать изначальным)
            $this->assertTrue(
                $objCStore->move(
                    $arExists["PRODUCT_ID"], $arExists["STORE_ID"], 1
                ),
                print_r($objCStore->getErrors(),1)
            );
            $nEndCount = $objCStore->getProductAmount(
                $arExists["PRODUCT_ID"], $arExists["STORE_ID"]
            );
            $this->assertTrue($nEndCount==$arExists["AMOUNT"]);

        }
    }
