<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\CCache as CCache;

/**
 * Работа с ценами товаров
*/
class CCatalogPrice extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        Получение элементов каталога по вилке цены
        @param $nPriceMin - минимальная цена
        @param $nPriceMax - максимальная цена
        @param $SectionCond - опциональный массив ID разделов среди которых искать
    */
    function getProductsByPrice($nPriceMin=0,$nPriceMax=0,$SectionCond=[]){
        $sPriceCond = [];
        if(!$nPriceMin && !$nPriceMax)return [];
        $nPriceMin = intval($nPriceMin);
        $nPriceMax = intval($nPriceMax);
        
        $CDB = new \DB\CDB;
        
        $sQuery = "
            SELECT
                `IBLOCK_ELEMENT_ID` as `ID`
            FROM
                `".\AGShop\CAGShop::t_iblock_element_property."`
            WHERE
                `IBLOCK_PROPERTY_ID`=".PRICE_PROPERTY_ID."
                ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
                ".($nPriceMin?"AND `VALUE_NUM`>=".$nPriceMin:"")."
                ".($nPriceMax?"AND `VALUE_NUM`<=".$nPriceMax:"")."
            GROUP BY
                `IBLOCK_ELEMENT_ID`
        ";
        $arIds = $CDB->sqlSelect($sQuery,10000);
        foreach($arIds as $arId)$sPriceCond[] =$arId["ID"];
        
        return $sPriceCond;
    }

}
