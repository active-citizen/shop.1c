<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");
    require_once(realpath(__DIR__."/..")."/CUser/CUser.class.php");
    
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationParking.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegration.class.php");
        
    use AGShop\Integration as Integration;

    use AGPhop as AGPhop;
    use AGShop\DB as DB;
    use AGShop\CCache as CCache;
    
    class CCatalogSection extends \AGShop\CAGShop{
        
        function __construct(){
            parent::__construct();
            \CModule::IncludeModule('iblock');
        }
        
        /**
            Получение разделов каталога
        */
        function get($arOptions = [
            "ACTIVE"                        => true,
            "ONLY_WITH_PRODUCTS"            => false,
            "ONLY_WITH_PRESENT_PRODUCTS"    => false        
        ],$nCacheExpires = 0){
            global $USER;
            if(!$nCacheExpires)$nCacheExpires = COMMON_CACHE_TIME; 

            $arUserCats = \User\CUser::getCategories($USER->GetId());

            $objCache = new \Cache\CCache("mainmenu",md5(serialize($arUserCats)),$nCacheExpires);
            if($sCacheData = $objCache->get()){
                return $sCacheData;
            }

            $objTroya = new \Integration\CIntegrationTroyka($USER->GetLogin());
            $objTroya->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bTroykaLimited = $objTroya->isLimited();

            $objParking = new \Integration\CIntegrationParking($USER->GetLogin());
            $objParking->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bParkingLimited = $objParking->isLimited();



            $CDB = new \DB\CDB;
            $sNow = date("Y-m-d");
            $sQuery = "
                SELECT
                    `section`.`ID` as `ID`,
                    `section`.`NAME` as `NAME`,
                    `section`.`CODE` as `CODE`,
                    `section`.`ACTIVE` as `ACTIVE`
                FROM
                    `".\AGShop\CAGShop::t_iblock_element."` as `product`
                        LEFT JOIN
                    `".\AGShop\CAGShop::t_iblock_section."` as `section`
                        ON
                        `product`.`IBLOCK_SECTION_ID`=`section`.`ID`
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
                    `".\AGShop\CAGShop::t_iblock_element_property."` as `product_userscats`
                        ON
                        `product_userscats`.`IBLOCK_PROPERTY_ID`=".USERSCATS_PROPERTY_ID."
                        AND
                        `product_userscats`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                WHERE
                    `product`.`IBLOCK_ID`=".CATALOG_IB_ID."
                    AND `section`.`ACTIVE`='Y'
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
                    ".(
                        $arUserCats
                        ?
                        "
                        AND 
                        (
                                `product_userscats`.`VALUE_NUM` IS NULL
                            OR
                                `product_userscats`.`VALUE_NUM` IN
                                (".implode(",",$arUserCats).")
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
                            `hide_date`.`VALUE`>='".$sNow."'
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
                    `product`.`IBLOCK_SECTION_ID`
            ";
            $arResult = $CDB->sqlSelect($sQuery);
            return $objCache->set($arResult);
        }
        
        /**
            Получение массива всех активных разделов
        */
        function getAllActiveIds(){
            $arSectionsIds = [];
            $res = \CIBlockSection::GetList([],["ACTIVE"=>"Y"],false,["ID"]);
            while($arSection = $res->Fetch())$arSectionsIds[] = $arSection["ID"];
            return $arSectionsIds;
        }
        
        function getById($nId){
            if(!$nId)return [];
            
            $objCache = new 
                \Cache\CCache("card_section_common_info_by_id",$nId,COMMON_CACHE_TIME);
            if($sCacheData = $objCache->get()){
                return $sCacheData;
            }

            $arResult = \CIBlockSection::GetList(
                [],[
                    "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                    "ID"=>$nId
                ],false,[],[
                    "nTopCount"=>1
                ]
            )->GetNext();
            return $objCache->set($arResult);
        }
        
        function getByCode($sCode){
            
            if(!$sCode)return [];
            $objCache = new
            \Cache\CCache("card_section_common_info_by_code",$sCode,COMMON_CACHE_TIME);
            if($sCacheData = $objCache->get()){
                return $sCacheData;
            }

            $arResult = \CIBlockSection::GetList(
                [],[
                    "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                    "CODE"=>$sCode
                ],false,[],[
                    "nTopCount"=>1
                ]
            )->GetNext();
            return $objCache->set($arResult);
        }

        function getBriefById($nId){
            $objCache = new
            \Cache\CCache("card_section_brief_by_id",$nId,COMMON_CACHE_TIME);
            if($sCacheData = $objCache->get()){
                return $sCacheData;
            }

            $arResult = \CIblockSection::GetList([],[
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "ID"=>$nId
            ],false,["ID","NAME","CODE","IBLOCK_SECTION_ID"])->Fetch();
            return $objCache->set($arResult);
        }
        
    }
