<?php
    namespace Section;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    use AGPhop as AGPhop;
    
    class CSection extends \AGShop\CAGShop{
        
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
            while($arSection = $resSections->Fetch())
                $arResult[$arSection["ID"]] = $arSection;
            
            return $arResult;
        }
        
    }
