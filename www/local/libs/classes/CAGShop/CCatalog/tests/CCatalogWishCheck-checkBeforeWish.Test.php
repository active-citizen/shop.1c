<?php
require_once(realpath(__DIR__."/..")."/CCatalogWishCheck.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogWishCheck_CheckBeforeWish_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testCheckBeforeWish(){
        
        global $USER;
        
        $objCheck = new \Catalog\CCatalogWishCheck;
        $this->assertTrue(boolval($objCheck));
        
        $this->assertFalse($objCheck->checkBeforeWish(0,1));
        $this->assertFalse($objCheck->checkBeforeWish(1,0));
        
        // Получение активного товара
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
                "!IBLOCK_SECTION_ID"=>false,
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();
        
        $this->assertTrue($objCheck->checkBeforeWish($arProduct["ID"],1));
        $this->assertFalse($objCheck->checkBeforeWish(9999999999999999,1));
    }
}
