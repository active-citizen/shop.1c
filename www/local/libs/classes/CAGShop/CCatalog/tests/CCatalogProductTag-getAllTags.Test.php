<?php
require_once(realpath(__DIR__."/..")."/CCatalogProductTag.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProductTag_GetAllTags_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    /**
        
    */
    function testGetAllTags(){

        // Получение активного раздела
        $arSection = \CIBlockSection::GetList(["id"=>"desc"],[
            "IBLOCK_ID"=>$objOffer->IBLOCKS["CATALOG"],
            "ACTIVE"=>"Y"
        ],false,[
            "ACTIVE","ID","NAME","CODE"
        ])->Fetch();

        
        $objTag = new \Catalog\CCatalogProductTag;
        
        $objTag = new \Catalog\CCatalogProductTag(
            $objTag->getPropId("catalog","clothes","INTERESTS")
        );
        $this->assertTrue(boolval($objTag));
        
        $arTags = $objTag->getAllTags($arSection["CODE"]);
        $this->assertTrue(boolval($arTags));

        $objTag = new \Catalog\CCatalogProductTag(
            $objTag->getPropId("catalog","clothes","WANTS")
        );
        $this->assertTrue(boolval($objTag));

        $arTags = $objTag->getAllTags('');
        $this->assertTrue(boolval($arTags));
    }
}
