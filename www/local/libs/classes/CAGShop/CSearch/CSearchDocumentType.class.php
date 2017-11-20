<?php
namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__)."/CSearch.interface.php");

use AGShop as AGShop;
use AGShop\DB as DB;
use AGShop\Search as Search;

/**
    Типы документов
*/
class CSearchDocumentType extends \AGShop\CAGShop{

    var $nExpires = 86400;  //!< Время переиндексирования документа

    private $arDocTypes = [
        "PRODUCT"=>1,
        "ARTICLE"=>2
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
        if(isset($this->arDocTypes[$sType]))return $this->arDocTypes[$sType];
        return false;
        
    }
    
    /**
        Получение кода документа по ID
    */
    function getCode($nId){
        foreach($this->arDocTypes as $sKey=>$sValue)
            if($sValue==$nId)return $sKey;
        return false;
    }
    
    /**
        Извлечение из документа определённого типа, лежащего в БД текста, который 
        нужно проиндексировать
    */
    function getSearchableContent($nId, $sType='PRODUCT'){
        switch($sType){
            case 'PRODUCT':
                $arDocument = $this->__contentProduct($nId, $sType);
            break;
        }
        return $arDocument;
    }
    
    /**
        Получение следующего к индексации документа
        @return Id следующего пригодного к индексации документа этого типа
    */
    function getNextDocument($sType = 'PRODUCT', $nExpires = ''){
        if(!$nExpires)$nExpires = $this->nExpires;
        
        $arDocument = [];
        switch($sType){
            case 'PRODUCT':
                $arDocId = $this->__nextProduct($sType,$nExpires);
            break;
        }
        return $arDocId;
    }

    /**
        Получение количества документов, пригодных для индексирования
    */
    function getUnindexedDocuments($sType = 'PRODUCT', $nExpires = ''){
        if(!$nExpires)$nExpires = $this->nExpires;
        
        $arDocument = [];
        switch($sType){
            case 'PRODUCT':
                $nCount = $this->__getUnindexedProducts($sType,$nExpires);
            break;
        }
        return $nCount;
    }

    
    /**
        Получение числа непроиндексированных элементов
    */
    private function __getUnindexedProducts($sType='PRODUCT', $nExpires = 0){
        if(!$nExpires)$nExpires = $this->nExpires;
        $sExpires = date("Y-m-d H:i:s", time()-$nExpires);

        $CDB = new \DB\CDB;
        $sQuery = "
            SELECT
                COUNT(`product`.`ID`) as `count`
            FROM
                `".ISearch::t_iblock_element."` as `product`
                    LEFT JOIN
                `".ISearch::t_csearch_documents."` as `document`
                    ON
                    `product`.`ID`=`document`.`doc_id`
                    AND
                    `document`.`doc_type_id`=".$this->getId($sType)."
                
            WHERE
                `product`.`IBLOCK_ID`='".$this->IBLOCKS["CATALOG"]."'
                AND (
                    `document`.`id` IS NULL
                    OR
                    `document`.`last_index`<'$sExpires'
                )
            LIMIT
                1
        ";
        $arNextProduct = $CDB->sqlSelect($sQuery);
        if(isset($arNextProduct[0]["count"]))return $arNextProduct[0]["count"];
        return false;
    }
    
    
    /**
        Получение следующего необходимого к индексации продукта
    */
    private function __nextProduct($sType='PRODUCT', $nExpires = 0){
        if(!$nExpires)$nExpires = $this->nExpires;
        
        $sExpires = date("Y-m-d H:i:s", time()-$nExpires);
        
        $CDB = new \DB\CDB;
        $sQuery = "
            SELECT
                `product`.`ID` as `ID`
            FROM
                `".ISearch::t_iblock_element."` as `product`
                    LEFT JOIN
                `".ISearch::t_csearch_documents."` as `document`
                    ON
                    `product`.`ID`=`document`.`doc_id`
                    AND
                    `document`.`doc_type_id`=".$this->getId($sType)."
                
            WHERE
                `product`.`IBLOCK_ID`='".$this->IBLOCKS["CATALOG"]."'
                AND (
                    `document`.`id` IS NULL
                    OR
                    `document`.`last_index`<'$sExpires'
                )
            ORDER BY
                `product`.`ID` DESC
            LIMIT
                1
        ";
        $arNextProduct = $CDB->sqlSelect($sQuery);
        if(isset($arNextProduct[0]["ID"]))return $arNextProduct[0]["ID"];
        return false;
    }
    
    
    /**
        Получение индексируемого содержимого для продукта
    */
    function __contentProduct($nId, $sType = 'PRODUCT'){
        $CDB = new \DB\CDB;
        
        // Получаем общую информацию о продукте
        $arNextProduct = $CDB->searchOne(ISearch::t_iblock_element,[
            "ID"=>intval($nId)
        ],[
            "ID","NAME"
        ]);
        
        $sContent = $arNextProduct["NAME"]." ".$arNextProduct["DETAIL_TEXT"];
        
        return $sContent;
    }
    
