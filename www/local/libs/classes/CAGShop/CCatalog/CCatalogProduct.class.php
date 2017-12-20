<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProduct.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationParking.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegration.class.php");
        
    use AGShop\Integration as Integration;
    use AGShop\Catalog as Catalog;
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
                $arResult[$arProp["CODE"]][] = $arProp;
            }
            return $arResult;
        }
        
        
        /**
        
            @param $arOptions = [
                "filter"=>[
                    "name"=>"section"   // Имя поля фильтра
                        //(section,interest,store)
                ]
                "sorting"=>[
                    "param"=>price, // Поле по которому сортируем
                        //  (price,rating,favorites,new,hit)

                    "direction"=>asc // Направление сортировки 
                        //  (asc,desc)
                ],
                "pagination"=>[
                    "offset"=>0,
                    "onpage"=>10
                ]
            ]
        */
        function getTeasers($arOptions = []){
            $CDB = new \DB\CDB;;

            $arFilter = [];
            $arSorting = [];

            $ON_PAGE = 12;
            $PAGE = isset($_REQUEST["PAGE"])?intval($_REQUEST["PAGE"]):1;

            // Составляем справочник флагов
            $ENUMS = array();
            $res = \CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>CATALOG_IB_ID));
            while($data = $res->getNext()){
                $enum = \CIBlockPropertyEnum::GetByID($data["ID"]);
                if(!isset($ENUMS[$data["PROPERTY_CODE"]]))$ENUMS[$data["PROPERTY_CODE"]] = array();
                $ENUMS[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
            }
        
            // Составляем список разделов, из которых будем выводить
            $res = \CIBlockSection::GetList(array(),array("ACTIVE"=>"Y"),false,array("ID"));
            $arSectionsIds = array();
            while($arSection = $res->Fetch())$arSectionsIds[] = $arSection["ID"];
            
            $arSortingTypes = ["price","rating","favorites","new","hit"];
            foreach($arSortinTypes as $sSortingType)
                if(
                    in_array($arOptions["sorting"]["param"])
                    && $arOptions["sorting"]["direction"]=='asc'
                    && $arOptions["sorting"]["direction"]=='desc'
                )$arSorting[$arOptions["sorting"]["param"]] 
                    = $arOptions["sorting"]["direction"];
            if(!$arSorting)$arSorting = ["price"=>"desc"];
            
            if(isset($arOptions["filter"]["interest"]))
                $arFilter["interest"] = intval($arOptions["filter"]["interest"]);
            
            if(isset($arOptions["filter"]["price_min"]))
                $arFilter["price_min"] = intval($arOptions["filter"]["price_min"]);
            
            if(isset($arOptions["filter"]["price_max"]))
                $arFilter["price_max"] = intval($arOptions["filter"]["price_max"]);
            
            if(isset($arOptions["filter"]["store"]))
                $arFilter["store"] = intval($arOptions["filter"]["store"]);
            
            if(isset($arOptions["filter"]["hit"]) && $arOptions["filter"]["hit"])
                $arFilter["hit"] = intval($arOptions["filter"]["hit"]);
            
            if(isset($arOptions["filter"]["new"]) && $arOptions["filter"]["new"])
                $arFilter["new"] = intval($arOptions["filter"]["new"]);
            
            if(isset($arOptions["filter"]["sale"]) && $arOptions["filter"]["sale"])
                $arFilter["sale"] = intval($arOptions["filter"]["sale"]);
            
            if(isset($arOptions["filter"]["query"]) && $arOptions["filter"]["query"])
                $arFilter["query"] = $CDB->ForSql($arOptions["filter"]["query"]);
            
            if(isset($arOptions["filter"]["section_code"]))
                $arFilter["section_code"] = trim($CDB->ForSql($arOptions["filter"]["section_code"]));
                
            $nSectionId = 0;
            if(isset($arFilter["section_code"])){
                $arCatalogSection = \CIBlockSection::GetList([],[
                    "CODE"=>$arFilter["section_code"]],false,[
                    "nTopCount"=>1
                    ],["ID"]
                )->GetNext();
                $nSectionId = $arCatalogSection["ID"];
            }
            
            // Выбираем по разделу и по доступности
            $arSectionCond = [];
            $sQuery = "
                SELECT
                    `ID` as `ID`
                FROM
                    `".\AGShop\CAGShop::t_iblock_element."`
                WHERE
                    `IBLOCK_ID`=".CATALOG_IB_ID."
                    AND `IBLOCK_SECTION_ID`!=0
                    ".($nSectionId?"AND `IBLOCK_SECTION_ID`=".$nSectionId:"")."
                    AND `ACTIVE`='Y'
            ";
            $arIds = $CDB->sqlSelect($sQuery,1000);
            foreach($arIds as $arId)$arSectionCond[] = $arId["ID"];
            
            // Выбираем по поисковому запросу
            $arQueryCond = [];
            if($arFilter["query"]){
                $sQuery = "
                    SELECT
                        `ID` as `ID`
                    FROM
                        `".\AGShop\CAGShop::t_iblock_element."`
                    WHERE
                        `IBLOCK_ID`=".CATALOG_IB_ID."
                        ".($SectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode($SectionCond).")":"")."
                        ".($arFilter["query"]?"AND `NAME` LIKE '%".$arFilter["query"]."%'":"")."
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$arQueryCond[] = $arId["ID"];
            }
            
            
            // Выбираем ID товаров, подходящих по интересу
            $sInterestCond = [];
            if($arFilter["interest"]){
                $sQuery = "
                    SELECT
                        `IBLOCK_ELEMENT_ID` as `ID`
                    FROM
                        `".\AGShop\CAGShop::t_iblock_element_property."`
                    WHERE
                        `IBLOCK_PROPERTY_ID`=".INTEREST_PROPERTY_ID."
                        ".($SectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode($SectionCond).")":"")."
                        ".($arFilter["interest"]?"AND `VALUE_NUM`=".$arFilter["interest"]:"")."
                    GROUP BY
                        `IBLOCK_ELEMENT_ID`
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sInterestCond = $arId["ID"];
            }
            
            // Выбираем ID товаров, подходящих по цене
            $sPriceCond = [];
            if($arFilter["price_min"] || $arFilter["price_max"]){
                $sQuery = "
                    SELECT
                        `IBLOCK_ELEMENT_ID` as `ID`
                    FROM
                        `".\AGShop\CAGShop::t_iblock_element_property."`
                    WHERE
                        `IBLOCK_PROPERTY_ID`=".PRICE_PROPERTY_ID."
                        ".($SectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode($SectionCond).")":"")."
                        ".($arFilter["price_min"]?"AND `VALUE_NUM`>=".$arFilter["price_min"]:"")."
                        ".($arFilter["price_max"]?"AND `VALUE_NUM`<=".$arFilter["price_max"]:"")."
                    GROUP BY
                        `IBLOCK_ELEMENT_ID`
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sPriceCond[] =$arId["ID"];
            }

            // Выбираем ID Товаров, подходящих по складу
            $sStoreCond = [];
            $sQuery = "
                SELECT
                    `product`.`ID` as `ID`
                FROM
                    `".\AGShop\CAGShop::t_iblock_element."` as `product`
                        LEFT JOIN
                    `".\AGShop\CAGShop::t_iblock_element_property."` as `offerlink`
                        ON
                        `offerlink`.`IBLOCK_PROPERTY_ID`=".CML2_LINK_PROPERTY_ID."
                        AND
                        `offerlink`.`VALUE_NUM`=`product`.`ID`
                        LEFT JOIN
                    `".\AGShop\CAGShop::t_catalog_store_product."` as `store_product`
                        ON
                        `offerlink`.`IBLOCK_ELEMENT_ID`=`store_product`.`PRODUCT_ID`
                        AND
                        `store_product`.`AMOUNT`>0
                WHERE
                    `product`.`ACTIVE` = 'Y'
                    ".($SectionCond?" AND `product`.`ID` IN(".implode($SectionCond).")":"")."
                    AND `product`.`IBLOCK_ID`=".CATALOG_IB_ID."
                    ".($arFilter["store"]?"AND `store_product`.`STORE_ID`=".$arFilter["store"]:"")."
                GROUP BY
                    `product`.`ID`
            ";
            $arIds = $CDB->sqlSelect($sQuery,1000);
            foreach($arIds as $arId)$sStoreCond[] =$arId["ID"];

            // Выбираем ID товаров, подходящих по ХИТ
            $sHitCond = [];
            if($arFilter["hit"]){
                $sQuery = "
                    SELECT
                        `IBLOCK_ELEMENT_ID` as `ID`
                    FROM
                        `".\AGShop\CAGShop::t_iblock_element_property."`
                    WHERE
                        `IBLOCK_PROPERTY_ID`=".SALELEADER_PROPERTY_ID."
                        ".($SectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode($SectionCond).")":"")."
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sHitCond[] =$arId["ID"];
            }

            // Выбираем ID товаров, подходящих по НОВИНКА
            $sNewCond = [];
            if($arFilter["new"]){
                $sQuery = "
                    SELECT
                        `IBLOCK_ELEMENT_ID` as `ID`
                    FROM
                        `".\AGShop\CAGShop::t_iblock_element_property."`
                    WHERE
                        `IBLOCK_PROPERTY_ID`=".NEWPRODUCT_PROPERTY_ID."
                        ".($SectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode($SectionCond).")":"")."
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sNewCond[] =$arId["ID"];
            }

            // Выбираем ID товаров, подходящих по Акция
            $sSaleCond = [];
            if($arFilter["sale"]){
                $sQuery = "
                    SELECT
                        `IBLOCK_ELEMENT_ID` as `ID`
                    FROM
                        `".\AGShop\CAGShop::t_iblock_element_property."`
                    WHERE
                        `IBLOCK_PROPERTY_ID`=".SPECIALOFFER_PROPERTY_ID."
                        ".($SectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode($SectionCond).")":"")."
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sSaleCond[] =$arId["ID"];
            }

            // Вычисляем пересечения
            $arIntersect = [
                $arSectionCond, $arQueryCond,
                $sSaleCond,$sNewCond,$sHitCond,$sStoreCond,
                $sPriceCond,$sInterestCond
            ];
            // Выкидываем нулевые и опеределяем с минимальным числом элементов
            $nMin = 10000000000;
            $nMinIndex = -1;
            foreach($arIntersect as $nKey=>$arVal){
                if(!count($arVal)){unset($arIntersect[$nKey]);continue;}
                if(count($arVal)<$nMin){
                    $nMin = count($arVal);
                    $nMinIndex = $nKey;
                }
            }
            if($nMinIndex==-1)return [];
            
            // Вычисляем элементы, входящие во все множества
            $arMinArray = $arIntersect[$nMinIndex];
            $nIntersectSetscount = count($arIntersect);
            $arIds = [];
            foreach($arMinArray as $nId){
                $nCount = 0;
                foreach($arIntersect as $nKye=>$arVal)
                    if(in_array($nId, $arVal))$nCount++;
                if($nCount>=$nIntersectSetscount)$arIds[] = $nId;
            }
            
            // Прогоняем получившееся пересечение чере БД для сортировки
            
            
            
            $arIdsPage = array_slice(
                $arIds, 
                $arOptions["pagination"]["offset"],
                $arOptions["pagination"]["onpage"]
            );
            return ["items"=>$arIdsPage,"total"=>count($arIds)];
        }
        
        function getProductsForTeasersByIds($arProductIds){
            $CDB = new \DB\CDB;;
            $resProduct = \CIBlockElement::GetList([],[
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "ID"=>$arProductIds
            ],false,["nTopCount"=>count($arProductIds)],[
                "ID","CODE","NAME","DETAIL_PICTURE","PROPERTY_MINIMUM_PRICE"
                ,"PROPERTY_NEWPRODUCT","PROPERTY_SALELEADER"
                ,"PROPERTY_SPECIALOFFER","IBLOCK_SECTION_ID"
            ]);
            $arProducts = [];
            $objSection = new \Catalog\CCatalogSection;
            while($arProduct = $resProduct->Fetch()){
                $arProduct["IMAGE"] = \CFile::GetPath(
                    $arProduct["DETAIL_PICTURE"]
                );
                $arProduct["SECTION"] = $objSection->getBriefById(
                    $arProduct["IBLOCK_SECTION_ID"]
                );
                $arProducts[] = $arProduct;
            }
            return $arProducts;
        }
        
    }
