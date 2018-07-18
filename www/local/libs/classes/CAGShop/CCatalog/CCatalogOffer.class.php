<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CIntegration/CIntegrationTroyka.class.php");
require_once(realpath(__DIR__."/..")."/CIntegration/CIntegration.class.php");

require_once("CCatalogProduct.class.php");
require_once("CCatalogProperties.class.php");
require_once("CCatalogElement.class.php");

require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\Catalog as Catalog;
use AGShop\Integration as Integration;
use AGShop\CCache as CCache;

class CCatalogOffer extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
   
    /**
        Проверка активности товара по ID торгового предложения
        @param $nOfferId - ID торгового предложения
        @return true, если торговое предложение активно
    */
    function isActive($nOfferId){

        $objCache = new \Cache\CCache("isActiveOffer",$nOfferId,COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get()){
            return $sCacheData;
        }

        $bResult = true;
        if(!intval($nOfferId))return $this->addError('Не указан ID предложения');
        $arProduct = \CIBlockElement::GetList([],[
            "IBLOCK_ID"=>$this->IBLOCKS["OFFER"],
            "ID"=>$nOfferId
        ],false,[
            "nTopCount"=>1
        ],[
            "PROPERTY_CML2_LINK.ACTIVE",
            "PROPERTY_CML2_LINK.IBLOCK_SECTION_ID",
            ""
        ])->Fetch();
        if($arProduct["PROPERTY_CML2_LINK_ACTIVE"]!='Y')$bResult = false;
        if(!intval($arProduct["PROPERTY_CML2_LINK_IBLOCK_SECTION_ID"]))
            $bResult = false;

        if($bResult){
            $arSection = \CIBlockSection::GetList([],[
                "IBLOCK_ID"=>$this->IBLOCKS["CATALOG"],
                "ID"=>$arProduct["PROPERTY_CML2_LINK_IBLOCK_SECTION_ID"]
            ],false,[
                "ACTIVE",
            ])->Fetch();
            if($arSection["ACTIVE"]!='Y')$bResult = false;
        }
        return $objCache->set($bResult);
    }

    /**
        Получение информации о товарной позиции по её ID
        @param $nOfferId - ID торгового предложения
        @return массив с информацие о торковом предложении вида 
        [
            "PRODUCT"=>[...], // Информация о продукте к которому относится торговое предложение
            "PRODUCT_PROPERTIES"=>[
                "PROPERTY_CODE"=>[  // Код свойства
                    ...             
                ]
            ],
            "MAIN"=>[],  // Основные свойства торгового предложения
            "PROPERTIES"=>[ // Свойсва торгового предложения
                "PROPERTY_CODE"=>[  // Код свойства
                    ...             
                ]
            ]
        ]
    */
    function getById($nOfferId){
        $nOfferId = intval($nOfferId);
        $CDB = new \DB\CDB;
        $objCCatalogProduct = new \Catalog\CCatalogProduct;
        $objCCatalogProperties = new \Catalog\CCatalogProperties;
        
        $arResult = [
            "PROPERTIES"=>$objCCatalogProperties->getById(
                $nOfferId,"offerProperties"
            )
        ];
        $arResult["PRODUCT"] = $objCCatalogProduct->get(
            $arResult["PROPERTIES"]["CML2_LINK"]
        );
        $arResult["PRODUCT_PROPERTIES"] = $objCCatalogProduct->getProperties(
            $arResult["PROPERTIES"]["CML2_LINK"]
        );
        
        $arResult["MAIN"]=$this->getMain($nOfferId);
        
        return $arResult;
    }
    
    /**
        О торговом предложении без свойств и элемента каталога
        @param $nOfferId - ID торгового предложения
        @return массив с торговым предложением
    */
    function getMain($nOfferId){
        $objCatalogElement = new \Catalog\CCatalogElement;
        return $objCatalogElement->getById(
            $nOfferId, "offerMainInfo"
        );
    }
    
    /**
        Получение свойств конкретного торгового предложения
        @param $nOfferId - ID торгового предложения
        @return массив свойств торгового предложения
    */
    function getProperties($nOfferId){
        $objCCatalogProperties = new \Catalog\CCatalogProperties;
        return $objCCatalogProperties->getById($nOfferId, "offerProperties");
    }


    /**
        Проверка исчерпания суточного лимита на товар
        @param $nOfferId - ID торгового предложения
        @param $nAmount - сколько собираемся купить
        @return - false - если лимит исчерпан или число доступных в сутки единиц
    */
    function failedDailyLimit($nOfferId,$nAmount){
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
            array("PROPERTY_DAILY_LIMIT","ID")
        )->Fetch();
    
        $arFailedLimit = 
        $this->getDailyProductCount(
            $arProduct["ID"]
        );
        $failedLimit = $arFailedLimit["count"];
    
        if(
            $arProduct["PROPERTY_DAILY_LIMIT_VALUE"]
            &&
            $failedLimit+$nAmount-1 >= $arProduct["PROPERTY_DAILY_LIMIT_VALUE"]
    
        )
        return $arProduct["PROPERTY_DAILY_LIMIT_VALUE"];
    }


    /**
        Проверка исчерпания месячного лимита на товар для пользователя
        @param $nUserId - ID пользователя для которого проверям исчерпание месячного лимита
        @param $nOfferId - ID торгового предложения
        @param $nAmount - сколько собираемся купить
        @return - false - если лимит исчерпан или число доступных в месяц единиц
    */
    function failedMonLimit(
        $nUserId,
        $nOfferId,
        $nAmount = 1 //!< Сколько собираемся купить
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
            $failedLimit+$nAmount-1 >= $arProduct["PROPERTY_MON_LIMIT_VALUE"]
    
        )
        return $arProduct["PROPERTY_MON_LIMIT_VALUE"];
    }

    /**
        Определение скольок сегодня заказали товара

        @param $nProductId ID продукта (Элемента каталога, не предложения)
        @return количество заказанного сегодня продукта
    */
    function getDailyProductCount($nProductId){
        global $DB;
        $nPropuctId = intval($nProductId);

        $nPropId = CML2_LINK_PROPERTY_ID;//isset($arProp["id"])?$arProp["id"]:0;
        $sStartDate = date("Y-m-d H:i:s",mktime(
            0,0,0,
            date("m"),date("d"),date("Y")
        ));

        $sQuery = "
            SELECT
                ROUND(SUM(`b`.`QUANTITY`)) as `count`
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
                AND `a`.`STATUS_ID` IN ('F','AA','N','AG')
                AND `a`.`DATE_INSERT`>'$sStartDate'
            LIMIT
                1
        ";
        /*
        echo "<pre>";
        echo $sQuery;
        die;
        */

        $arQuery = $DB->Query($sQuery)->Fetch();
        return [
            "count" =>  isset($arQuery["count"])?$arQuery["count"]:0
        ]; 
     }

    /**
        Определение сколько в этом месяце пользователь заказал товара
        @param $nUserId - ID пользователя
        @param $nProductId ID продукта (Элемента каталога, не предложения)
    */
    function getMounthProductCount(
        $nUserId,
        $nProductId
    ){
        global $DB;
        $nUserId = intval($nUserId);
        $nPropuctId = intval($nProductId);
    
        // Вычисляем ID свойства привязки к элементу каталога
    
        //$arProp = $DB->Query($sQuery)->Fetch();
        $nPropId = CML2_LINK_PROPERTY_ID;//isset($arProp["id"])?$arProp["id"]:0;
        $sStartDate = date("Y-m-d H:i:s",mktime(
            date("H"),date("i"),date("s"),
            date("m")-1,date("d"),date("Y")
        ));
    
        $sQuery = "
            SELECT
                ROUND(SUM(`b`.`QUANTITY`)) as `count`,
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
    
    /**
        Определение сколько в этом месяце пользователь заказал товара
        @param $nUserId - ID пользователя
        @param $nProductId ID продукта (Элемента каталога, не предложения)
        @param $arProperties - свойства об элементе каталога, 
           получаемый из \CAGShop\Catalog\CCatalogProduct::getPropertiesForCard()
    */
    function getOffersForCard($nProductId, $arProperties){
        global $USER;
        
        $arResult = [];
        // Торговые предложения
        $resOffers = \CIBlockElement::GetList([],$arFilter = [
            "IBLOCK_ID"         =>  OFFER_IB_ID,
            "PROPERTY_CML2_LINK"=>  $nProductId
        ],false);
        $arResult["OFFERS"] = array();
        $arResult["OFFERS_JSON"] = array();
        $arResult["PROP1C"] = array();
        $arResult["STORAGES"] = array();

        $arUser = $USER->GetById($USER->GetId())->Fetch();
        
        while($arOffer = $resOffers->Fetch()){
            $arOfferJson= ["PICS"=>[],"1C_PROPS"=>[],"STORAGES"=>[]];
            
            // Свойства предложения
            $arOffer["PROPERTIES"] = [];
            $resProps=\CIBlockElement::GetProperty(OFFER_IB_ID,$arOffer["ID"]);
            
            while($arProp = $resProps->GetNext()){
                if(!isset($arOffer["PROPERTIES"][$arProp["CODE"]]))
                    $arOffer["PROPERTIES"][$arProp["CODE"]] = [];
                if($arProp["PROPERTY_TYPE"]=='F'){
                    $arProp["FILE_PATH"] = \CFile::GetPath($arProp["VALUE"]);
                }
                if($arProp["PROPERTY_TYPE"]=='F' && !$arProp["FILE_PATH"])
                    continue;
                elseif(
                    $arProp["PROPERTY_TYPE"]=='F' 
                    && $arProp["FILE_PATH"] && $arProp["CODE"]=='MORE_PHOTO'
                ) $arOfferJson["PICS"][] = $arProp["FILE_PATH"];
                
                if(preg_match("#PROP1C_(.*?)#",$arProp["CODE"])){
                    $arOfferJson["1C_PROPS"][$arProp["CODE"]] = [
                        "ID"=>$arProp["VALUE"],"VALUE"=>$arProp["VALUE_ENUM"]
                    ];
                    if(!isset($arResult["PROP1C"][$arProp["CODE"]]))
                        $arResult["PROP1C"][$arProp["CODE"]] = [
                            "NAME"=>$arProp["NAME"],"VALUES"=>[]
                        ];
                    if($arProp["VALUE"])
                        $arResult["PROP1C"][$arProp["CODE"]]["VALUES"]
                            [$arProp["VALUE"]]  = $arProp["VALUE_ENUM"];
                }
                
                $arOffer["PROPERTIES"][$arProp["CODE"]][] = $arProp;
            }
            // Склады предложения
            $arOffer["STORAGES"] = [];
            $resStorage = \CCatalogStoreProduct::GetList([],
                ["PRODUCT_ID"=>$arOffer["ID"]]
            );
    
    
            // !!!Отменяем невыбираемый остаток!!!
            // Будет независимо от того, что пришло из 1С браться умолчальный
            // А умолчальный сделаем нулём
            $arProperties["STORE_LIMIT"][0]["VALUE"] = 0;
    
            // Если это парковка и дневной лимит вышел - показываем фигу
            if(
                isset($arProperties["ARTNUMBER"][0]["VALUE"])
                &&
                $arProperties["ARTNUMBER"][0]["VALUE"]=='parking'
            ){
                $objParking = new \Integration\CIntegrationParking(
                    str_replace("u","",$arUser["LOGIN"])
                );
                $objParking->clearLocks();
                
                // Определяем вышел ли дневной лимит парковок 
                $bIsLimited = $objParking->isLimited();
                $arResult["PARKING_TODAY"] = $objParking->transactsToday;
            }
            // Если это тройка и дневной лимит вышел - показываем фигу
            if(
                isset($arProperties["ARTNUMBER"][0]["VALUE"])
                &&
                $arProperties["ARTNUMBER"][0]["VALUE"] =='troyka'
            ){
                $objTroya = new \Integration\CIntegrationTroyka(
                   str_replace("u","",$arUser["LOGIN"])
                );
                $objTroya->clearLocks();
                
                // Определяем вышел ли дневной лимит парковок 
                $bIsLimited = $objTroya->isLimited();
                $arResult["PARKING_TODAY"] = $objTroya->transactsToday;
            }
    
            // Если дневной лимит не вышел - получаем остатки по складам
            if(!$bIsLimited)while($arStorage = $resStorage->GetNext()){
                if(!$arStorage["AMOUNT"])continue;
                $arOffer["STORAGES"][$arStorage["STORE_ID"]] =
                    $arStorage["AMOUNT"]-(
                        intval($arProperties["STORE_LIMIT"][0]["VALUE"])
                        ?
                        $arProperties["STORE_LIMIT"][0]["VALUE"]
                        :
                        DEFAULT_STORE_LIMIT
                    );
                $arOfferJson["STORAGES"][$arStorage["STORE_ID"]] = 
                    $arStorage["AMOUNT"]-(
                        intval($arProperties["STORE_LIMIT"][0]["VALUE"])
                        ?
                        $arProperties["STORE_LIMIT"][0]["VALUE"]
                        :
                        DEFAULT_STORE_LIMIT
                    );
    
                // Пополняем справочник складов
                if(!isset($arResult["STORAGES"][$arStorage["STORE_ID"]])){
                    $arStoreItem = \CCatalogStore::GetList([],
                        ["ID"=>$arStorage["STORE_ID"]],false,["nTopCount"=>1]
                    )->Fetch();
    
                    $arStoreItem["EMAIL_SHORT"] = linkTruncate($arStoreItem["EMAIL"]);
                    $arResult["STORAGES"][$arStorage["STORE_ID"]] = $arStoreItem;
                }
                foreach($arResult["STORAGES"][$arStorage["STORE_ID"]] as $key=>$val)
                    $arResult["STORAGES"][$arStorage["STORE_ID"]][$key] = trim($val);
                
            }
    
            // Обнуляем отрицательные остатки и считаем общие
            $arResult["TotalAmount"] = 0;
            foreach($arOfferJson["STORAGES"] as $key=>$value){
                if($value<=0){
                    unset($arOfferJson["STORAGES"][$key]);
                    unset($arOffer["STORAGES"][$key]);
                }
                $arResult["TotalAmount"] += $arOffer["STORAGES"][$key];
            }
            
            $arOffer["RRICE_INFO"] = \CPrice::GetList([],[
                "PRODUCT_ID"=>$arOffer["ID"]
            ],false,["nTopCount"=>1])->Fetch();
            $arOfferJson["PRICE"] = str_replace(
                ",","",$arOffer["RRICE_INFO"]["PRICE"]
            );
            $arOfferJson["NAME"] = $arOffer["NAME"];
            
            $arResult["OFFERS"][] = $arOffer;
            $arResult["OFFERS_JSON"][$arOffer["ID"]] = $arOfferJson;
        };
        
        return $arResult;
    }

    
}
