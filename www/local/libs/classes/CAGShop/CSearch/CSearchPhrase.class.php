<?php
namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop as AGShop;
use AGShop\DB as DB;
use AGShop\Search as Search;

/**
    Документы в поиске
*/
class CSearchPhrase extends \AGShop\CAGShop{

    private $nMinLength = 3;
    private $nMaxResults = 10;
    private $sTableName = 'csearch_phrases';

    function __construct(){
    }

    /**
    */
    function get($sPattern=''){
        $objCDB = new \DB\CDB;
        return $objCDB->sqlSelect("
            SELECT
                `id`,`ctime`,`phrase`
            FROM
                `".$this->sTableName."`
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
            $this->sTableName,["ctime"=>date("Y-m-d H:i:s"),"phrase"=>$sPhrase]
        );
    }
    
    function delete($sPhrase){
        $objCDB = new \DB\CDB;
        return $objCDB->delete($this->sTableName,["phrase"=>$sPhrase]);
        return true;
    }
    
    
}

