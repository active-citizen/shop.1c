<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    use AGPhop as AGPhop;
    
    class CCatalogSection extends \AGShop\CAGShop{
        
        function __construct(){
            parent::__construct();
            \CModule::IncludeModule('iblock');
        }
        
        /**
            Получение разделов каталога
            @param $bActive - Только включенные
        */
        function get($arOptions = [
            "ACTIVE"                        => true,
            "ONLY_WITH_PRODUCTS"            => false,
            "ONLY_WITH_PRESENT_PRODUCTS"    => false
        ]){
            $resSections = \CIBlockSection::GetList([],[
                "ACTIVE"=>boolval($arOptions["ACTIVE"])?"Y":"N"
            ]);
            
            $arResult = [];
            while($arSection = $resSections->GetNext())
                $arResult[$arSection["ID"]] = $arSection;
            
            return $arResult;
        }
        
        function getById($nId){
            return \CIBlockSection::GetList(
                [],[
                    "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"],
                    "ID"=>$nId
                ],false,[],[
                    "nTopCount"=>1
                ]
            )->GetNext();
        }
        
        function getBriefById($nId){
            return \CIblockSection::GetList([],[
                "IBLOCK_ID"=>CATALOG_IB_ID,
                "ID"=>$nId
            ],false,["ID","NAME","CODE","IBLOCK_SECTION_ID"])->Fetch();
        }
        
    }
