<?
namespace Order;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CUser/CUser.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\CCache as CCache;

/**
    Статистики заказов
*/
class COrderStatistic extends \AGShop\CAGShop{
    
    private $objCache = null;
    private $nUserId = 0;

    function __construct($nUserId){
        $sCacheGroup = 'getOrdersCount';
        $nUserId = intval($nUserId);
        $this->objCache = new \Cache\CCache($sCacheGroup,$nUserId,COMMON_CACHE_TIME);
    }

    /**
        Получение количества заказов пользователя
    */
    function get(){
        if($sCacheData = $this->objCache->get()){
            return $sCacheData;
        }
        
        $sQuery = "
            SELECT 
                COUNT(DISTINCT ID) as `count`
            FROM
                `b_sale_order`
            WHERE
                `USER_ID`=".$this->nUserId."
                AND `STATUS_ID` IN ('N','F','AI','AG')
        ";
        $CDB = new \DB\CDB;
        $arResult = $CDB->sqlSelect($sQuery,1);
        $nCount = $arResult[0]["count"]?$arResult[0]["count"]:0;
        
        $this->objCache->set($nCount);
        return $nCount;
    }

    static function clear(){
       $this->objCache->clear();     
    }
    
}