    /**
        Выдача информации по документу, согласно его типу
    */
    function getDocInfo($nId, $sType = 'PRODUCT'){
        switch($sType){
            case 'PRODUCT':
                $arDocument = $this->__getInfoProduct($nId, $sType);
            break;
        }
        return $arDocument;
    }
    
    private function __getInfoProduct($nId, $sType='PRODUCT'){
        $CDB = new \DB\CDB;
        // Получаем раздел продукта
        $arProduct = $CDB->searchOne(ISearch::t_iblock_element,[
            "ID"=>$nId
        ],[
            "ID","NAME","CODE","IBLOCK_SECTION_ID","DETAIL_PICTURE","ACTIVE"
        ]);
        
        $arSection = $CDB->searchOne(ISearch::t_iblock_section,[
            "ID"=>$arProduct["IBLOCK_SECTION_ID"]
        ],[
            "ID","NAME","CODE"
        ]);
        unset($arProduct["IBLOCK_SECTION_ID"]);
        $arProduct["SECTION_CODE"] = $arSection["CODE"];
        $arProduct["SECTION_NAME"] = $arSection["NAME"];
        
        $arPicture = $CDB->searchOne(ISearch::t_file,[
            "ID"=>$arProduct["DETAIL_PICTURE"]
        ],[
            "SUBDIR","FILE_NAME"
        ]);
        
        // Цена
        $arPrice = $CDB->searchOne(ISearch::t_iblock_element_property,[
            "IBLOCK_PROPERTY_ID" => $this->PROPERTIES["PRICE"],
            "IBLOCK_ELEMENT_ID"  => $nId
        ],[
            "VALUE"
        ]);
        $arProduct["PRICE"] = $arPrice["VALUE"];

        // Прятать по дате
        $arHide = $CDB->searchOne(ISearch::t_iblock_element_property,[
            "IBLOCK_PROPERTY_ID" => $this->PROPERTIES["HIDE_DATE"],
            "IBLOCK_ELEMENT_ID"  => $nId
        ],[
            "VALUE"
        ]);
        if($arHide["VALUE"]){
            $tmp = date_parse($arHide["VALUE"]);
            $expire = mktime(
                $tmp["hour"],$tmp["minute"],$tmp["second"],
                $tmp["month"],$tmp["day"],$tmp["year"]
            );
            if($expire<time())
                $arProduct["HIDE_ON_DATE"] = 'Y';
            else
                $arProduct["HIDE_ON_DATE"] = 'N';
        }
        else{
            $arProduct["HIDE_ON_DATE"] = 'N';
        }
        
        unset($arProduct["DETAIL_PICTURE"]);
        $arProduct["IMAGE"] = "/upload/".$arPicture["SUBDIR"]."/"
            .$arPicture["FILE_NAME"];
            
        return $arProduct;
    }
    
}

