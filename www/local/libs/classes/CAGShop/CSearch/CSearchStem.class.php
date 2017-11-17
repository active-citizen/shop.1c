<?
namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."")."/CSearchDocument.class.php");
require_once(realpath(__DIR__)."/CSearch.interface.php");
require_once(realpath(__DIR__."/../../..")
    ."/vendor/vladkolodka/phpmorphy/libs/phpmorphy/src/common.php"
);
use AGShop as AGShop;
use AGShop\DB as DB;

/*
    Основы слов
*/
class CSearchStem extends \AGShop\CAGShop{

    private $objMorphy;
    private $sDicDir = '';
    private $sLang = 'ru_RU';
    private $objCDB;

    function __construct(){
        $this->sDicDir = 
        realpath(
            __DIR__
            ."/../../../vendor/vladkolodka/phpmorphy/libs/phpmorphy/dicts"
        );

        try{
            $this->objMorphy = new \phpMorphy(
                $this->sDicDir,
                $this->sLang
            );
        } catch(phpMorphy_Exception $e){
            $this->addError($e->getMessage());    
        }

        $this->objCDB = new \DB\CDB;
    }

    /** 
        Получить основу слова
        @param $sWord - слово
        @return НАчальная форма 
    */
    function get($sWord){
        $sWord = mb_strtoupper($sWord);
        $arBaseForm = $this->objMorphy->getBaseForm($sWord); 
        if(isset($arBaseForm[0]))return $arBaseForm[0];
        return false;
    }

    /**
        Сохранение базовой формы слова в БД и возврат его ID
        @param $sWord - слово
        @return ["id"=>{ID базовой формы},"word"=>{базовая форма}]
    */
    function save($sWord){
        if(!$sBaseForm = $this->get($sWord))
            $sBaseForm = $sWord;
        
        if(!$arBaseForm = $this->objCDB->searchOne(
            ISearch::t_csearch_stems,[ "word"=>$sBaseForm ]
        )){
            $arBaseForm = [
                "id"    =>  $this->objCDB->insert(
                    ISearch::t_csearch_stems,["word"=>$sBaseForm]
                ),
                "word"  =>  $sBaseForm
            ];
        }
        return $arBaseForm;
    }
    
    /**
        Получаем список ID базовых форм поискового запроса
        @param $sPhase - поисковая фраза
        @param $nMaxStems - максимальное число базовых форм в запросе
        @return массив ID базовых форм
    */
    function getBaseFormsIds($sPhase, $nMaxStems=64){
        
        // Парсим документ на слова
        $objCSearchDocument = new \Search\CSearchDocument;
        $arParsedPhrases = $objCSearchDocument->parse($sPhase);
        
        if(count($arParsedPhrases)>$nMaxStems){
            $this->addError("Too many words in search phrase");
            return false;
        }
        
        $objCDB = new \DB\CDB;

        // Разбиваем поисковый запрос на базовые формы
        $arBaseForms = [];
        foreach($arParsedPhrases as $arWord)
            $arBaseForms[] = $arWord['baseform']['word'];
            
        // Ищем ID базовых форм в таблице
        $sBaseForms = "'".implode("','",$arBaseForms)."'";
        $sQuery = "
            SELECT
                `id`
            FROM
                `".ISearch::t_csearch_stems."`
            WHERE
                `word` IN ($sBaseForms)
        ";
        $arStemsQuery = $objCDB->sqlSelect($sQuery);
        $arStemsIds = [];
        foreach($arStemsQuery as $arItem)$arStemsIds[] = $arItem['id'];
        
        return $arStemsIds;
    }

}
