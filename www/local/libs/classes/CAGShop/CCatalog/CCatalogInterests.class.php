<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\CCache as CCache;

/**
 * Работа с интересами
*/
class CCatalogInterests extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        Получение элементов каталога по ID интересов
        @param $arInterest - массив ID интересов
        @param $SectionCond - опциональный массив ID разделов среди которых искать
    */
    function getProductsByIds($arInterest, $SectionCond = []){
        if(!$arInterest)return [];

        $CDB = new \DB\CDB;
        
        // Выбираем ID товаров, подходящих по интересу
        $sInterestCond = [];
        $sQuery = "
            SELECT
                `IBLOCK_ELEMENT_ID` as `ID`
            FROM
                `".\AGShop\CAGShop::t_iblock_element_property."`
            WHERE
                `IBLOCK_PROPERTY_ID`=".INTEREST_PROPERTY_ID."
                -- ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
                ".($arInterest?"AND `VALUE_NUM`
                IN(".implode(",",$arInterest).")":"")."
            GROUP BY
                `IBLOCK_ELEMENT_ID`
        ";
        
        $arIds = $CDB->sqlSelect($sQuery,10000);
        foreach($arIds as $arId)$sInterestCond[] = $arId["ID"];
        return $sInterestCond;
    }

}
