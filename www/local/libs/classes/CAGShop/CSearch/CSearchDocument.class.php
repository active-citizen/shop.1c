<?php

namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__)."/CSearchStem.class.php");
require_once(realpath(__DIR__)."/CSearchDocumentType.class.php");
require_once(realpath(__DIR__)."/CSearchDocumentOption.class.php");
require_once(realpath(__DIR__)."/CSearch.interface.php");


use AGShop as AGShop;
use AGShop\DB as DB;
use AGShop\Search as Search;

/**
    Документы в поиске
*/
class CSearchDocument extends \AGShop\CAGShop{

    private $arStopWords = [];
    private $nMinWordLength = 3;
    private $nMaxWordLength = 24;

    function __construct(){
        $this->arStopWords = [
            'ЛИБО','НИБУДЬ','ДЛЯ','ЭТО',
            'OUT','WITH','UNDER','UNTIL','БЕЗ','КАК','ЧТО'
        ];
    }

    /**
        Разбор документа на слова и словоформы
        @param $sText
        @return Массив разбора 
    */
    function parse($sText){
        $objCSearchStem = new \Search\CSearchStem;
        $sText = strip_tags($sText);
        $sText = mb_strtoupper($sText);
        $sText = str_replace("\n"," ", $sText);
        $sText = str_replace("\r"," ", $sText);
        $sText = preg_replace(
            "#[^АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯABCDEFGHIJKLMNOPQRSTUVWXYZ]#",
            " ",$sText);
        $sText = preg_replace("#\s+#"," ",$sText);
        $arWords = explode(" ",$sText);
        $arResult = [];
        $nPosition = 0;
        foreach($arWords as $sWord){
            if(!$sWord = trim($sWord))continue;
            if(in_array($sWord, $this->arStopWords))continue;
            if(mb_strlen($sWord)<$this->nMinWordLength)continue;
            if(mb_strlen($sWord)>$this->nMaxWordLength)continue;
            $nPosition++;

            $arResult[$sWord] = [
                "baseform"  =>  $objCSearchStem->save($sWord),
                "position"  =>  $nPosition
            ];
        }
        return $arResult;
    }
    
    /**
        Индексирование текста документа
        @param $sText - документ
        @param $nDocId - внешний ID документа (ID товара, ID статьи и пр.)
        @param $sDocType - тип документа (PRODUCT, ARTICLE и пр.)
        @return количество вхождений слов
    */
    function index($sText, $nDocId = 999999999, $sDocType='PRODUCT'){
        $objCSearchStem = new \Search\CSearchStem;
        $objSearchDocumentType = new \Search\CSearchDocumentType;
        $objCDB = new \DB\CDB;
        if(!$nDocId = intval($nDocId)){
            $this->addError("Document ID for indexing is undefined");
            return false;
        }
        if(
            !$nDocType = $objSearchDocumentType->getId($sDocType)
        ){
            $this->addError("Document TYPE for indexing is undefined");
            return false;
        }
        if(!$sText = trim($sText)){
            $this->addError("Document body for indexing is empty");
            return false;
        }
        $arParsedDoc = $this->parse($sText);

        // Размещаем информацию о вхожденийх слов в документы
        foreach($arParsedDoc as $sEntry=>$arEntry){
            // Получаем ID базовой формы слова
            $arStem = $objCSearchStem->save($sEntry);
            $arFields = [
                "entry"         =>  $sEntry,
                "stem_id"       =>  $arStem['id'],
                "position"      =>  $arEntry['position'],
                "doc_id"        =>  $nDocId,
                "doc_type_id"   =>  $nDocType,
                "exact"         =>  ($sEntry==$arStem['word']?1:0)
            ];
            // Сохраняем вхождение
            $this->saveEntry($arFields);
        }
        
        // Индексируем опции документа
        $CSearchDocumentOption = new \Search\CSearchDocumentOption;
        $arOptions = $CSearchDocumentOption->fetch($nDocId, $sDocType);
        $CSearchDocumentOption->save($nDocId, $sDocType, $arOptions);
        
        // Обновляем/добавляем 
        if(!$this->save([
            "doc_id"        =>  $nDocId,
            "doc_type_id"   =>  $nDocType,
            "rank"          =>  $this->getRank($nDocId, $sDocType)
        ])){
            return false;
        }
        return count($arParsedDoc);
    }
    
    
    /**
        Вычисление ранга документа
        @param $arEntries - массив вхождений в документ слов
        @param $arOptions - массив опций
    */
    function getRank($nDocId, $sDocType='PRODUCT'){
        $objCSearchDocumentOption = new \Search\CSearchDocumentOption;

        // Получаем записи в БД об опциях документа 
        $arOpts = $objCSearchDocumentOption->getSummary($nDocId, $sDocType);
        
        //////////////////////// Веса ///////////////////////////
        $arWeight = [];
        $arWeight["INTEREST_ID"] = 0.05 * count($arOpts["INTEREST_ID"]);
        $arWeight["AT_STORAGE"] = 0.1 * count($arOpts["AT_STORAGE"]);
        $arWeight["WHISHES"] = 0.01 * (
            count($arOpts["WHISHES"])
            ?
            intval($arOpts["WHISHES"][0])
            :
            0
        );
        
        // Считаем итоговый ранг
        $rank = 1;
        foreach($arWeight as $nWeight)$rank += $nWeight;
        
        return $rank;
    }
    
