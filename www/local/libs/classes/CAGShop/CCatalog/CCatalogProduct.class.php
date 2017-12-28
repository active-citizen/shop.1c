<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogProduct.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogSection.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationParking.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegration.class.php");
    require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");
        
    use AGShop\Integration as Integration;
    use AGShop\Catalog as Catalog;
    use AGShop;
    use AGShop\DB as DB;
    use AGShop\CCache as CCache;
    
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
                    "page"=>1,
                    "onpage"=>10
                ]
            ]
        */
        function getTeasers($arOptions = []){
            global $USER;

            $objCache = new \Cache\CCache("mobile_teasers",md5(json_encode($arOptions)),$nCacheExpires);
            if($sCacheData = $objCache->get()){
                return $sCacheData;
            }
            
            $CDB = new \DB\CDB;
            
            $arFilter = [];
            if(!isset($arOptions["sorting"]))$arOptions["sorting"] = [];
            if(!isset($arOptions["sorting"]["param"]))$arOptions["sorting"]["param"] = 'price';
            if(!isset($arOptions["sorting"]["direction"]))$arOptions["direction"]["param"] = 'desc';
            $arOptions["sorting"]["direction"] = strtoupper($arOptions["sorting"]["direction"]);
            
            if($arOptions["sorting"]["param"]=='price')
                $arSorting = ["PROPERTY_MINIMUM_PRICE"=>$arOptions["sorting"]["direction"]];
            elseif($arOptions["sorting"]["param"]=='hit')
                $arSorting = ["PROPERTY_SALELEADER"=>$arOptions["sorting"]["direction"]];
            elseif($arOptions["sorting"]["param"]=='new')
                $arSorting = ["PROPERTY_NEWPRODUCT"=>$arOptions["sorting"]["direction"]];
            elseif($arOptions["sorting"]["param"]=='sale')
                $arSorting = ["PROPERTY_SPECIALOFFER"=>$arOptions["sorting"]["direction"]];

            $arSortingTypes = ["price","rating","favorites","new","hit"];
            foreach($arSortinTypes as $sSortingType)
                if(
                    in_array($arOptions["sorting"]["param"])
                    && $arOptions["sorting"]["direction"]=='asc'
                    && $arOptions["sorting"]["direction"]=='desc'
                )$arSorting[$arOptions["sorting"]["param"]] 
                    = $arOptions["sorting"]["direction"];
            if(!$arSorting)$arSorting = ["price"=>"desc"];
            
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
            
            if(isset($arOptions["filter"]["interest"]))
                $arFilter["interest"] = $arOptions["filter"]["interest"];
            
            if(isset($arOptions["filter"]["price_min"]))
                $arFilter["price_min"] = intval($arOptions["filter"]["price_min"]);
            
            if(isset($arOptions["filter"]["price_max"]))
                $arFilter["price_max"] = intval($arOptions["filter"]["price_max"]);
            
            if(isset($arOptions["filter"]["store"]) && is_array($arOptions["filter"]["store"]))
                $arFilter["store"] = $CDB->ForSql(implode(",",$arOptions["filter"]["store"]));
            elseif(isset($arOptions["filter"]["store"]))
                $arFilter["store"] = $CDB->ForSql($arOptions["filter"]["store"]);
            
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

            $objTroya = new \Integration\CIntegrationTroyka($USER->GetLogin());
            $objTroya->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bTroykaLimited = $objTroya->isLimited();

            $objParking = new \Integration\CIntegrationParking($USER->GetLogin());
            $objParking->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bParkingLimited = $objParking->isLimited();

            // Выбираем по разделу и по доступности
            // Выбираем ID Товаров, подходящих по складу
            // При этом либо тех, у которых не стоит флаг "прятать без остатка"
            // либо с флагом, но и остатками
            $arSectionCond = [];
            $sNow = date("Y-m-d");
            $sQuery = "
                SELECT
                    `product`.`NAME` as `NAME`,
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
                        LEFT JOIN
                    `".\AGShop\CAGShop::t_iblock_element_property."` as `hide_prop`
                        ON
                        `hide_prop`.`IBLOCK_PROPERTY_ID`=".HIDE_IF_ABSENT_PROPERTY_ID."
                        AND
                        `hide_prop`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                        LEFT JOIN
                    `".\AGShop\CAGShop::t_iblock_element_property."` as `hide_date`
                        ON
                        `hide_date`.`IBLOCK_PROPERTY_ID`=".HIDE_DATE_PROPERTY_ID."
                        AND
                        `hide_date`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                        LEFT JOIN
                    `".\AGShop\CAGShop::t_iblock_element_property."` as `artnum`
                        ON
                        `artnum`.`IBLOCK_PROPERTY_ID`=".ARTNUMBER_PROPERTY_ID."
                        AND
                        `artnum`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                WHERE
                    `product`.`IBLOCK_ID`=".CATALOG_IB_ID."
                    AND `product`.`IBLOCK_SECTION_ID`!=0
                    ".($nSectionId?"AND `product`.`IBLOCK_SECTION_ID`=".$nSectionId:"")."
                    AND `product`.`ACTIVE`='Y'
                    AND 
                    (
                        (
                            `hide_prop`.`VALUE` IS NULL
                        )
                        OR
                        (
                            `hide_prop`.`VALUE` IS NOT NULL
                            AND
                            `store_product`.`AMOUNT`>0
                        )
                    )
                    AND 
                    (
                        (
                            `hide_date`.`VALUE` IS NULL
                        )
                        OR
                        (
                            `hide_date`.`VALUE`>='".$sNow."'
                        )
                    )
                    AND 
                    (
                        `artnum`.`ID` IS NULL
                        OR
                        `artnum`.`VALUE`!='troyka'
                        OR
                        `artnum`.`VALUE`!='parking'
                        OR
                        (
                            `artnum`.`VALUE`='troyka'
                            AND
                            ".($bTroykaLimited?0:1)."
                        )
                        OR
                        (
                            `artnum`.`VALUE`='parking'
                            AND
                            ".($bParkingLimited?0:1)."
                        )
                    )
                GROUP BY
                    `product`.`ID`
            ";
            $arIds = $CDB->sqlSelect($sQuery,10000);
            foreach($arIds as $arId){
                $arSectionCond[] = $arId["ID"];
            }
            
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
                        ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
                        ".($arFilter["interest"]?"AND `VALUE_NUM` IN(".$arFilter["interest"].")":"")."
                    GROUP BY
                        `IBLOCK_ELEMENT_ID`
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sInterestCond[] = $arId["ID"];
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
                        ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
                        ".($arFilter["price_min"]?"AND `VALUE_NUM`>=".$arFilter["price_min"]:"")."
                        ".($arFilter["price_max"]?"AND `VALUE_NUM`<=".$arFilter["price_max"]:"")."
                    GROUP BY
                        `IBLOCK_ELEMENT_ID`
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sPriceCond[] =$arId["ID"];
            }

            // Выбираем ID Товаров, подходящих по складу
            $arStoreCond = [];
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
                    ".($arSectionCond?" AND `product`.`ID` IN(".implode(",",$arSectionCond).")":"")."
                    AND `product`.`IBLOCK_ID`=".CATALOG_IB_ID."
                    ".($arFilter["store"]?"AND `store_product`.`STORE_ID` IN (".$arFilter["store"]:"").")
                GROUP BY
                    `product`.`ID`
            ";
            $arIds = $CDB->sqlSelect($sQuery,1000);
            foreach($arIds as $arId)$arStoreCond[] =$arId["ID"];

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
                        ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID`
                        IN(".implode(",",$arSectionCond).")":"")."
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
                        ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID`
                        IN(".implode(",",$arSectionCond).")":"")."
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
                        ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID`
                        IN(".implode(",",$arSectionCond).")":"")."
                ";
                $arIds = $CDB->sqlSelect($sQuery,1000);
                foreach($arIds as $arId)$sSaleCond[] =$arId["ID"];
            }
            
            // Вычисляем пересечения
            $arIntersect = [
                $arSectionCond, $arQueryCond, $arStoreCond, 
                $sSaleCond,$sNewCond,$sHitCond,
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
            
            // Ничего не найдено
            if(!$arIds)return ["items"=>$arItems,"total"=>$nTotal];
            
            // Прогоняем получившееся пересечение чере БД для сортировки, попутно
            // Делая пагинацию
            $nTotal = count($arIds);
            $res = \CIBlockElement::GetList($arSorting,[
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "ID"=>$arIds
            ],false,[
                "iNumPage"  =>  $arOptions["pagination"]["page"],
                "nPageSize" =>  $arOptions["pagination"]["onpage"]
            ],[
                "ID","CODE","NAME","DETAIL_PICTURE","PROPERTY_MINIMUM_PRICE"
                ,"PROPERTY_NEWPRODUCT","PROPERTY_SALELEADER"
                ,"PROPERTY_SPECIALOFFER","PREVIEW_TEXT"//,"PROPERTY_WANTS.NAME"
            ]);
            $arItems = [];
            $objSection = new \Catalog\CCatalogSection;
            $arIds = [];
            while($arProduct = $res->Fetch()){
                $arIds[] = $arProduct['ID'];
                $arProduct["IMAGE"] = \CFile::GetPath(
                    $arProduct["DETAIL_PICTURE"]
                );
                $arProduct["SECTION"] = $objSection->getBriefById(
                    $arProduct["IBLOCK_SECTION_ID"]
                );
                $arItems[$arProduct['ID']] = $arProduct;
            }
            $arIdsPage = [];
            // Составляем справочник свойств
            $sQuery = "
                SELECT `ID`,`CODE` FROM 
                WHERE  `CODE`='WISH_PRODUCT' LIMIT 1
            ";
            $arProp = $CDB->searchOne(\AGShop\CAGShop::t_iblock_property,[
                "CODE"=>'WISH_PRODUCT'],["ID"]);
                
            // Получаем количество сердечек для каждого товара
            $sQuery = "
                SELECT
                    FLOOR(`VALUE_NUM`) as `ID`,
                    COUNT(`VALUE_NUM`) as `COUNT`
                FROM
                    `".\AGShop\CAGShop::t_iblock_element_property."`
                WHERE
                    `IBLOCK_PROPERTY_ID`=".$arProp["ID"]."
                    AND `VALUE_NUM` IN(".implode(",",$arIds).")
                GROUP BY
                    `VALUE_NUM`
            ";
            $arWishes = $CDB->sqlSelect($sQuery);
            $arWishesIndex = [];
            foreach($arWishes as $arWish)
                $arItems[$arWish['ID']]["WISHES"] = $arWish["COUNT"];

            $arResult = ["items"=>$arItems,"total"=>$nTotal];
            $objCache->set($arResult);
            
            return $arResult;
        }
        
        
    }
