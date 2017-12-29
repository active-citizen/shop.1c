<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    require_once(realpath(__DIR__)."/CCatalogSection.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegrationParking.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CIntegration/CIntegration.class.php");
    require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

    use AGShop;
    use AGShop\DB as DB;
    use AGShop\Integration as Integration;
    use AGShop\CCache as CCache;
    
    class CCatalogProductTag extends \AGShop\CAGShop{
        
        var $nProductPropertyId = '';
        
        function __construct($nProductPropertyId, $sSectionCode=''){
            parent::__construct();
            $this->nProductPropertyId = $nProductPropertyId;
            \CModule::IncludeModule('iblock');
        }
        
        /**
            Получение полного списка тэгов
            
            @param $nPropId - ID свойства инфоблока, к которому привязан инфоблок-справочник
        */
        function getAllTags($sSectionCode){
            global $USER;
            $objCache = new \Cache\CCache(
                "mobile_interests",$sSectionCode,300
            );
            if($sCacheData = $objCache->get()){
                return $sCacheData;
            }
             $CDB = new \DB\CDB;
            // Получаем ID раздела
            if($sSectionCode){
                $objCatalog = new \Catalog\CCatalogSection;
                $arSection = $objCatalog->getByCode($sSectionCode);
                $nSectionId = $arSection["ID"];
            }

            // Получаем ID инфоблока из ктоорого надо достать значения
            $arProp = $CDB->searchOne(\AGShop\CAGShop::t_iblock_property,[
                "ID"=>$this->nProductPropertyId
            ],["LINK_IBLOCK_ID"]);


            $objTroya = new \Integration\CIntegrationTroyka($USER->GetLogin());
            $objTroya->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bTroykaLimited = $objTroya->isLimited();

            $objParking = new \Integration\CIntegrationParking($USER->GetLogin());
            $objParking->clearLocks();
            // Определяем вышел ли дневной лимит парковок 
            $bParkingLimited = $objParking->isLimited();


            $sNow = date("Y-m-d H:i:s");
            $sQuery = "
                SELECT
                    `tag`.`NAME` as `NAME`,
                    `tag`.`ID` as `ID`,
                    COUNT(DISTINCT `product`.`ID`) as `count`
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
                    `".\AGShop\CAGShop::t_iblock_element_property."` as `taglink`
                        ON
                        `taglink`.`IBLOCK_PROPERTY_ID`=".$this->nProductPropertyId."
                        AND
                        `taglink`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                        LEFT JOIN
                    `".\AGShop\CAGShop::t_iblock_element."` as `tag`
                        ON `taglink`.`VALUE_NUM`=`tag`.`ID`

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
                    `tag`.`ID`
            ";

            $arResult = $CDB->sqlSelect($sQuery);
            $objCache->set($arResult);

            // Получаем все элементы инфоблока справочника
            return $arResult;
        }
        
        
    }
