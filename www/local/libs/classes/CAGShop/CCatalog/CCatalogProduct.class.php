<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

    use AGShop;
    use AGShop\DB as DB;
    
    class CCatalogProduct extends \AGShop\CAGShop{
        
        function __construct(){
            parent::__construct();
            \CModule::IncludeModule('iblock');
        }
        
        function getProperties($nProductId){
            $nProductId = intval($nProductId);
            $CDB = new \DB\CDB;
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
                    `element_prop`.`IBLOCK_ELEMENT_ID`= ".$nProductId."
            ";
            $arResult = $CDB->sqlSelect($sQuery);
            foreach($arResult as $arItem){
                if(!isset($arProperties[$arItem["CODE"]]))
                    $arProperties[$arItem["CODE"]] = [];
                $arProperties[$arItem["CODE"]][] = $arItem["VALUE"];
            }
            foreach($arProperties as $sCode=>$sValue)
                if(count($sValue)==1)$arProperties[$sCode] = $sValue[0];
            return $arProperties;
        }
        
        function get($nId){
            $arFilter = [
                "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                "ID"=>$nId
            ];
            $arProduct = \CIBlockElement::GetList(
                [],$arFilter,false,[
                    "nTopCount"=>1
                ],[
                    "ID","CODE","NAME","XML_ID"
                ]
            )->Fetch();
            return $arProduct;
        }
        
    }
