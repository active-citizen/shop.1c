<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\DB as DB;

class CCatalogOffer extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
    
    function getProperties($nOfferId){
        $nOfferId = intval($nOfferId);
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
                `element_prop`.`IBLOCK_ELEMENT_ID`= ".$nOfferId."
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
        Проверка исчерпания месячного лимита на товар для пользователя
    */
    function failedMonLimit(
        $nUserId,
        $nOfferId
    ){
        $nOfferId = intval($nOfferId);
        $arOffer = \CIBlockElement::GetList(
            array(),
            $arFilter = array(
                "IBLOCK_ID" => OFFER_IB_ID,
                "ID"        =>  $nOfferId
            ),
            false,
            array("nTopCount"=>1),
            array("PROPERTY_CML2_LINK","ID")
        )->Fetch();
        if(!isset($arOffer["ID"]))return 1;
    
        $arProduct = \CIBlockElement::GetList(
            array(),
            array(
                "IBLOCK_ID" =>  CATALOG_IB_ID,
                "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"]
            ),
            false,
            array("nTopCount"=>1),
            array("PROPERTY_MON_LIMIT","ID")
        )->Fetch();
    
        $arFailedLimit = 
        $this->getMounthProductCount(
            $nUserId,
            $arProduct["ID"]
        );
        $failedLimit = $arFailedLimit["count"];
    
        if(
            $arProduct["PROPERTY_MON_LIMIT_VALUE"]
            &&
            $failedLimit >= $arProduct["PROPERTY_MON_LIMIT_VALUE"]
    
        )
        return $arProduct["PROPERTY_MON_LIMIT_VALUE"];
    }

    /**
        Определение сколько в этом месяце пользователь заказал товара
    */
    function getMounthProductCount(
        $nUserId,
        $nProductId
    ){
        global $DB;
        $nUserId = intval($nUserId);
        $nPropuctId = intval($nProductId);
    
        // Вычисляем ID свойства привязки к элементу каталога
        
        $sQuery = "
            SELECT
                `ID` as `id`
            FROM
                `b_iblock_property` as `a`
            WHERE
                `a`.`IBLOCK_ID`=".OFFER_IB_ID."
                AND `a`.`CODE`='CML2_LINK'
            LIMIT 
                1
        ";
    
        $arProp = $DB->Query($sQuery)->Fetch();
        $nPropId = isset($arProp["id"])?$arProp["id"]:0;
        $sStartDate = date("Y-m-d H:i:s",mktime(
            date("H"),date("i"),date("s"),
            date("m")-1,date("d"),date("Y")
        ));
    
        $sQuery = "
            SELECT
                count(`b`.`ID`) as `count`,
                DATE_FORMAT(DATE_ADD(`a`.`DATE_INSERT`, INTERVAL 1 MONTH),'%d.%m.%Y %H:%i:%s') as `next`
                -- ,`a`.`DATE_INSERT` as `order_date`
                -- ,`c`.`VALUE_NUM` as `product_id`
                -- ,`a`.`ID` as `order_id`
                -- ,`b`.`PRODUCT_ID` as `offer_id`
            FROM 
                `b_iblock_element_property` as `c`
                    LEFT JOIN
                `b_sale_basket` as `b`
                    ON `b`.`PRODUCT_ID`=`c`.`IBLOCK_ELEMENT_ID`
                    LEFT JOIN
                `b_sale_order` as `a`
                    on `b`.`ORDER_ID`=`a`.`ID`
    
            WHERE
                1
                AND `c`.`IBLOCK_PROPERTY_ID`=$nPropId
                AND `c`.`VALUE_NUM`=$nProductId
                AND `a`.`USER_ID`=$nUserId
                AND `a`.`STATUS_ID` IN ('F','AA','N')
                AND `a`.`DATE_INSERT`>'$sStartDate'
            LIMIT
                1
        ";
        $arQuery = $DB->Query($sQuery)->Fetch();
        return [
            "next"  =>  isset($arQuery["next"])?$arQuery["next"]:date("d.m.Y H:i:s"),
            "count" =>  isset($arQuery["count"])?$arQuery["count"]:0
        ]; 
    }

    
}
