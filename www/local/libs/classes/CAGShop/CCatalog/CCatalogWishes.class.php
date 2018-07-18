<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");
use AGPhop as AGPhop;
use AGShop\CCache as CCache;

class CCatalogWishes extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }
    
    /**
        Получение чмсла желающих для товара с указанным ID
        @param $nId - ID элемента каталога
    */
    function getCountByCatalogId($nId){
        $objCache = new \Cache\CCache("card_wishes",$nId,COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get()){
            return $sCacheData;
        }

        $res1 = \CIBlockElement::GetList([],$arFilter = [
            "IBLOCK_ID"=>$this->IBLOCKS["WISHES"], 
            "PROPERTY_WISH_PRODUCT"=>$nId
        ],false, false);
        $arResult = $res1->SelectedRowsCount();
        return $objCache->set($arResult);
    }
    
    /**
        Является ли указанный товар желаемым для указанного пользователя
        @param $nCatalogId - ID элемента каталога
        @param $nUserId - ID элемента каталога
    */
    function isWished($nCatalogId, $nUserId){
        $res1 = \CIBlockElement::GetList([], $arFilter = [
            "IBLOCK_ID"=>$this->IBLOCKS["WISHES"], 
            "PROPERTY_WISH_USER"=>$nUserId,
            "PROPERTY_WISH_PRODUCT"=>$nCatalogId
        ],false, array("nTopCount"=>1));
        return $res1->SelectedRowsCount();
    }

    /**
        Установка или снятие желания
        @param $nAmount - количество
        @param $nUserId - ID пользователя
        @param $sAct - действие ('on' или 'off')
        @return true, если движение возможно
    */
    function set($nProductId, $nUserId, $sAct){
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
        else{$iblockObj->Delete($arElement["ID"]);}
            
        return true;
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
        ],false,false,["PROPERTY_WISH_PRODUCT"]);
        $arProducts = [];
        while($arProduct = $resWishes->Fetch())
            $this->wishRecalcForProduct(
                $arProduct["PROPERTY_WISH_PRODUCT_VALUE"]
            );

        return true;
    }
        
}
