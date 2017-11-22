<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

    use AGShop;
    use AGShop\DB as DB;
    
    class CCatalogStore extends \AGShop\CAGShop{
        
        private $arStoreInfo = [];
        
        function __construct(){
            parent::__construct();
            \CModule::IncludeModule('sale');
        }
        
        function getAnyExists(){
            $CDB = new \DB\CDB;
            
            $sQuery = "
                SELECT
                    `PRODUCT_ID`,
                    `AMOUNT`,
                    `STORE_ID`
                FROM
                    `".\AGShop\CAGShop::t_catalog_store_product."`
                WHERE
                    `AMOUNT`>0
                ORDER BY
                    `AMOUNT` DESC
            ";
            $arResult = $CDB->sqlSelect($sQuery);
            return array_pop($arResult);
        }
        
        function fetch($nId){
            $nId = intval($nId);
            $CDB = new \DB\CDB;
            $arResult = $CDB->searchOne(\AGShop\CAGShop::t_catalog_store,[
                "ID"=>$nId
            ]);
            if(!$arResult)return false;
            $this->arStoreInfo = $arResult;
            return true;
        }
        
        function get(){
            return $this->arStoreInfo;
        }
        
    }
