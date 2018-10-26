<?php
namespace Report;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
use AGShop;

class CReportType extends \AGShop\CAGShop{
    private $arResult = [];
    private $arParams = [];

    function __construct($arConf = []){
        if(!$arConf)
            $this->loadConfig();
        else
            $this->setParams($arConf);
    }

    function build(){

    }

    function render(){
    }

    function loadConfig(){
        require("config/jira.conf.php");    
        $this->setParams($conf);
    }

    function setParams($arConf){
        foreach($arConf as $sKey=>$sValue)
            $this->setParam($sKey,$sValue);
    }

    function setParam($sKey,$sValue){
        $this->arParams[$sKey] = $sValue;
    }

    function getParam($sKey){
        return $this->arParams[$sKey];
        
    }

    function setResult($arData){
        $this->arResult = $arData;
    }

    function getResult(){
        return $this->arResult;
    }
}
