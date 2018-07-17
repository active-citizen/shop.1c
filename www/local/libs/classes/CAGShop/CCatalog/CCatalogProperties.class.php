<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\CCache as CCache;

/**
 * Работа со свойствами элемента каталога
*/
class CCatalogProperties extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        Получение свойств конкретного элемента каталога (товара или торгового предложения)
        @param $nOfferId - ID торгового предложения
        @return массив свойств торгового предложения
    */
    function getById($nId, $sCacheGroup = ""){
        $nOfferId = intval($nOfferId);

        $objCache = new \Cache\CCache($sCacheGroup, $nId);
        if($sCacheGroup && $sCacheData = $objCache->get()){
            return $sCacheData;
        }

        $CDB = new \DB\CDB;
        $sQuery = "
            SELECT
                `element_prop`.`VALUE` as `VALUE`,
                `prop`.`CODE` as `CODE`
            FROM
                `".\AGShop\CAGShop::t_iblock_element_property."` as `element_prop`
                    LEFT JOIN
                `".\AGShop\CAGShop::t_iblock_property."` as `prop`
                    ON
                    `prop`.`ID`=`element_prop`.`IBLOCK_PROPERTY_ID`
            WHERE
                `element_prop`.`IBLOCK_ELEMENT_ID`= ".$nId."
        ";
        $arResult = $CDB->sqlSelect($sQuery);
        $arProperties = [];
        foreach($arResult as $arItem){
            if(!isset($arProperties[$arItem["CODE"]]))
                $arProperties[$arItem["CODE"]] = [];
            $arProperties[$arItem["CODE"]][] = $arItem["VALUE"];
        }
        foreach($arProperties as $sCode=>$sValue)
            if(count($sValue)==1)$arProperties[$sCode] = $sValue[0];
        if($sCacheGroup)$objCache->set($arProperties);
        return $arProperties;
    }

}
