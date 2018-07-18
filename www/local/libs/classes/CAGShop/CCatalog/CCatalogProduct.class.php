<?php
namespace Catalog;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");


require_once("CCatalogProduct.class.php");
require_once("CCatalogProductProperty.class.php");
require_once("CCatalogSection.class.php");
require_once("CCatalogProperties.class.php");
require_once("CCatalogElement.class.php");
require_once("CCatalogEnums.class.php");
require_once("CCatalogSorting.class.php");
require_once("CCatalogFilter.class.php");
require_once("CCatalogTeasers.class.php");
require_once("CCatalogSearch.class.php");
require_once("CCatalogInterests.class.php");
require_once("CCatalogPrice.class.php");
require_once("CCatalogStore.class.php");
require_once("CCatalogWishes.class.php");
require_once("CCatalogWishCheck.class.php");

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
        Проверка активности товара по ID
        @param $nProductId - ID продукта
    */
    function isActive($nProductId){

        $objCache = new \Cache\CCache("isActiveProduct",$nProductId,COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get()){
            return $sCacheData;
        }

        $objCatalogElement = new \Catalog\CCatalogElement;
        $arProduct = $objCatalogElement->getById($nProductId);

        $objCatalogSection = new \Catalog\CCatalogSection;
        $arSection = $objCatalogSection->getById($arProduct["IBLOCK_SECTION_ID"]);
        
        return $objCache->set(
            $arSection["ACTIVE"]=="Y" && $arProduct["ACTIVE"]=="Y"
        );
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
        Получение любого активного продукта (для автотестов)
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

        $objCache = new \Cache\CCache("card_product_properties",$nId,COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get())return $sCacheData;

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
        return $objCache->set($arResult);
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

        // Кеширование данных для плитки
        $arUsersCatsForCacheKey = \User\CUser::getCategories(
            arOptions["user_id"]
        );
        $objCache = new
        \Cache\CCache("mobile_teasers",md5(
            json_encode($arOptions).json_encode($arUsersCatsForCacheKey)
        ),COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get())return $sCacheData;        
        
        $CDB = new \DB\CDB;
        
        // Получаем фильтр 
        $objCatalogFilter = new \Catalog\CCatalogFilter;
        $arFilter = $objCatalogFilter->getIBlockFilter($arOptions["filter"]);

        // Получаем сортировку 
        $objCatalogSorting = new \Catalog\CCatalogSorting;
        $arSorting = $objCatalogSorting->getIBlockSorting($arOptions["sorting"]);
        
        // Получаем ID всех доступных пользователю для отображения товаров
        $objCatalogTeasers = new \Catalog\CCatalogTeasers;
        $arTeas = $objCatalogTeasers->getAllIds(
            $arFilter["section_code"],  // Код раздела
            $arOptions["user_id"],   // ID пользователя для которого тизеры
            $arOptions["filter"]["not_exists"], // Включать в выдачу несуществующие товары
            $arOptions["filter"]["wishes_user"] // Показывать желания этого пользователя
        );
        // ID товаров
        $arSectionCond = $arTeas["IDS"];
        // Массив остатков
        $arExists = $arTeas["EXISTS"];
        
        // Выбираем по поисковому запросу
        $objCatalogSearch = new \Catalog\CCatalogSearch;
        $arQueryCond = $objCatalogSearch->getIdsByProductName(
            $arFilter["query"],$SectionCond
        );
        
        $objCatalogInterest = new \Catalog\CCatalogInterests;
        $sInterestCond = $objCatalogInterest->getProductsByIds(
            $arFilter["interest"],$SectionCond
        );
        
        $objCatalogPrice = new \Catalog\CCatalogPrice;
        // Выбираем ID товаров, подходящих по цене
        $sPriceCond = $objCatalogPrice->getProductsByPrice(
            $arFilter["price_min"],
            $arFilter["price_max"],
            $arSectionCond
        );

        // Выбираем ID Товаров, подходящих по складу
        $objCatalogStore = new \Catalog\CCatalogStore;
        $arStoreCond = $objCatalogStore->getProductsByIds(
            $arFilter["store"], $arFilter["section_code"], 
            $arOptions["filter"]["not_exists"]
        );

        $objCatalogProductProperty = new \Catalog\CCatalogProductProperty;
        // Выбираем ID товаров, подходящих по ХИТ
        $sHitCond = $objCatalogProductProperty->getFlagedProducts(
            $arFilter["hit"]?SALELEADER_PROPERTY_ID:0,
            $arSectionCond
        );
        
        // Выбираем ID товаров, подходящих по НОВИНКА
        $sNewCond =  $objCatalogProductProperty->getFlagedProducts(
            $arFilter["new"]?NEWPRODUCT_PROPERTY_ID:0,
            $arSectionCond
        );

        // Выбираем ID товаров, подходящих по Акция
        $sSaleCond = $objCatalogProductProperty->getFlagedProducts(
            $arFilter["sale"]?SPECIALOFFER_PROPERTY_ID:0,
            $arSectionCond
        );

        $arFlags = array_unique(array_merge(
            $sHitCond, $sSaleCond, $sNewCond
        ));
        
        // Порядок добавления пересечений
        /*
        $arIntersectOrder = [
            "arSectionCond", "arQueryCond" , "arStoreCond", "arFlags",
            "sPriceCond", "sInterestCond"
        ];
        
        $arIntersect = [];
        foreach($arIntersectOrder as $sIntersectOrder){
            if($$sIntersectOrder)$arIntersect[] = $$sIntersectOrder;
        }
        */

        // Вычисляем пересечения
        
        if($arSectionCond)$arIntersect[] = $arSectionCond;
        if($arQueryCond)
            $arIntersect[] = $arQueryCond;
        elseif(!$arQueryCond && $arFilter["query"])
            $arIntersect[] = "none";
        
        if($arStoreCond)
            $arIntersect[] = $arStoreCond;
        elseif(!$arStoreCond && $arFilter["store"])
            $arIntersect[] = "none";
 
        if($arFlags)
            $arIntersect[] = $arFlags;
        elseif(!$arFlags && ($arFilter["new"] || $arFilter["sale"] || $arFilter["hit"]))
            $arIntersect[] = "none";
            
        if($sPriceCond)
            $arIntersect[] = $sPriceCond;
        elseif(!$sInterestCond && ($arFilter["price_min"] || $arFilter["price_max"]))
            $arIntersect[] = "none";
        
        if($sInterestCond)
            $arIntersect[] = $sInterestCond;
        elseif(!$sInterestCond && $arFilter["interest"])
            $arIntersect[] = "none";
        
        
         
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
        return $objCache->set($arResult);
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
    
        $objWishCheck = new \Catalog\CCatalogWishCheck();
        if(!$objWishCheck->checkBeforeWish($nProductId, $nUserId))
            return $this->addError($objWishCheck->getErrors());
        
        $objWishes = new \Catalog\CCatalogWishes;
        if(!$objWishes->set($nProductId, $nUserId, $sAct))
            return $this->addError($objWishes->getErrors());
        
        return $objWishes->wishRecalcForProduct($nProductId);
    }
    
    /**
        Пересчёт желаний для всех повишеных товаров
    */
    function wishRecalcForAllProducts(){
        $objWishes = new \Catalog\CCatalogWishes;
        return $objWishes->wishRecalcForAllProducts();
    }
    
    
}
