<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    require_once(realpath(__DIR__)."/CCatalogOffer.class.php");
    require_once(realpath(__DIR__)."/CCatalogStore.class.php");
    require_once(realpath(__DIR__)."/CCatalogProduct.class.php");

    use AGShop;
    use AGShop\DB as DB;
    use AGShop\Catalog as Catalog;
    
    class CCatalogSKU extends \AGShop\CAGShop{
        
        private $arSKUInfo = [];
        
        function __construct(){
            parent::__construct();
            \CModule::IncludeModule('iblock');
        }
        
        function fetch($nId = ''){
            $nId = intval($nId);
            $CDB = new \DB\CDB;
            $objCCatalogOffer = new \Catalog\CCatalogOffer;
            $objCCatalogProduct = new \Catalog\CCatalogProduct;
            $objCCatalogStore = new \Catalog\CCatalogStore;
            
            $arFilter = ["IBLOCK_ID" =>  $this->IBLOCKS["OFFER"]];
            if($nId)$arFilter["ID"] = $nId;
            
            $arOffer = \CIBlockElement::GetList(["ID"=>"DESC"],$arFilter,false,
                ["nTopCount"=>1],["ID","NAME"])->Fetch();
            
            $arProperties = [];
            if(isset($arOffer["ID"]))
                $arProperties = $objCCatalogOffer->getProperties($arOffer["ID"]);

            $arStores = [];
            if(isset($arOffer["ID"]))
                $arStores = $objCCatalogStore->exists($arOffer["ID"]);

            
            $arProduct = [];
            if(isset($arProperties["CML2_LINK"]) && $arProperties["CML2_LINK"]){
                $arProduct = $objCCatalogProduct->get($arProperties["CML2_LINK"]);
            }
            
            $arProductProperties = [];
            if(isset($arProperties["CML2_LINK"]) && $arProperties["CML2_LINK"])
                $arProductProperties = 
                    $objCCatalogProduct->getProperties($arProperties["CML2_LINK"]);
            
            
            $this->arSKUInfo = [
                "OFFER"         =>  $arOffer,
                "PROPERTIES"    =>  $arProperties,
                "STORES"        =>  $arStores,
                "PRODUCT"       =>  $arProduct,
                "PRODUCT_PROPERTIES"       =>  $arProductProperties
            ];
            return true;
        }
        
        function get(){
            return $this->arSKUInfo;
        }
        
    }
