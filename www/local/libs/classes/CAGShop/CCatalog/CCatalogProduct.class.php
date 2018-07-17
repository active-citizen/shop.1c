<?php
namespace Catalog;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

$sIntegrationNamespacePath = $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CIntegration/";

require_once("CCatalogProduct.class.php");
require_once("CCatalogSection.class.php");
require_once("CCatalogProperties.class.php");
require_once("CCatalogElement.class.php");

require_once($sIntegrationNamespacePath."CIntegrationTroyka.class.php");
require_once($sIntegrationNamespacePath."CIntegrationParking.class.php");
require_once($sIntegrationNamespacePath."CIntegration.class.php");
    
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");
require_once(realpath(__DIR__."/..")."/CUser/CUser.class.php");
    
use AGShop\Integration as Integration;
use AGShop\Catalog as Catalog;
use AGShop;
use AGShop\DB as DB;
use AGShop\CCache as CCache;
use AGShop\User as User;

class CCatalogProduct extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
    
    /**
        Проверка активности товара по ID торгового предложения
    */
    function isActive($nProductId){

        $objCache = new \Cache\CCache("isActiveProduct",$nProductId);
        if($sCacheData = $objCache->get()){
            return $sCacheData;
        }

        $bResult = true;
        if(!intval($nProductId))return $this->addError('Не указан ID продукта');
        $arProduct = \CIBlockElement::GetList([],[
            "IBLOCK_ID"=>$this->IBLOCKS["CATALOG"],
            "ID"=>$nProductId
        ],false,[
            "nTopCount"=>1
        ],[
            "ACTIVE",
            "IBLOCK_SECTION_ID",
            ""
        ])->Fetch();
        if($arProduct["ACTIVE"]!='Y')$bResult = false;
        if(!intval($arProduct["IBLOCK_SECTION_ID"]))
            $bResult = false;

        if($bResult){
            $arSection = \CIBlockSection::GetList([],[
                "IBLOCK_ID"=>$this->IBLOCKS["CATALOG"],
                "ID"=>$arProduct["IBLOCK_SECTION_ID"]
            ],false,[
                "ACTIVE",
            ])->Fetch();
            if($arSection["ACTIVE"]!='Y')$bResult = false;
        }
        $objCache->set($bResult);
        return $bResult;
    }


    /**
        Получить свойства элемента каталога по его ID
    */
    function getProperties($nProductId){
        $objCCatalogProperties = new \Catalog\CCatalogProperties;
        return $objCCatalogProperties->getById($nProductId,"ProductProperties");
    }

    /**
    
        Получение основных параметров товара по ID элемента каталога
    
    */
    function get($nId){
        $objCatalogElement = new \Catalog\CCatalogElement;
        return $objCatalogElement->getById($nId, "ProductMainInfoById");
    }
        
    
    /**
        Получение основных параметров товара по его коду
    */
    function getByCode($sCode){
        $objCatalogElement = new \Catalog\CCatalogElement;
        return $objCatalogElement->getByCode(
            $sCode, $this->IBLOCKS["CATALOG"],"ProductMainInfoByCode"
        );
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

        $objCache = new \Cache\CCache("card_product_properties",$nId);
        if($sCacheData = $objCache->get()){
            return $sCacheData;
        }

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
        $objCache->set($arResult);
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
        
        // Получаем список категорий для пользователя
        $arOptions["user_id"] = \User\CUser::getCategories(
            isset($arOptions["user_id"]) && intval($arOptions["user_id"])
            ?
            intval($arOptions["user_id"])
            :
            0
        );

//            new \XPrint($arUsersCats);
//            new \XPrint($arFilter);
        $objCache = new
        \Cache\CCache("mobile_teasers",md5(json_encode($arOptions)));
        if($sCacheData = $objCache->get()){
            return $sCacheData;
        }
        
        $CDB = new \DB\CDB;
        
        $arFilter = [];
        if(!isset($arOptions["sorting"]))$arOptions["sorting"] = [];
        if(!isset($arOptions["sorting"]["param"]))$arOptions["sorting"]["param"]
            = 'fresh';
        if(!isset($arOptions["sorting"]["direction"]))$arOptions["sorting"]["direction"] = 'desc';
        $arOptions["sorting"]["direction"] = strtoupper($arOptions["sorting"]["direction"]);
        
        if($arOptions["sorting"]["param"]=='price')
            $arSorting = ["PROPERTY_MINIMUM_PRICE"=>$arOptions["sorting"]["direction"]];
        elseif($arOptions["sorting"]["param"]=='hit')
            $arSorting = ["PROPERTY_SALELEADER"=>$arOptions["sorting"]["direction"]];
        elseif($arOptions["sorting"]["param"]=='new')
            $arSorting = ["PROPERTY_NEWPRODUCT"=>$arOptions["sorting"]["direction"]];
        elseif($arOptions["sorting"]["param"]=='sale')
            $arSorting = ["PROPERTY_SPECIALOFFER"=>$arOptions["sorting"]["direction"]];
        elseif($arOptions["sorting"]["param"]=='wishes')
            $arSorting = ["PROPERTY_WISHES_QUANTITY"=>$arOptions["sorting"]["direction"]];
        elseif($arOptions["sorting"]["param"]=='rating')
            $arSorting = ["PROPERTY_RATING"=>$arOptions["sorting"]["direction"]];
        elseif($arOptions["sorting"]["param"]=='fresh')
            $arSorting = ["TIMESTAMP_X"=>$arOptions["sorting"]["direction"]];

        $arSortingTypes = ["price","rating","favorites","new","hit","wishes"];
        foreach($arSortinTypes as $sSortingType)
            if(
                in_array($arOptions["sorting"]["param"])
                && $arOptions["sorting"]["direction"]=='asc'
                && $arOptions["sorting"]["direction"]=='desc'
            )$arSorting[$arOptions["sorting"]["param"]] 
                = $arOptions["sorting"]["direction"];
        if(!$arSorting)$arSorting = ["TIMESTAMP_X"=>"desc"];
        
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

        if(isset($arFilter["store"]) && $arFilter["store"]==333)
            unset($arFilter["store"]);

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
        $sNow = date("Y-m-d")." 00:00:00";
        $sQuery = "
            SELECT
                `product`.`NAME` as `NAME`,
                `product`.`ID` as `ID`,
                SUM(`store_product`.`AMOUNT`) as `EXISTS`
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
                    LEFT JOIN
                `".\AGShop\CAGShop::t_iblock_section."` as `section`
                    ON `product`.`IBLOCK_SECTION_ID`=`section`.`ID`
                    LEFT JOIN
                `".\AGShop\CAGShop::t_iblock_element_property."` as `product_userscats`
                    ON
                    `product_userscats`.`IBLOCK_PROPERTY_ID`=".USERSCATS_PROPERTY_ID."
                    AND
                    `product_userscats`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                ".
                (
                    $arOptions["filter"]["wishes_user"]
                    ?
                    "
                    LEFT JOIN
                 `".\AGShop\CAGShop::t_iblock_element_property."` as `wishes_prod`
                    ON
                    `wishes_prod`.`IBLOCK_PROPERTY_ID`=".WISH_PRODUCT_PROPERTY_ID."
                    AND
                    `wishes_prod`.`VALUE_NUM`=`product`.`ID`
                    LEFT JOIN
                 `".\AGShop\CAGShop::t_iblock_element_property."` as `wishes_user`
                    ON
                    `wishes_user`.`IBLOCK_PROPERTY_ID`=".WISH_USER_PROPERTY_ID."
                    AND
                    `wishes_prod`.`IBLOCK_ELEMENT_ID`=`wishes_user`.`IBLOCK_ELEMENT_ID`
                    AND
                    `wishes_user`.`VALUE_NUM`=".intval(
                        $arOptions["filter"]["wishes_user"]
                    )."
                    "
                    :
                    ""
                )
                ."
            WHERE
                `product`.`IBLOCK_ID`=".CATALOG_IB_ID."
                AND `product`.`IBLOCK_SECTION_ID`!=0
                ".($nSectionId?"AND `product`.`IBLOCK_SECTION_ID`=".$nSectionId:"")."
                AND `section`.`ACTIVE`='Y'
                AND `product`.`ACTIVE`='Y'
                ".(
                    $arOptions["filter"]["wishes_user"]
                    ?
                    "
                    AND `wishes_prod`.`ID` IS NOT NULL
                    AND `wishes_user`.`ID` IS NOT NULL
                    "
                    :
                    "
                    AND 1
                    "
                )
                ."
                ".(
                    $arOptions["filter"]["not_exists"]
                    ?
                    "
                    AND 
                    (
                        (
                            `hide_prop`.`VALUE_NUM` IS NULL
                        )
                        OR
                        (
                            `hide_prop`.`VALUE_NUM` IS NOT NULL
                            AND
                            `store_product`.`AMOUNT`>0
                        )
                    )
                    "
