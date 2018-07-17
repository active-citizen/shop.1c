<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CUser/CUser.class.php");

$sIntegrationNamespacePath = $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CIntegration/";
require_once($sIntegrationNamespacePath."CIntegrationTroyka.class.php");
require_once($sIntegrationNamespacePath."CIntegrationParking.class.php");
require_once($sIntegrationNamespacePath."CIntegration.class.php");

require_once("CCatalogSection.class.php");

use AGShop;

/**
 * Работа с параметрами сортировки плитки
*/
class CCatalogTeasers extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    
    /**
        Возвращает ID всех доступных продуктов, которые должны попасть в тизеры
        @param $sSectionCode - код раздела магазина
        @param $nUserId - ID пользователя для которого показываем плитку
        @param $isNotExists - включать в выдачу неактивные товары
        @param $nWishUser - ID пользователя. Если установлено, то отображаем только избранныые этого пользователя
        @return [
            "IDS"=>[1,2,3,4..]  // ID всех доступных для отображения товаров
            "EXISTS"=>[         // Доступные остатки всех доступных для отображения товаров
                "1"=>124,
                "2"=>263,
                "3"=>382,
                "4"=>1,
                ...
            ]
        ]
    */
    function getAllIds(
        $sSectionCode= '', 
        $nUserId = 0, 
        $isNotExists=false, 
        $nWishUser = 0
    ){
        
        global $USER;

        // Получам ID раздела в котором находимся
        $objSection = new \Catalog\CCatalogSection;
        
        if(!$nSectionId = $objSection->getByCode($sSectionCode)["ID"])
            $nSectionId = 0;
        
        $arOptions = [];

        /*
        // Составляем справочник флагов
        $objEnums = new \Catalog\CCatalogEnums;
        $ENUMS = $objEnums->getAll();
    
        // Составляем список активных разделов
        $arSectionsIds = $objSection->getAllActiveIds();
        */

        // Получаем список категорий для пользователя
        $arOptions["user_id"] = \User\CUser::getCategories($nUserId);

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
                    $nWishUser
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
                        $nWishUser
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
                    $nWishUser
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
                    $isNotExists
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

        $CDB = new \DB\CDB;
        $arIds = $CDB->sqlSelect($sQuery,10000);
        $arExists = [];
        foreach($arIds as $arId){
            $arExists[$arId["ID"]] = $arId["EXISTS"];
            $arSectionCond[] = $arId["ID"];
        }
        return [
            "IDS"   =>$arSectionCond,
            "EXISTS"=>$arExists
        ];
    }

}
