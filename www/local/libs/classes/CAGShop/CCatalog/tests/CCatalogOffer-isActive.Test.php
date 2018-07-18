<?php
require_once(realpath(__DIR__."/..")."/CCatalogOffer.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogOfferTest extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testIsActive(){
        $objOffer = new \Catalog\CCatalogOffer;
        $this->assertTrue(boolval($objOffer));

        // Получение активного раздела
        $arSection = \CIBlockSection::GetList(["id"=>"desc"],[
            "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
            "ACTIVE"=>"Y"
        ],false,[
            "ACTIVE","ID","NAME",
        ])->Fetch();
        
        // Получение активного товара
        $arProduct = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
                "IBLOCK_SECTION_ID"=>$arSection["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();
        
        // Получение активного предложения
        $arOffer = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID"=>$objOffer->IBLOCKS["OFFER"],
                "PROPERTY_CML2_LINK"=>$arProduct["ID"],
                "ACTIVE"=>"Y"
            ],false,["nTopCount"=>1],
            ["ID","ACTIVE","NAME"]
        )->Fetch();

        $this->assertTrue(boolval($objOffer->isActive($arOffer["ID"])));
    }
}
