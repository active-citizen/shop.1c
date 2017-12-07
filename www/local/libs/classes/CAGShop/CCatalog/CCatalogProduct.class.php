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
        
        /**
            Получить свойства элемента каталога по его ID
        */
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
        
        /**
        
            Получение основных параметров товара по ID элемента каталога
        
        */
        function get($nId){
            return \CIBlockElement::GetList(
                [],[
                    "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                    "ID"=>$nId
                ],false,[
                    "nTopCount"=>1
                ],[
                ]
            )->GetNext();
        }
        
        /**
            Получение основных параметров товара по его коду
        */
        function getByCode($sCode){
            return \CIBlockElement::GetList(
                [],[
                    "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                    "CODE"=>$sCode
                ],false,[
                    "nTopCount"=>1
                ],[
                ]
            )->GetNext();
        }
        
        /**
            Получение информации по любому активному продукту
        */
        function getAnyExists(){
            return \CIblockElement::GetList([
                "ID"=>"DESC"
            ],[
                "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                "ACTIVE"=>"Y"
            ],false,[
                "nTopCount"=>1
            ],[
                "ID","CODE","NAME","XML_ID"
            ])->Fetch();
        }
        
        /**
            Свойства товара для формирования картоуи товара
            @param $nId - ID элемента каталога
        */
        function getPropertiesForCard($nId){
            $arResult = [];
            $resProps = \CIBlockElement::GetProperty(
                $this->IBLOCKS["CATALOG"],$nId
            );
            while($arProp = $resProps->GetNext()){
                if(!isset($arResult[$arProp["CODE"]]))
                    $arResult[$arProp["CODE"]] = [];
                if($arProp["PROPERTY_TYPE"]=='F')
                    $arProp["FILE_PATH"] = \CFile::GetPath($arProp["VALUE"]);
                $arResult[$arProp["CODE"]] = $arProp;
            }
            return $arResult;
        }
        
    }
