<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\CCache as CCache;

/**
 * Поиск по элементам каталога
*/
class CCatalogSearch extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        Получение элементов каталога по подстроке имени
        @param $sQuery - поисковый запрос
        @param $SectionCond - опциональный массив ID разделов среди которых искать
    */
    function getIdsByProductName($sQuery, $SectionCond = []){
        if(!trim($sQuery))return [];
        
        $CDB = new \DB\CDB;
        
        $sQuery = $CDB->forSql($sQuery);
        $arQueryCond = [];
        $sQuery = "
            SELECT
                `ID` as `ID`
            FROM
                `".\AGShop\CAGShop::t_iblock_element."`
            WHERE
                `IBLOCK_ID`=".CATALOG_IB_ID."
                ".($SectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode($SectionCond).")":"")."
                ".($arFilter["query"]?"AND `NAME` LIKE '%".$sQuery."%'":"")."
        ";
        $arIds = $CDB->sqlSelect($sQuery,10000);
        foreach($arIds as $arId)$arQueryCond[] = $arId["ID"];
        return $arQueryCond;
    }

}
