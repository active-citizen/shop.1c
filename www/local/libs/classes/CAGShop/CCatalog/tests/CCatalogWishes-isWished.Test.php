<?php
require_once(realpath(__DIR__."/..")."/CCatalogWishes.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogWishes_IsWished_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testIsWished(){
        $objWish = new \Catalog\CCatalogWishes;
        $this->assertTrue(boolval($objWish));
        
        // Получение активного товара
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
                "!IBLOCK_SECTION_ID"=>false,
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();

        $this->assertTrue(is_numeric(
            $objWish->isWished($arProduct["ID"],1)
        ));
    }
}
