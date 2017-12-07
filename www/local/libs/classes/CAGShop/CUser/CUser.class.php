<?php
namespace User;
    
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\DB as DB;

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
        require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/point.class.php");
        $objPoints = new \bxPoint;
        return $objPoints->fetchAccountFromAPI();
    }
}
