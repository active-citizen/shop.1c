<?php
namespace Order;
    
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\DB as DB;

class COrderStatus extends \AGShop\CAGShop{
    
    private $arStatusInfo = [];
    
    function __construct(){
        parent::__construct();
        \CModule::IncludeModule('sale');
    }
    
    function fetch($sStatusParamName, $sParamValue){
        
        switch($sStatusParamName){
            case 'ID':
            break;
            case 'NAME':
            break;
            default:
                $this->addError("Неизвестный параметр статуса "
                    .htmlspecialchars($sStatusParamName));
                return false;
            break;
        }

        $this->arStatusInfo = \CSaleStatus::GetList(
            [],$arFilter = [$sStatusParamName=>$sParamValue]
        )->Fetch();
        if(!$this->arStatusInfo)return false;
        return true;
    }
    
    function get(){
        return $this->arStatusInfo;
    }

    static function getAll($sLang = 'ru',$arSort = ["SORT"=>"ASC"]){
        $resStatus = \CSaleStatus::GetList(
            $arSort,
            ["LID"=>$sLang]
        );
        $arStatuses = [];
        while($arStatus = $resStatus->Fetch()){
            $arStatuses[] = $arStatus;
        }
        return $arStatuses;
    }
}