    /**
        Сохранение информации по проиндексированному документу
        * дата последней индексации
        * прочее
    */
    function save($arFields){
        
        if(!isset($arFields["doc_id"]) || !intval($arFields["doc_id"])){
            $this->addError("Document ID not defined");
            return false;
        }

        if(!isset($arFields["doc_type_id"]) || !intval($arFields["doc_type_id"])){
            $this->addError("Document type ID not defined");
            return false;
        }
        
        $objCDB = new \DB\CDB;
        $objSearchDocumentType = new \Search\CSearchDocumentType;
        $arFilter = [
            "doc_id"=>$arFields["doc_id"],
            "doc_type_id"=>$arFields["doc_type_id"]
        ];
        
        $arFields = $arFields;
        $arFields['last_index'] = date("Y-m-d H:i:s");
        if($objCDB->searchOne(ISearch::t_csearch_documents,$arFilter)){
            $objCDB->update(ISearch::t_csearch_documents, $arFilter, $arFields);
        }
        else{
            if(!$objCDB->insert(ISearch::t_csearch_documents,$arFields)){
                $this->addError($objCDB->getErrors());
                return false;
            }
        }
        return true;
    }
    
    /**
        Сохранение вхождения
    */
    function saveEntry($arFields){
        $objCDB = new \DB\CDB;
        $arFilter = $arFields;
        unset($arFilter['entry']);
        if(!$objCDB->searchOne(ISearch::t_csearch_entries, $arFilter)){
            if(!$objCDB->insert(ISearch::t_csearch_entries,$arFields))
                $this->addError("Cant save entry ".json_encode($arFields));
        }
        else
            return true;
    }
    
    /**
        Переиндексация текста
        @param $sText - документ
        @param $nDocId - внешний ID документа (ID товара, ID статьи и пр.)
        @param $sDocType - тип документа (PRODUCT, ARTICLE и пр.)
        @return количество вхождений слов
    */
    function reindex($sText, $nDocId = 999999999, $sDocType='PRODUCT'){
        $this->delete($nDocId, $sDocType);
        return $this->index($sText, $nDocId = 999999999, $sDocType='PRODUCT');
    }
    
    /**
        Удаление документа и всех вхождений основ в него
    */
    function delete($nDocId = 999999999, $sDocType='PRODUCT'){
        $objSearchDocumentType = new \Search\CSearchDocumentType;
        if(!$nDocType = $objSearchDocumentType->getId($sDocType)){
            $this->addError("Document TYPE for indexing is undefined");
            return false;
        }
        
        $objCDB = new \DB\CDB;
        $arFilter = ["doc_id"=>$nDocId,"doc_type_id"=>$nDocType];
        $objCDB->delete(ISearch::t_csearch_entries, $arFilter);
        $objCDB->delete(ISearch::t_csearch_documents, $arFilter);
    }
    
    /**
        Получение всех вхождений в документ
    */
    function getEntries($nDocId = 999999999, $sDocType='PRODUCT'){
        $objSearchDocumentType = new \Search\CSearchDocumentType;
        if(!$nDocType = $objSearchDocumentType->getId($sDocType)){
            $this->addError("Document TYPE for indexing is undefined");
            return false;
        }

        $objCDB = new \DB\CDB;
        $arFilter = ["doc_id"=>$nDocId,"doc_type_id"=>$nDocType];
        return $objCDB->searchAll(ISearch::t_csearch_entries, $arFilter);
    }
    
}

