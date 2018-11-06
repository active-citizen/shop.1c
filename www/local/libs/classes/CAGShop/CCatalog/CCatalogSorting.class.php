<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");

use AGShop;

/**
 * Работа с параметрами сортировки плитки
*/
class CCatalogSorting extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
    }
   
    
    /**
        @param $arOptions - массив параметров сортировки из компонента плитки
        @return массив сортировки для применения в поиске по инфоблоку
    */
    function getIBlockSorting($arOptionSorting){
        
        $arOptions["sorting"] = $arOptionSorting;
        
        if(!isset($arOptions["sorting"]["param"]))$arOptions["sorting"]["param"]
            = 'fresh';
        if(!isset($arOptions["sorting"]["direction"]))
            $arOptions["sorting"]["direction"] = 'desc';
            
        $arOptions["sorting"]["direction"] = strtoupper($arOptions["sorting"]["direction"]);
        
        $arSorting = [];
        $arSortingParams = [
            'price' =>"PROPERTY_MINIMUM_PRICE",
            'hit'   =>"PROPERTY_SALELEADER",
            'new'   =>"PROPERTY_NEWPRODUCT",
            'sale'  =>"PROPERTY_SPECIALOFFER",
            'wishes'=>"PROPERTY_WISHES_QUANTITY",
            'rating'=>"PROPERTY_RATING",
            'fresh' =>"TIMESTAMP_X",
        ];
        foreach($arSortingParams as $sParam=>$sSort)
            if($arOptions["sorting"]["param"]==$sParam){
                $arSorting[$sSort] = $arOptions["sorting"]["direction"];
                break;
            }

        /**
         * Чо за муть?
        $arSortingTypes = ["price","rating","favorites","new","hit","wishes"];
        foreach($arSortinTypes as $sSortingType)
            if(
                in_array($arOptions["sorting"]["param"])
                && $arOptions["sorting"]["direction"]=='asc'
                && $arOptions["sorting"]["direction"]=='desc'
            )$arSorting[$arOptions["sorting"]["param"]] 
                = $arOptions["sorting"]["direction"];
        */
                
        if(!$arSorting)$arSorting = ["TIMESTAMP_X"=>"desc"];
        $arSorting["ID"] = "desc";
        
        return $arSorting;
    }

}
