<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

    use AGShop;
    use AGShop\DB as DB;
    
    class CCatalogSKU extends \AGShop\CAGShop{
        
        private $arSKUInfo = [];
        
        function __construct(){
            parent::__construct();
            \CModule::IncludeModule('iblock');
        }
        
        function fetch($nId = ''){
            $nId = intval($nId);
            $CDB = new \DB\CDB;
            
            $arFilter = ["IBLOCK_ID" =>  $this->IBLOCKS["OFFER"]];
            if($nId)$arFilter["ID"] = $nId;
            
            $arOffer = \CIBlockElement::GetList([
                "ID"=>"DESC"
            ],$arFilter,false,[
                "nTopCount"=>1
            ],[
                "ID","NAME"
            ])->Fetch();
            
            $arProperties = [];
            if(isset($arOffer["ID"])){
                $sQuery = "
                    SELECT
                        `element_prop`.`VALUE` as `VALUE`,
                        `prop`.`CODE` as `CODE`
                    FROM
                        `".\AGShop\CAGShop::t_iblock_element_property."` as `element_prop`
                            LEFT JOIN
                        `".\AGShop\CAGShop::t_iblock_property."` as `prop`
                            ON
                            `prop`.`ID`=`element_prop`.`IBLOCK_PROPERTY_ID`
                    WHERE
                        `element_prop`.`IBLOCK_ELEMENT_ID`= ".$arOffer["ID"]."
                ";
                $arResult = $CDB->sqlSelect($sQuery);
                foreach($arResult as $arItem)
                    $arProperties[$arItem["CODE"]] = $arItem["VALUE"];
            }

            $arStores = [];
            if(isset($arProperties["CML2_LINK"]) && $arProperties["CML2_LINK"]){
                $sQuery = "
                    SELECT
                        `store_product`.`STORE_ID`,
                        `store_product`.`AMOUNT`
                    FROM
                        `".\AGShop\CAGShop::t_catalog_store_product."` as `store_product`
                    WHERE
                        `store_product`.`PRODUCT_ID`=".$arOffer["ID"]."
                ";
                $arResult = $CDB->sqlSelect($sQuery);
                foreach($arResult as $arItem)
                    $arStores[$arItem["STORE_ID"]] = $arItem["AMOUNT"];
            }

            
            if(isset($arProperties["CML2_LINK"]) && $arProperties["CML2_LINK"]){
                $arFilter = [
                    "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                    "ID"=>$arProperties["CML2_LINK"]
                ];
                $arProduct = \CIBlockElement::GetList(
                    [],$arFilter,false,[
                        "nTopCount"=>1
                    ],[
                        "ID","CODE","NAME"
                    ]
                )->Fetch();
            }
            
            $this->arSKUInfo = [
                "OFFER"         =>  $arOffer,
                "PROPERTIES"    =>  $arProperties,
                "STORES"        =>  $arStores,
                "PRODUCT"       =>  $arProduct
            ];
            return true;
        }
        
        function get(){
            return $this->arSKUInfo;
        }
        
    }
