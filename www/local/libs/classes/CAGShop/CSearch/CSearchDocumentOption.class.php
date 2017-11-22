<?php
namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__)."/CSearch.interface.php");
require_once(realpath(__DIR__)."/CSearchDocumentType.class.php");

use AGShop as AGShop;
use AGShop\DB as DB;
use AGShop\Search as Search;

/**
    Типы документов
*/
class CSearchDocumentOption extends \AGShop\CAGShop{

    var $nExpires = 86400;  //!< Время переиндексирования документа

    private $arOptionTypes = [
        "SECTION_ID"    =>1,    //!< Id раздела товара
        "INTEREST_ID"   =>2,    //!< Id закреплённых тегов интересов
        "AT_STORAGE"    =>3,    //!< Число товаров на складах
        "WHISHES"       =>4     //!< Число пожелавших это
    ];

    function __construct(){
        parent::__construct();
    }

    /**
        Получение массива опций документа и их ID
    */
    function getTypes(){return $this->arOptionTypes;}

    /**
        Получение ID типа документа по его имени
    */
    function getId($sType){
        if(!trim($sType)){
            $this->addError("Document type is undefined");
            return false;
        }
        if(isset($this->arOptionTypes[$sType]))return $this->arOptionTypes[$sType];
        return false;
        
    }
    /**
        Получение кода документа по ID
    */
    function getCode($nId){
        foreach($this->arOptionTypes as $sKey=>$sValue)
            if($sValue==$nId)return $sKey;
        return false;
    }

    
    /**
        Получаем опции документа из самого документа в БД
    */
    function fetch($nDocId, $sOptionType='PRODUCT'){
        $CDB = new \DB\CDB;
        
        $arOptions = [];
        
        ///////////// Получаем опциию интересов
        $arResult = $CDB->searchAll(\AGShop\CAGShop::t_iblock_element_property,[
            "IBLOCK_ELEMENT_ID"=>$nDocId,
            "IBLOCK_PROPERTY_ID"=>$this->PROPERTIES["INTEREST"]
        ],[
            "VALUE_NUM"
        ]);
        $arOptions["INTEREST_ID"] = [];
        foreach($arResult as $arItem)
            $arOptions["INTEREST_ID"][] = intval($arItem["VALUE_NUM"]);
        
        
        /////////////// Получаем опцию разделов
        $arResult = $CDB->searchAll(\AGShop\CAGShop::t_iblock_element,[
            "ID"=>$nDocId
        ],[
            "IBLOCK_SECTION_ID","NAME"
        ]);
        $arOptions["SECTION_ID"] = [];
        foreach($arResult as $arItem)
            $arOptions["SECTION_ID"][] = intval($arItem["IBLOCK_SECTION_ID"]);
        
        //////// Получаем опцию товаров на складах
        // Получаем ID товарного предложения
        $arOffer = $CDB->searchOne(\AGShop\CAGShop::t_iblock_element_property,[
            "VALUE_NUM"=>$nDocId,
            "IBLOCK_PROPERTY_ID"=>$this->PROPERTIES["CML2_LINK"]
        ],[
            "IBLOCK_ELEMENT_ID"
        ]);
        // Получаем остатки на складах
        $arStores = [];
        
        if(intval($arOffer["IBLOCK_ELEMENT_ID"]))
            $arStores = $CDB->searchAll(\AGShop\CAGShop::t_catalog_store_product,[
                "PRODUCT_ID"=>$arOffer["IBLOCK_ELEMENT_ID"]
            ],[
                "AMOUNT","STORE_ID"
                
            ]);

        $nStores = 0;
        $arOptions["AT_STORAGE"] = [];
        foreach($arStores as $arStore)
            if(intval($arStore["AMOUNT"]))
                $arOptions["AT_STORAGE"][] = $arStore["STORE_ID"];


        // Получаем ID свойства "желаемый товар"
        $arWishProp = $CDB->searchOne(\AGShop\CAGShop::t_iblock_property,
            ["CODE"=>"WISH_PRODUCT"],["ID"]
        );
        
        // Получаем популярность товара (пожелания)
        $sQuery = "
            SELECT
                COUNT(`ID`) as `COUNT`
            FROM
                `".\AGShop\CAGShop::t_iblock_element_property."` as `prop`
            WHERE
                `prop`.`IBLOCK_PROPERTY_ID`=".$arWishProp["ID"]."
                AND `prop`.`VALUE_NUM`=".$nDocId."
            LIMIT
                1
        ";
        $arWish = $CDB->sqlSelect($sQuery);
        if(isset($arWish[0]))$arOptions["WHISHES"] = [$arWish[0]["COUNT"]];
        
        return $arOptions;
    }
    
    /**
        Получаем проиндексированные опции документа из БД
    */
    function get($nDocId, $sDocType='PRODUCT'){
        $CDB = new \DB\CDB;
        $objCSearchDocumentType = new \Search\CSearchDocumentType;
        
        return $CDB->searchAll(ISearch::t_csearch_options,[
            "doc_id"        =>  $nDocId,
            "doc_type_id"   =>  $objCSearchDocumentType->getId($sDocType) 
        ]);
    }
    
    /**
        Получаем сводку проиндексированных в БД опций документа
    */
    function getSummary($nDocId, $sDocType='PRODUCT'){
        $arOptions = $this->get($nDocId, $sDocType);
        // Cоcтавляем индекс опций
        $arOpts = [];
        foreach($arOptions as $arOption){
            $sOptCode = $this->getCode(
                $arOption["opt_type_id"]
            );
            if(!isset($arOpts[$sOptCode]))$arOpts[$sOptCode] = array();
            $arOpts[$sOptCode][] = $arOption["opt_value"];
        }
        $arOptTypes = $this->getTypes();
        foreach($arOptTypes as $optCode=>$optId)
            if(!isset($arOpts[$optCode]))$arOpts[$optCode] = [];
        return $arOpts;
    }
    
    /*
        Удаляем опции документа
    */
    function delete($nDocId, $sDocType='PRODUCT'){
        $CDB = new \DB\CDB;
        $objCSearchDocumentType = new \Search\CSearchDocumentType;
        $CDB->delete(ISearch::t_csearch_options,[
            "doc_id"        =>  $nDocId,
            "doc_type_id"   =>  $objCSearchDocumentType->getId($sDocType) 
        ]);
    }
    
    /*
        Сохраняем опции документа
    */
    function save($nDocId, $sDocType='PRODUCT', $arOptions = []){
        $CDB = new \DB\CDB;
        $objCSearchDocumentType = new \Search\CSearchDocumentType;
        
        $this->delete($nDocId, $sDocType);
        
        foreach($arOptions as $sOptionName=>$arOptValues)
            foreach($arOptValues as $nOptValue)
            if(intval(trim($nOptValue)))
                $this->set([
                    "doc_id"        =>  $nDocId,
                    "doc_type_id"   =>  $objCSearchDocumentType->getId($sDocType),
                    "opt_value"     =>  $nOptValue,
                    "opt_type_id"   =>  $this->getId($sOptionName)
                ]);
        
    }
    
    /**
        Сохранение одной опции документа
    */
    function set($arFields){
        $CDB = new \DB\CDB;
        return $CDB->insert(ISearch::t_csearch_options, $arFields);
    }
    
    
}

