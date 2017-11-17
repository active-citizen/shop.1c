<?
namespace Search;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__)."/CSearchPhrase.class.php");
require_once(realpath(__DIR__)."/CSearchDocument.class.php");
require_once(realpath(__DIR__)."/CSearchDocumentType.class.php");
require_once(realpath(__DIR__)."/CSearchStem.class.php");
require_once(realpath(__DIR__)."/CSearchDocumentOption.class.php");
require_once(realpath(__DIR__)."/CSearch.interface.php");

use AGShop;
use AGShop\DB as DB;

/**
    Поиск по магазину
*/
class CSearch extends \AGShop\CAGShop{
    
    private $nMaxWords = 10; //!< Максимальное число значащих слов в запросе
    private $nDocsLimit = 12; //!< Число результатов на страницу
    
    function __construct(){
    }


    /**
        Перестроение индексных таблиц
    */
    function tablesRebuild(){
        $objCDB = new \DB\CDB;
        if(!$objCDB->runSqlFile( realpath(__DIR__)."/data/tables.sql")){
            $this->addError($objCDB->getErrors());
            return false;
        }
        return true;
    }
    
    /*
        Выдача списка ID Документов, по которым что-то нашли
        @param $sPhase - поисковая фраза
        @param $arOptions - опции 
        [
            "LIMIT" =>  {Количество результатов на страницу},
            "PAGE"  =>  {Номер страницы, начиная с 1}
        ]
    */
    function getDocsIndex($sPhase, $arOptions = []){
        $CSearchDocumentOption = new \Search\CSearchDocumentOption;
        
        if(!isset($arOptions["LIMIT"]))$arOptions["LIMIT"] = $this->nDocsLimit;
        if(!isset($arOptions["PAGE"]))$arOptions["PAGE"] = 1;
        
        $objCSearchStem = new CSearchStem;
        
        if(!$arStemsIds = $objCSearchStem->getBaseFormsIds(
            $sPhase, 
            $this->nMaxWords
        )){
            $this->addError($objCSearchStem->getErrors());
            return false;
        }
        $sStemsIds = "'".implode("','",$arStemsIds)."'";

        $objCDB = new \DB\CDB;
        
        
        $nOffset    =   ($arOptions["PAGE"]-1)*$arOptions["LIMIT"];
        $nLimit     =   $arOptions["LIMIT"];
        $sQuery = "
            SELECT
                `entries`.`doc_id`,
                `entries`.`doc_type_id`,
                SUM(`entries`.`exact`)  as `exacts`,
                COUNT(`entries`.`id`) as `entries`,
                MIN(`entries`.`position`) as `minpos`,
                COUNT(`interests`.`id`) as `interests`
            FROM
                `".ISearch::t_csearch_entries."` as `entries`
                    LEFT JOIN
                `".ISearch::t_csearch_options."` as `sections`
                    ON 
                    `entries`.`doc_id`=`sections`.`doc_id`
                    AND `entries`.`doc_type_id`=`sections`.`doc_type_id`
                    AND `sections`.`opt_type_id`="
                        .$CSearchDocumentOption->getId('SECTION_ID')
                    ."
                    LEFT JOIN
                `".ISearch::t_csearch_options."` as `interests`
                    ON 
                    `entries`.`doc_id`=`interests`.`doc_id`
                    AND `entries`.`doc_type_id`=`interests`.`doc_type_id`
                    AND `interests`.`opt_type_id`="
                        .$CSearchDocumentOption->getId('INTEREST_ID')
                    ."
            WHERE
                `entries`.`stem_id` IN ($sStemsIds)
                AND `sections`.`id` IS NOT NULL
                
            GROUP BY
                `entries`.`doc_id`
            ORDER BY
                `exacts` DESC,
                `entries` DESC,
                `minpos` ASC,
                `interests` DESC
            LIMIT
                $nOffset, $nLimit
        ";
        return $arResult = $objCDB->sqlSelect($sQuery);
    }
    
    
    /*
        Выдача результатов поискового запроса
        @param $sPhase - поисковая фраза
        @param $arOptions - опции 
        [
            "LIMIT" =>  {Количество результатов на страницу},
            "PAGE"  =>  {Номер страницы, начиная с 1}
        ]
    */
    function results($sPhase, $arOptions = []){
        
        $CSearchDocumentType = new \Search\CSearchDocumentType;
        $CSearchPhrase = new \Search\CSearchPhrase;
        // Запоминаем поисковую фразу
        $CSearchPhrase->add($sPhase);
        
        // Получаем список документов
        $arIndex = $this->getDocsIndex($sPhase, $arOptions);
        // Собираем подрообную информацию по каждому
        $arDocs = [];
        
        foreach($arIndex as $arDocIndexItem){
            $arDocs[] = $CSearchDocumentType->getDocInfo(
                $arDocIndexItem["doc_id"],
                $CSearchDocumentType->getCode($arDocIndexItem["doc_type_id"])
            );
        }
        return $arDocs;
    }
    
    /**
        Индексирование товара на сайте. Берём ещё непроиндексированный или 
        протухший в индексе товар и индексируем его
        @param $sType - тип документа
        @param $nExpires - время переиндексирования
    */
    function indexNextDocument($sType = 'PRODUCT', $nExpires = 86400){
        $objCSearchDocumentType = new \Search\CSearchDocumentType;
        $objCSearchDocument = new \Search\CSearchDocument;
        $objCSearchDocumentOption = new \Search\CSearchDocumentOption;

        // Получаем ID следующего документа согласно типу и времени индексации
        $nDocId = $objCSearchDocumentType->getNextDocument($sType, $nExpires);
        // Получаем индексируемый контент документа
        $sText = $objCSearchDocumentType->getSearchableContent($nDocId, $sType);
        // Индексируем контент
        if(!$objCSearchDocument->index($sText, $nDocId, $sType)){
            $this->addError($objCSearchDocument);
            return false;
        }
        return true;
    }
}
