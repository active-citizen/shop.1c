<?
namespace Order;

require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\DB as DB;

/**
    Управление заказами
*/
class COrder extends \AGShop\CAGShop{
    
    private $arOrderParams = []; // Массив параметров заказа
    private $sOrderType = 'Б';
    private $arUser = [];
    
    private $arEnabledOrderParams = [
        "Num"       =>  "Номер заказа",
        "UserId"    =>  "ID пользователя, совершившего заказ",
        "UserPhone" =>  "Номер телефона пользователя совершившего заказ",
        "UserEmail" =>  "Email пользователя, совершившего заказ"
    ];
    
    function __construct(){
        parent::__construct();
    }

    /**
        Добавление заказа
    */
    function add(){
        
    }
    
    function getOrderType(){
        return $this->sOrderType;
    }

    function setParam($sParamName, $paramValue){
        $sMethodName = "__setParam".$sParamName;
        if(!isset($this->arEnabledOrderParams[$sParamName])){
            $this->addError("Неизвестный параметр заказа $sParamName");
            return false;
        }
        elseif(!method_exists($this, $sMethodName)){
            $this->addError("Нет обработчика параметра $sParamName");
            return false;
        }
        
        if(!$this->$sMethodName($paramValue))return false;
        
        return true;
    }
    
    
    function getParam($sParamName){
        $sMethodName = "__getParam".$sParamName;
        if(!isset($this->arEnabledOrderParams[$sParamName])){
            $this->addError("Неизвестный параметр заказа $sParamName");
            return false;
        }
        elseif(!method_exists($this, $sMethodName)){
            $this->addError("Нет обработчика параметра $sParamName");
            return false;
        }
        
        return $this->$sMethodName();
    }
    
    function __setParamNum($sValue){
        if(!preg_match("#^(.*?)\-(\d+)$#", $sValue, $m)){
            $this->addError("Некорректный формат номера заказа $sValue");
            return false;
        }
        
        $this->sOrderType = $m[1];
        $this->arOrderParams["Num"] = $sValue;
        
        return true;
    }

    function __getParamNum(){
        return $this->arOrderParams["Num"];
    }


}
