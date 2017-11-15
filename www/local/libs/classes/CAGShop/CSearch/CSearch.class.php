<?
namespace Search;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\DB as DB;

/**
    Поиск по товарам магазина
*/
class CSearch extends \AGShop\CAGShop{
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
    }
}
