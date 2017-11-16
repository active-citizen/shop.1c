<?
namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
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
    private $sTableName = 'csearch_stems';
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
            $this->sTableName,[ "word"=>$sBaseForm ]
        )){
            $arBaseForm = [
                "id"    =>  $this->objCDB->insert(
                    $this->sTableName,["word"=>$sBaseForm]
                ),
                "word"  =>  $sBaseForm
            ];
        }
        return $arBaseForm;
    }

}
