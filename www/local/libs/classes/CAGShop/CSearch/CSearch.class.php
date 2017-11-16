<?
namespace Search;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__)."/CSearchDocument.class.php");
require_once(realpath(__DIR__)."/CSearchDocumentType.class.php");

use AGShop;
use AGShop\DB as DB;

/**
    Поиск по магазину
*/
class CSearch extends \AGShop\CAGShop{
    
    private $nMaxWords = 10; //!< Максимальное число значащих слов в запросе
    private $sEntriesTableName = "csearch_entries";
    private $sStemsTableName = "csearch_stems";
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
    */
    function getDocsIndex($sPhase, $arOptions = []){
        
        if(!isset($arOptions["LIMIT"]))$arOptions["LIMIT"] = $this->nDocsLimit;
        if(!isset($arOptions["PAGE"]))$arOptions["PAGE"] = 1;
        
        $objCSearchDocument = new \Search\CSearchDocument;
        $arParsedPhrases = $objCSearchDocument->parse($sPhase);
        if(count($arParsedPhrases)>$this->nMaxWords){
            $this->addError("Too many words in search phrase");
            return false;
        }
        $objCDB = new \DB\CDB;
        
        $arBaseForms = [];
        foreach($arParsedPhrases as $arWord)
            $arBaseForms[] = $arWord['baseform']['word'];
            
        $sBaseForms = "'".implode("','",$arBaseForms)."'";
        $sQuery = "
            SELECT
                `id`
            FROM
                `".$this->sStemsTableName."`
            WHERE
                `word` IN ($sBaseForms)
        ";
        $arStemsQuery = $objCDB->sqlSelect($sQuery);
        $arStemsIds = [];
        foreach($arStemsQuery as $arItem)$arStemsIds[] = $arItem['id'];
        $sStemsIds = "'".implode("','",$arStemsIds)."'";
        
        
        $nOffset    =   ($arOptions["PAGE"]-1)*$arOptions["LIMIT"];
        $nLimit     =   $arOptions["LIMIT"];
        $sQuery = "
            SELECT
                `doc_id`,
                SUM(`exact`)  as `exacts`,
                COUNT(`id`) as `entries`,
                MIN(`position`) as `minpos`
            FROM
                `".$this->sEntriesTableName."`
            WHERE
                `stem_id` IN ($sStemsIds)
            GROUP BY
                `doc_id`
            ORDER BY
                `exacts` DESC,
                `entries` DESC,
                `minpos` ASC
            LIMIT
                $nOffset, $nLimit
        ";
        return $arResult = $objCDB->sqlSelect($sQuery);
    }
    
    /**
        Индексирование документа на сайте
    */
    function indexDocument(){
        $objCSearchDocumentType = new \Search\CSearchDocumentType;
    }

}
