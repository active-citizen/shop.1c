<?php
namespace User;
    
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CSSAG/CSSAGAccount.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");
     
use AGShop;
use AGShop\DB as DB;
use AGShop\SSAG as SSAG;
use AGShop\CCache as CCache;

class CUser extends \AGShop\CAGShop{
    
    private $arUserInfo = [];
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule("sale");
        \CModule::IncludeModule("forum");
    }
    
    /**
        Загрузка информации о пользователе из БД по одному из параметров
        
        @param $sUserParamName - имя параметра ("ID","LOGIN","EMAIL")
        @param $sUserParam - значение параметра
    */
    function fetch($sUserParamName, $sUserParam){
        $objCache = new \Cache\CCache("userInfo".$sUserParamName,$sUserParam,COMMON_CACHE_TIME);
        if($sCacheData = $objCache->get()){
            $this->arUserInfo = $sCacheData;
            return true;
        }
        $arResult = [];
        switch($sUserParamName){
            case 'ID':
                $arResult = \CUser::GetByID(intval($sUserParam))->Fetch();
            break;
            case 'LOGIN':
                $arResult = \CUser::GetByLogin($sUserParam)->Fetch();
            break;
            case 'EMAIL':
                $arResult = \CUser::GetList(
                    ($by="personal_country"), ($order="desc"), 
                    ["EMAIL"=>$sUserParam]
                )->Fetch();
            break;
            default:
                $this->addError("Неизвестный параметр для поиска пользователя "
                    .htmlspecialchars($sUserParamName));
                return false;
            break;
        }
        if(!$arResult){
            $this->addError("Не удалось найти пользователя для котрого ".
                htmlspecialchars($sUserParamName)."=".htmlspecialchars($sUserParam));
            return false;
        }
        $this->arUserInfo = $arResult;
        $objCache->set($arResult);
        return true;
    }
    
    /**
        Возвращает текущего пользователя
    */
    function get(){
        return $this->arUserInfo;
    }
    
    /**
        Получение число баллов на счету пользователя
        
        @param $nUserId - ID пользователя
    */
    function getPoints($nUserId){
        $objSSAGAccount = new \SSAG\CSSAGAccount('',$nUserId);
        return $objSSAGAccount->balance();
    }


    function getById($nUserId){
        $this->fetch("ID", $nUserId);
        return $this->get();
    }

    static function getCategories($nUserId){
        $arCats = [];
        if(!$nUserId)return [];
        $resCats = \CIBlockElement::GetList([],$arFilter = [
            "IBLOCK_ID"=>USERSCATS_IB_ID,
            "PROPERTY_USERS"=>$nUserId
        ],false,false,["ID"]);
        while($arCat = $resCats->Fetch())$arCats[] = $arCat["ID"];
        return $arCats;
    }

}
