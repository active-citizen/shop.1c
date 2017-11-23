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
        
        function getTitleById($nId){
            $this->fetch($nId);
            return $this->arStoreInfo["TITLE"];
        }
        
        /**
            Получение количества товара на нужном складе
        */
        function getProductAmount($nProductId, $nStoreId){
            $CDB = new \DB\CDB;
            $arResult = $CDB->searchOne(
                \AGShop\CAGShop::t_catalog_store_product,[
                    "PRODUCT_ID"=>$nProductId, "STORE_ID"=>$nStoreId
                ],["AMOUNT"]
            );
            return $arResult["AMOUNT"];
        }
        
        /*
            Движение продукта на складе
        */
        function move($nProductId, $nStoreId, $nAmount){
            $nProductId = intval($nProductId);
            $nStoreId = intval($nStoreId);
            $nAmount = intval($nAmount);
            
            if(!$nAmount){
                $this->addError("Не указано число изымаемого товара");
                return false;
            }
            if(!$nProductId){
                $this->addError("Не указан продукт для движения по складу");
                return false;
            }
            if(!$nStoreId){
                $this->addError("Не указан склад для движения по складу");
                return false;
            }
            $CDB = new \DB\CDB;
            $sQuery = "
                UPDATE
                    `".\AGShop\CAGShop::t_catalog_store_product."`
                SET 
                    `AMOUNT`=`AMOUNT`+$nAmount
                WHERE
                    `PRODUCT_ID` = $nProductId
                    AND `STORE_ID` = $nStoreId
                LIMIT
                    1
            ";
            $CDB->sqlQuery($sQuery);
            return true;
        }
        
        /**
            Наличие торгового предложения на складах
        */
        function exists($nProductId){
            $nProductId = intval($nProductId);
            if(!$nProductId){
                $this->addError("Не указан продукт для отображения наличия на складах");
                return false;
            }
            $arStores = [];
            $CDB = new \DB\CDB;
            $sQuery = "
                SELECT
                    `store_product`.`STORE_ID`,
                    `store_product`.`AMOUNT`
                FROM
                    `".\AGShop\CAGShop::t_catalog_store_product."` as `store_product`
                WHERE
                    `store_product`.`PRODUCT_ID`=".$nProductId."
            ";
            $arResult = $CDB->sqlSelect($sQuery);
            foreach($arResult as $arItem)
                $arStores[$arItem["STORE_ID"]] = $arItem["AMOUNT"];
            
            return $arStores;
        }
        
    }
