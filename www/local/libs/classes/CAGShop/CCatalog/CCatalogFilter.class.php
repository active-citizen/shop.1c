<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;

/**
 * Работа с параметрами сортировки плитки
*/
class CCatalogFilter extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        @param $arOptions - массив параметров сортировки из компонента плитки
        @return массив сортировки для применения в поиске по инфоблоку
    */
    function getIBlockFilter($arOptionFilter){
        
        $arOptions["filter"] = $arOptionFilter;
        $CDB = new \DB\CDB;
        
        $arFilters = [
            "interest"  =>$arOptions["filter"]["interest"],
            "price_min" =>intval($arOptions["filter"]["price_min"]),
            "price_max" =>intval($arOptions["filter"]["price_max"]),
            "hit"       =>intval($arOptions["filter"]["hit"]),
            "new"       =>intval($arOptions["filter"]["new"]),
            "sale"      =>intval($arOptions["filter"]["sale"]),
            "query"     =>$CDB->ForSql($arOptions["filter"]["query"]),
            "section_code"=>trim($CDB->ForSql($arOptions["filter"]["section_code"]))
        ];

        foreach($arFilters as $sParam=>$Value)
            if(
                isset($arOptions["filter"][$sParam])
                && $arOptions["filter"][$sParam]
            )$arFilter[$sParam] = $Value;
        
        if(isset($arOptions["filter"]["store"]) && is_array($arOptions["filter"]["store"]))
            $arFilter["store"] = $CDB->ForSql(implode(",",$arOptions["filter"]["store"]));
        elseif(isset($arOptions["filter"]["store"]))
            $arFilter["store"] = $CDB->ForSql($arOptions["filter"]["store"]);
        
        if(isset($arFilter["store"]) && $arFilter["store"]==333)
            unset($arFilter["store"]);

        foreach($arFilter["interest"] as $nKey=>$nVal)
            if(!$nVal)unset($arFilter["interest"][$nKey]);
        
        return $arFilter;
    }

}
