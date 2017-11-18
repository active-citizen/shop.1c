<?php
    require_once(realpath(__DIR__."/..")."/CSection.class.php");
    use AGPhop\Section as Section;
    
    class agshopSearchStemTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            
        */
        function testSectionList(){
            $objCSection = new \Section\CSection;
            
            $this->assertTrue(boolval($arSections = $objCSection->get()));
            foreach($arSections as $nSectionId=>$arSection){
                $this->assertArrayHasKey("ID",$arSection);
                $this->assertArrayHasKey("CODE",$arSection);
                $this->assertArrayHasKey("NAME",$arSection);
                $this->assertArrayHasKey("ACTIVE",$arSection);
                $this->assertTrue(boolval($arSection["ACTIVE"]=='Y'));
            }
            
        }

    }
