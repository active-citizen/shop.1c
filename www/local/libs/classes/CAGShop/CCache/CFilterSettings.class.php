<?php
    namespace Cache;
    require_once(realpath(__DIR__)."/CCache.class.php");
    require_once(realpath(__DIR__."/../CDB")."/CDB.class.php");
    use AGShop\DB as DB;

    /**
        Класс для сохранения в БД настроек фильтров товаров
    */
    class CFilterSettings {
        
        var $nUserId = 0;
        var $sCode = '';
        var $nExpires = 300;

        function __construct($sCode, $nUserId = 0){
            if(!intval($nUserId))$nUserId = \CUser::GetID();
            $this->nUserId = $nUserId;
            $this->sCode = $sCode;
        }

        /**
            Получение настроек фильтра товаров
        */
        function getFilter(){
            $objCache = new \Cache\CCache(
                "teaser_filter",$this->nUserId.":".$this->sCode
                , $this->nExpires
            );
            if($arFilter = $objCache->get()){
                return $arFilter;
            }
            $arFilter = $this->__getFilterFromDB();
            $objCache->set($arFilter);
            unset($objCache);
            return $arFilter;
        }
        /**
            Получение настроек сортировки товаров
        */
        function getSorting(){
            $objCache = new \Cache\CCache(
                "teaser_sorting",$this->nUserId
                , $this->nExpires
            );
            if($arSorting = $objCache->get()){
                return $arSorting;
            }
            $arSorting = $this->__getSortingFromDB();
            $objCache->set($arSorting);
            unset($objCache);
            return $arSorting;
        }

        function getSmallIcons(){
            $objCache = new \Cache\CCache(
                "teaser_smallicons",$this->nUserId
                , $this->nExpires
            );
            if($bSmallIcons = $objCache->get()){
                return $bSmallIcons;
            }
            $bSmallIcons = $this->__getSmallIconsFromDB();
            $objCache->set($bSmallIcons);
            unset($objCache);
            return $bSmallIcons;
        }

        /**
            Сохранение настроек фильтра товаров
        */
        function setFilter($arFilter = []){
            $objCache = new \Cache\CCache(
                "teaser_filter",$this->nUserId.":".$this->sCode
                , $this->nExpires
            );
            $objCache->clear();
            $this->__setFilterToDB($arFilter);
            
        }
        
        /**
            Сохранение настроек сортировки товаров
        */
        function setSorting($arSorting = []){
            $objCache = new \Cache\CCache(
                "teaser_sorting",$this->nUserId
                , $this->nExpires
            );
            $objCache->clear();
            $this->__setSortingToDB($arSorting);
        }

        function setSmallIcons($bSmallIcons){
            $objCache = new \Cache\CCache(
                "teaser_smallicons",$this->nUserId
                , $this->nExpires
            );
            $objCache->clear();
            $this->__setSmallIconsToDB($bSmallIcons);
        }

        private function __getSmallIconsFromDB(){
            $arRow = $this->__getSmallIconsRow();
            if(!$arRow)return 0;
            return intval($arRow["SMALL_ICONS"]);
        }

        private function __getFilterFromDB(){
            $arRow = $this->__getFilterRow();
            if(!$arRow)return [];
            return unserialize($arRow["FILTER"]);
        }

        private function __getSortingFromDB(){
            $arRow = $this->__getSortingRow();
            if(!$arRow)return [];
            return unserialize($arRow["SORTING"]);
        }



        private function __setFilterToDB($arFilter){
            if(!$arRow = $this->__getFilterRow())
                $this->__createFilterRow($arFilter);
            else
                $this->__updateFilterRow($arRow["ID"],$arFilter);
        }

        private function __setSortingToDB($arSorting){
            if(!$arRow = $this->__getSortingRow())
                $this->__createSortingRow($arSorting);
            else
                $this->__updateSortingRow($arRow["ID"],$arSorting);
        }

        private function __setSmallIconsToDB($bSmallIcons){
            if(!$arRow = $this->__getSmallIconsRow())
                $this->__createSmallIconsRow($bSmallIcons);
            else
                $this->__updateSmallIconsRow($arRow["ID"],$bSmallIcons);
        }

        private function __getFilterRow(){
            $CDB = new \DB\CDB;
            return $CDB->searchOne(
                "int_filter_settings_filter",
                [
                    "USER_ID"=>$this->nUserId,
                    "SECTION_CODE"=>$this->sCode
                ]
            );
        }

        private function __createFilterRow($arFilter = []){
            $CDB = new \DB\CDB;
            $arFields =
            [
                "USER_ID"=>$this->nUserId,
                "SECTION_CODE"=>$this->sCode,
                "FILTER" => serialize($arFilter)
            ];
            $CDB->insert("int_filter_settings_filter",$arFields);
        }

        private function __updateFilterRow($nId, $arFilter = []){
            $CDB = new \DB\CDB;
            $arFields =
            [
                "FILTER" => serialize($arFilter)
            ];
            $arFilter = ["ID"=>$nId];
            $CDB->update("int_filter_settings_filter",$arFilter,$arFields);
        }



        private function __getSortingRow(){
            $CDB = new \DB\CDB;
            return $CDB->searchOne(
                "int_filter_settings_sorting",
                [
                    "USER_ID"=>$this->nUserId,
                ]
            );
        }

        private function __createSortingRow($arSorting = []){
            $CDB = new \DB\CDB;
            $arFields =
            [
                "USER_ID"=>$this->nUserId,
                "SORTING" => serialize($arSorting)
            ];
            $CDB->insert("int_filter_settings_sorting",$arFields);
        }

        private function __updateSortingRow($nId, $arSorting = []){
            $CDB = new \DB\CDB;
            $arFields =
            [
                "SORTING" => serialize($arSorting)
            ];
            $arFilter = ["ID"=>$nId];
            $CDB->update("int_filter_settings_sorting",$arFilter,$arFields);
        }


        private function __getSmallIconsRow(){
            $CDB = new \DB\CDB;
            return $CDB->searchOne(
                "int_filter_settings_smallicon",
                [
                    "USER_ID"=>$this->nUserId,
                ]
            );
        }

        private function __createSmallIconsRow($bSmallIcons = 0){
            $CDB = new \DB\CDB;
            $arFields =
            [
                "USER_ID"=>$this->nUserId,
                "SMALL_ICONS" => intval($bSmallIcons)
            ];
            $CDB->insert("int_filter_settings_smallicon",$arFields);
        }

        private function __updateSmallIconsRow($nId, $bSmallIcons = 0){
            $CDB = new \DB\CDB;
            $arFields =
            [
                "SMALL_ICONS" => intval($bSmallIcons)
            ];
            $arFilter = ["ID"=>$nId];
            $CDB->update("int_filter_settings_smallicon",$arFilter,$arFields);
        }
    }
   
