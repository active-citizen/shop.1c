<?php
namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__)."/CSearch.interface.php");

use AGShop as AGShop;
use AGShop\DB as DB;
use AGShop\Search as Search;

/**
    Документы в поиске
*/
class CSearchPhrase extends \AGShop\CAGShop{

    private $nMinLength = 3;
    private $nMaxResults = 10;

    function __construct(){
    }

    /**
        Выводим ранее введённые поисковые фразы, совпадающие началом с шаблоном
    */
    function get($sPattern=''){
        $objCDB = new \DB\CDB;
        return $objCDB->sqlSelect("
            SELECT
                `id`,`ctime`,`phrase`
            FROM
                `".ISearch::t_csearch_phrases."`
            WHERE
                `phrase` LIKE '".$objCDB->ForSql($sPattern)."%'
            LIMIT
                ".$this->nMaxResults."
        ",$this->nMaxResults);
    }
    
    /**
    
        @return ID добавленной фразы
    */
    function add($sPhrase){
        $objCDB = new \DB\CDB;
        return $objCDB->insert(
            ISearch::t_csearch_phrases,
            ["ctime"=>date("Y-m-d H:i:s"),"phrase"=>$sPhrase]
        );
    }
    
    function delete($sPhrase){
        $objCDB = new \DB\CDB;
        return $objCDB->delete(ISearch::t_csearch_phrases,["phrase"=>$sPhrase]);
        return true;
    }
    
    
}

