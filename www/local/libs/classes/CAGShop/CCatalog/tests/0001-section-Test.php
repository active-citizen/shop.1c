<?php
    require_once(realpath(__DIR__."/..")."/CCatalogSection.class.php");
    use AGPhop\Catalog as Catalog;
    
    class agshopCatalogSectionTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            
        */
        function testSectionList(){
            $objCSection = new \Catalog\CCatalogSection;
            
            $this->assertTrue(boolval($arSections = $objCSection->get()));
            foreach($arSections as $nSectionId=>$arSection){
                $this->assertArrayHasKey("ID",$arSection);
                $this->assertArrayHasKey("CODE",$arSection);
                $this->assertArrayHasKey("NAME",$arSection);
                $this->assertArrayHasKey("ACTIVE",$arSection);
                $this->assertTrue(boolval($arSection["ACTIVE"]=='Y'));
            }
            
            $this->assertTrue(boolval($arSectionById = $objCSection->getById($arSection["ID"])));
            $this->assertArrayHasKey("ID",$arSectionById);
            $this->assertArrayHasKey("CODE",$arSectionById);
            $this->assertArrayHasKey("NAME",$arSectionById);
            $this->assertArrayHasKey("ACTIVE",$arSectionById);
            
            
            
            
        }

    }
