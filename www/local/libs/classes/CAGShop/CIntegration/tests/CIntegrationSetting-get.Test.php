<?php
require_once(realpath(__DIR__."/..")."/CIntegrationSetting.class.php");
use AGPhop\Integration as Integration;

class CIntegrationSettings_Get_Test extends PHPUnit_Framework_TestCase{
    function __construct(){
    }
    
    function testGet(){
        $objIntegrationSettings = new \Integration\CIntegrationSettings;
        $this->assertTrue(boolval($objIntegrationSettings));
        
        $arSettings = $objIntegrationSettings->get();
        $this->assertTrue(boolval(count($arSettings)));
        
        foreach($arSettings as $sSettingName=>$arSetting){
            $this->assertArrayHasKey("TITLE",$arSetting);
            $this->assertArrayHasKey("VALUE",$arSetting);
        }

        
    }
}
