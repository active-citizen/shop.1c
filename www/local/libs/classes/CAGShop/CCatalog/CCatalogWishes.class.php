<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    use AGPhop as AGPhop;
    
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
            $res1 = \CIBlockElement::GetList([],$arFilter = [
                "IBLOCK_ID"=>$this->IBLOCKS["WISHES"], 
                "PROPERTY_WISH_PRODUCT"=>$nId
            ],false, false);
            return $res1->SelectedRowsCount();
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
        
    }
