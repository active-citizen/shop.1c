<?
namespace Catalog;

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
class CCatalogWishesStatistic extends \AGShop\CAGShop{
    
    private $objCache = null;
    private $nUserId = 0;

    function __construct($nUserId){
        $sCacheGroup = 'getWishesCount';
        $nUserId = intval($nUserId);
        $this->objCache = new \Cache\CCache($sCacheGroup,$nUserId,COMMON_CACHE_TIME);
    }

    /**
        Получение количества пожеланий пользователя
    */
    function get(){
        if($sCacheData = $this->objCache->get()){
            return $sCacheData;
        }

        $res1 = \CIBlockElement::GetList([],$arFilter = [
            "IBLOCK_ID"=>$this->IBLOCKS["WISHES"], 
            "PROPERTY_WISH_USER"=>$this->nUserId
        ],false, false);
        $nCount = $res1->SelectedRowsCount();
        
        $this->objCache->set($nCount);
        return $nCount;
    }

    static function clear(){
       $this->objCache->clear();     
    }
    
}
