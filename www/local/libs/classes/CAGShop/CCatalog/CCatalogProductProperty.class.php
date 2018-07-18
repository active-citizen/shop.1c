<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\DB as DB;

class CCatalogproductProperty extends \AGShop\CAGShop{

    /**
        Установлено ли у продукта свойство
        * 
        @param $sPropertyId - ID свойства элемента инфоблока по которому ишем
        @param $arSectionCond - масссив ID продуктов, среди которых ищем с установленным флагом
        @return - массив ID товаров, у которых установлен флаг
    */
    function getFlagedProducts($nPropertyId,$arSectionCond = []){
        if(!$nPropertyId)return [];
        
        $CDB = new \DB\CDB;
        
        $sNewCond = [];
        $sQuery = "
            SELECT
                `IBLOCK_ELEMENT_ID` as `ID`
            FROM
                `".\AGShop\CAGShop::t_iblock_element_property."`
            WHERE
                `IBLOCK_PROPERTY_ID`=".$nPropertyId."
                ".($arSectionCond?"AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
                
        ";
        $arIds = $CDB->sqlSelect($sQuery,10000);
        foreach($arIds as $arId)$sNewCond[] =$arId["ID"];
        return $sNewCond;
    }
}
