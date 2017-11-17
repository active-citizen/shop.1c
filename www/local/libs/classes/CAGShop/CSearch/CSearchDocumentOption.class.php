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
        "SECTION_ID"    =>1,
        "INTEREST_ID"   =>2,
        "IN_STORAGE"    =>3
    ];

    function __construct(){
        parent::__construct();
    }

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
        Получаем опции документа из самого документа в БД
    */
    function fetch($nDocId, $sOptionType='PRODUCT'){
        $CDB = new \DB\CDB;
        
        $arOptions = [];
        
        // Получаем опциию интересов
        $arResult = $CDB->searchAll(ISearch::t_iblock_element_property,[
            "IBLOCK_ELEMENT_ID"=>$nDocId,
            "IBLOCK_PROPERTY_ID"=>$this->PROPERTIES["INTEREST"]
        ],[
            "VALUE_NUM"
        ]);
        $arOptions["INTEREST_ID"] = [];
        foreach($arResult as $arItem)
            $arOptions["INTEREST_ID"][] = intval($arItem["VALUE_NUM"]);
        
        
        // Получаем опцию разделов
        $arResult = $CDB->searchAll(ISearch::t_iblock_element,[
            "ID"=>$nDocId
        ],[
            "IBLOCK_SECTION_ID","NAME"
        ]);
        $arOptions["SECTION_ID"] = [];
        foreach($arResult as $arItem)
            $arOptions["SECTION_ID"][] = intval($arItem["IBLOCK_SECTION_ID"]);
        
        // Получаем наличие товара, затем индексируем по нему и поднимаем при поиске товары вналичии
        фывфывфыв
            
            
        return $arOptions;
    }
    
    /**
        Получаем проиндексированные опции документа
    */
    function get($nDocId, $sDocType='PRODUCT'){
        $CDB = new \DB\CDB;
        $objCSearchDocumentType = new \Search\CSearchDocumentType;
        
        return $CDB->searchAll(ISearch::t_csearch_options,[
            "doc_id"        =>  $nDocId,
            "doc_type_id"   =>  $objCSearchDocumentType->getId($sDocType) 
        ]);
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

