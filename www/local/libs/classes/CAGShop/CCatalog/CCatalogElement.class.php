<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\CCache as CCache;

/**
 * Работа с основными параметрами элемента каталога
*/
class CCatalogElement extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        Получение основных параметров элемента каталога по ID
    */
    function getById($nId, $sCacheGroup = ''){
        $nId = intval($nId);
        $objCache = new \Cache\CCache($sCacheGroup,$nId,COMMON_CACHE_TIME);
        if($sCacheGroup && $sCacheData = $objCache->get()){
            return $sCacheData;
        }
        $arResult =  \CIBlockElement::GetList(
            [],[
                "ID"=>$nId
            ],false,[
                "nTopCount"=>1
            ],[
            ]
        )->GetNext();
        if($sCacheGroup)$objCache->set($arResult);
        return $arResult;
    }

    /**
        Получение основных параметров товара по его коду
    */
    function getByCode($sCode, $nIblockId, $sCacheGroup = ''){
        $objCache = new \Cache\CCache($sCacheGroup,$sCode,COMMON_CACHE_TIME);
        if($sCacheGroup && $sCacheData = $objCache->get()){
            return $sCacheData;
        }

        $arResult =  \CIBlockElement::GetList(
            [],[
                "IBLOCK_ID" =>  $nIblockId,
                "CODE"=>$sCode
            ],false,[
                "nTopCount"=>1
            ],[
            ]
        )->GetNext();
        if($sCacheGroup)$objCache->set($arResult);
        return $arResult;
    }


}
