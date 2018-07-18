<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\CCache as CCache;

/**
 * Работа с флагами элемента каталога
*/
class CCatalogEnums extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        Получение полного списка флагов
    */
    function getAll(){
        
        $objCache = new \Cache\CCache("CatalogEnums", 0, COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get())return $sCacheData;
        
        $res = \CIBlockPropertyEnum::GetList([],["IBLOCK_ID"=>CATALOG_IB_ID]);
        $ENUMS = [];
        while($data = $res->getNext()){
            $enum = \CIBlockPropertyEnum::GetByID($data["ID"]);
            if(!isset($ENUMS[$data["PROPERTY_CODE"]]))
                $ENUMS[$data["PROPERTY_CODE"]] = array();
            $ENUMS[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
        }
        
        return $objCache->set($ENUMS);
    }

}