//                        "AND 1"
                    :
                    "
                    AND 
                    (
                        `store_product`.`AMOUNT`>0
                    )
                    "
                )
                ."
                ".(
                    $arOptions["user_id"]
                    ?
                    "
                    AND 
                    (
                            `product_userscats`.`VALUE_NUM` IS NULL
                        OR
                            `product_userscats`.`VALUE_NUM` IN
                            (".implode(",",$arOptions["user_id"]).")
                    )
                    "
//                        "AND 1"
                    :
                    "
                    AND 
                    (
                        `product_userscats`.`VALUE_NUM` IS NULL
                    )
                    "
                )
                ."
                AND 
                (
                    (
                        `hide_date`.`VALUE` IS NULL
                    )
                    OR
                    (
                        `hide_date`.`VALUE`>'".$sNow."'
                    )
                )
                AND 
                (
                    `artnum`.`ID` IS NULL
                    OR
                    (
                        `artnum`.`VALUE`!='troyka'
                        AND
                        `artnum`.`VALUE`!='parking'
                    )
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
//            $fd = fopen($_SERVER["DOCUMENT_ROOT"]."/1.sql","a");
//            fwrite($fd,$sQuery);
//            fclose($fd);
//            echo "<!-- ";
//            print_r($sQuery);
//            echo " -->";
        $arIds = $CDB->sqlSelect($sQuery,10000);
        $arExists = [];
        foreach($arIds as $arId){
            $arExists[$arId["ID"]] = $arId["EXISTS"];
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
            $arIds = $CDB->sqlSelect($sQuery,10000);
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
                    -- ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
                    ".($arFilter["interest"]?"AND `VALUE_NUM`
                    IN(".implode(",",$arFilter["interest"]).")":"")."
                GROUP BY
                    `IBLOCK_ELEMENT_ID`
            ";
            $arIds = $CDB->sqlSelect($sQuery,10000);
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
                    -- ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
                    ".($arFilter["price_min"]?"AND `VALUE_NUM`>=".$arFilter["price_min"]:"")."
                    ".($arFilter["price_max"]?"AND `VALUE_NUM`<=".$arFilter["price_max"]:"")."
                GROUP BY
                    `IBLOCK_ELEMENT_ID`
            ";
            $arIds = $CDB->sqlSelect($sQuery,10000);
            foreach($arIds as $arId)$sPriceCond[] =$arId["ID"];
        }

        // Выбираем ID Товаров, подходящих по складу
        $arStoreCond = [];
        $sQuery = "
            SELECT
                `product`.`ID` as `ID`,
                `store_product`.`AMOUNT` as `AMOUNT`
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
                    `offerlink`.`IBLOCK_ELEMENT_ID`=`store_product`.`PRODUCT_ID`"
                    .(
                        !$arOptions["filter"]["not_exists"]
                        ?
                        " AND `store_product`.`AMOUNT`>0 "
                        :
                        ""
                    )."
            WHERE
                1
                AND `store_product`.`ID` IS NOT NULL
                AND `product`.`ID` IN(".($arSectionCond?implode(",",$arSectionCond):0).")
                AND `product`.`IBLOCK_SECTION_ID`!=0
                ".($nSectionId?"AND `product`.`IBLOCK_SECTION_ID`=".$nSectionId:"")."
                -- AND `product`.`ACTIVE` = 'Y'
                -- ".($arSectionCond?" AND `product`.`ID` IN(".implode(",",$arSectionCond).")":"")."
                AND `product`.`IBLOCK_ID`=".CATALOG_IB_ID."
                ".(
                    $arFilter["store"]
                    ?
                    "AND `store_product`.`STORE_ID` IN (".$arFilter["store"].")"
                    :
                    ""
                )."
            GROUP BY
                `product`.`ID`
        ";
        $arIds = $CDB->sqlSelect($sQuery,10000);
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
                    -- ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
            ";
            $arIds = $CDB->sqlSelect($sQuery,10000);
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
                    -- ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
            ";
            $arIds = $CDB->sqlSelect($sQuery,10000);
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
                    -- ".($arSectionCond?" AND `IBLOCK_ELEMENT_ID` IN(".implode(",",$arSectionCond).")":"")."
            ";
            $arIds = $CDB->sqlSelect($sQuery,10000);
            foreach($arIds as $arId)$sSaleCond[] =$arId["ID"];
        }

        $arFlags = array_unique(array_merge(
            $sHitCond, $sSaleCond, $sNewCond
        ));
        // Вычисляем пересечения
        
        $arIntersect = [];
        if($arSectionCond)$arIntersect[] = $arSectionCond;

        if($arQueryCond)$arIntersect[] = $arQueryCond;
        if($arStoreCond)$arIntersect[] = $arStoreCond;
        if($arFlags)$arIntersect[] = $arFlags;
        if($sPriceCond)$arIntersect[] = $sPriceCond;
        if($sInterestCond)$arIntersect[] = $sInterestCond;
         
        /*
        $arIntersect = [
            $arSectionCond, $arQueryCond, $arStoreCond, 
            //$sSaleCond,$sNewCond,$sHitCond,
            $arFlags,
            $sPriceCond,$sInterestCond
        ];
        */
        
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
            ,"PROPERTY_WISHES_QUANTITY","PROPERTY_NEWPRODUCT","PROPERTY_RATING"
            ,"PROPERTY_SALELEADER","IBLOCK_SECTION_ID"
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

            $arProduct["EXISTS"] = $arExists[$arProduct["ID"]];

            $arProduct["WISHES"] = $arProduct["PROPERTY_WISHES_QUANTITY_VALUE"];
            $arItems[$arProduct['ID']] = $arProduct;
        }
        
        $arResult = ["items"=>$arItems,"total"=>$nTotal];
        $objCache->set($arResult);
        
        return $arResult;
    }
    
    
    /**
     * Пожелание продукта
     * 
     @param $nProductId - ID продукта
     @param $sAct - действие (on|off)
     @param $nUserId - ID пользователя, пожелавшего
    */
    function wish($nProductId, $sAct, $nUserId){
        \CModule::IncludeModule('iblock');
    
        
        if(!$nProductId)return $this->addError("Не указан ID товара");
            
        if(!$nUserId)return $this->addError("Не указан ID пользователя");
        
        if(!\CIBlockElement::GetList(
            ["ID"=>$nProductId,"IBLOCK_ID"=>$this->IBLOCKS["CATALOG"]]
            ,false,["nTopCount"=>1],["ID"]
        )->Fetch())
            return $this->addError("Товар с ID=$productId не существует");
        
        // Ишем желание с этими условиями
        $arElement = \CIBlockElement::GetList(
            [],$arFields = [
                "IBLOCK_ID" =>  $this->IBLOCKS["WISHES"],
                "NAME"      =>  $nProductId."_".$nUserId
            ],false,["nTopCount"=>1],["ID"]
        )->Fetch();
        
        
        // Если надо добавить, но уже есть
        if($sAct=='on' && $arElement)
            return $this->addError("Желание этого товара этим пользователем уже добавлено");
        // Если надо удалить, но нечего
        elseif($sAct=='off' && !$arElement)
            return $this->addError("Желание этого товара этим пользователем не добавлено");
            
        $iblockObj = new \CIBlockElement;
        // Добавление
        if($sAct=='on' && $elementId = $iblockObj->Add($arFields)){
            // Устанавливаем свойства
            \CIBlockElement::SetPropertyValues(
                $elementId,$this->IBLOCKS["WISHES"],
                ["WISH_USER"=>$nUserId,"WISH_PRODUCT"=>$nProductId]
            );
        }
        // Удалить
        elseif($sAct=='off'){$iblockObj->Delete($arElement["ID"]);}
        // Сообщить об ошибке
        else{return $this->addError($iblock->LAST_ERROR);}

        return $this->wishRecalcForProduct($nProductId);
    }
    
    /**
     * Пересчёт количество желающих у продукта и заполнение им 
     * соответствующего свойства
    */
    function wishRecalcForProduct($nProductId){

        // Получаем актуальное число вишей, если нет ошибок
        $res = \CIBlockElement::GetList([],[
            "IBLOCK_ID"=>$this->IBLOCKS["WISHES"],
            "PROPERTY_WISH_PRODUCT"=>$nProductId
        ],false,false,["ID"]);
        
        $nWishes = $res->SelectedRowsCount();
        
        // Прописываем число вишей в свойстве товара
        \CIBlockElement::SetPropertyValueCode(
            $nProductId, "WISHES_QUANTITY", $nWishes
        );
        
        return $nWishes;
    }
    
    /**
        Пересчёт желаний для всех повишеных товаров
    */
    function wishRecalcForAllProducts(){
        // Получаем полный список продуктов, которые пожелали
        $resWishes = \CIBlockElement::GetList([],[
            "IBLOCK_ID"=>$this->IBLOCKS["WISHES"],
            "PROPERTY_WISH_PRODUCT"=>$nProductId
        ],false,false,["PROPERTY_WISH_PRODUCT"]);
        $arProducts = [];
        while($arProduct = $resWishes->Fetch()){
            $arProducts[] = $arProduct["PROPERTY_WISH_PRODUCT_VALUE"];
        }
        foreach($arProducts as $nProductId)
            $this->wishRecalcForProduct($nProductId);

        return true;
    }
    
    
}
