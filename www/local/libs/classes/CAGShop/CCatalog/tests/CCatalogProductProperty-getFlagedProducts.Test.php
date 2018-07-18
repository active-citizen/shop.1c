<?php
require_once(realpath(__DIR__."/..")."/CCatalogProductProperty.class.php");
use AGPhop\Catalog as Catalog;

class CCatalogProductProperty_GetFlagedProducts_Test extends PHPUnit_Framework_TestCase{
    function __construct($sTroykaNum){
    }
    /**
        
    */
    function testGetFlagedProducts(){
        $objProperty = new \Catalog\CCatalogProductProperty;
        $this->assertTrue(boolval($objProperty));

        // Определяем ID свойств
        $arProperties = [
            "NEWPRODUCT"=>0,
            "SALELEADER"=>0,
            "SPECIALOFFER"=>0
        ];
        foreach($arProperties as $sCode=>$nNum)
            $arProperties[$sCode] = 
                $objProperty->getPropId("catalog","clothes",$sCode);
        
        // Для каждого из флаго находим один продукт с флагом и один без и
        // проверяем его наличие/отсуствтвие
        foreach($arProperties as $sPropCode=>$nPropId){
            // Получаем продукт с утановленным флагом
            $arProduct = \CIBlockElement::GetList(
                [],$arFields = [
                    "IBLOCK_ID"=>$objProperty->IBLOCKS["CATALOG"],
                    "!PROPERTY_$sPropCode"=>false
                ],false,["nTopCount"=>1],
                ["ID","CODE","PROPERTY_$sPropCode"]
            )->Fetch();
            // Проверяем наличие флага
            $this->assertTrue(boolval($objProperty->getFlagedProducts(
                $nPropId,[$arProduct["ID"]]
            )));

            // Получаем продукт без утановленного флага
            $arProduct = \CIBlockElement::GetList(
                [],$arFields = [
                    "IBLOCK_ID"=>$objProperty->IBLOCKS["CATALOG"],
                    "PROPERTY_$sPropCode"=>false
                ],false,["nTopCount"=>1],
                ["ID","CODE","PROPERTY_$sPropCode"]
            )->Fetch();
            // Проверяем отсутствие флага
            $this->assertFalse(boolval($objProperty->getFlagedProducts(
                $nPropId,[$arProduct["ID"]]
            )));
            
        }

        

        
    }
}
