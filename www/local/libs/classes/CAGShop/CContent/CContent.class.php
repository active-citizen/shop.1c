<?php
    namespace Content;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    
    use AGShop;
    use AGShop\DB as DB;
    
    class CContent extends \AGShop\CAGShop{
        function __construct(){
            parent::__construct();
            \CModule::IncludeModule('iblock');
        }

        /**
            Получение FAQ для отображения на сайте

            @param $nSectionId - ID раздела. Если 0, выводится всё
        */
        function getFAQForSite($nSectionId = 0){
            $arFilter = [];
            $arFilter["ACTIVE"] = "Y";
            $arFilter["IBLOCK_CODE"] = "content_sectioned_faq";
            // Сортировка для разделов и элементов
            $arSort = array("SORT"=>"ASC");

            // Получаем разделы
            $res = \CIBlockSection::GetList( $arSort, $arFilter);   
            $arResult["sections"] = array();
            while($arSection = $res->Fetch()){
                $arSection["childs"] = array();
                // Получаем пункты
                $arFilter["SECTION_ID"] = $arSection["ID"];
                $resFAQ = \CIBlockElement::GetList($arSort, $arFilter);
                while($arFAQ = $resFAQ->Fetch())
                    $arSection["childs"][] = $arFAQ;
                
                $arResult["sections"][] = $arSection;
            }
            return $arResult;
        }

        function getArticleForSite($sCode){
            $arResult = \CIBlockElement::GetList(
                ["SORT"=>"ASC"],
                $arFilter = [
                    "IBLOCK_CODE"   =>  "content_articles",
                    "ACTIVE"        =>  "Y",
                    "CODE"            =>  $sCode
                ]
            )->GetNext();
            return $arResult;

        }
        
    }
    
