<?
namespace Order;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CUser/CUser.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogSKU.class.php");
require_once(realpath(__DIR__)."/COrderStatus.class.php");
require_once(realpath(__DIR__)."/COrderProperty.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\User as User;
use AGShop\Catalog as Catalog;
use AGShop\Order as Order;

/**
    Управление заказами
*/
class COrder extends \AGShop\CAGShop{
    
    private $arOrderParams = []; // Массив параметров заказа
    var $objUser = null;
    var $objStatus = null;
    private $arSKUs = [];
    private $arProps = [];
    
    private $arEnabledOrderParams = [
        "Id"            =>  "ID заказа",
        "Num"           =>  "Номер заказа",
        "XML_ID"        =>  "XML_ID заказа",
        "DateInsert"    =>  "Дата добавления заказа",
        "DateUpdate"    =>  "Дата обновления заказа"
    ];


    function addSKU($nSKUId, $nStoreId, $nCount){
        $nSKUId = intval($nSKUId);
        $nStoreId = intval($nStoreId);
        $nCount = intval($nCount);
        if(!$nCount){
            $this->addError("Неверно указано количество товара");
            return false;
        }
        
        $objStore = new \Catalog\CCatalogStore;
        if(!$objStore->fetch($nStoreId)){
            $this->addError("Склад с ID $nStoreId не существует");
            return false;
        }
        
        $objSKU = new \Catalog\CCatalogSKU;
        if(!$objSKU->fetch($nSKUId)){
            $this->addError("Не найти добавить торговое предложение $nSKUId ");
            return false;
        }
        
        $this->arSKUs[] = [
            "SKU"       =>$objSKU->get(),
            "AMOUNT"    => $nCount
        ];
        return true;
    }
    
    function getSKUs(){
        return $this->arSKUs;
    }

    function create(){
        return true;
    }
    
    function __construct(){
        parent::__construct();
        $this->objUser = new \User\CUser;
        $this->objStatus = new \Order\COrderStatus;
    }

    function getPropertyByCode($sPropCode){
        $sPropCode = htmlspecialchars($sPropCode);
        if(!isset($this->arProps[$sPropCode])){
            $this->addError("Свойство $sPropCode не установлено");
            return false;
        }
        return $this->arProps[$sPropCode];
    }


    function setPropertyByCode($sPropCode, $sPropValue){
        $sPropName = htmlspecialchars($sPropCode);
        $objProp = new \Order\COrderProperty;
        if(!$objProp->existsByCode($sPropCode)){
            $this->addError("Несуществующее свойство заказа $sPropCode");
            return false;
        }
        $this->arProps[$sPropCode] = $sPropValue;
        return true;
    }

    function fetchProperty($sPropName){
        $objProp = \OrderPropery\COrderPropery;
        $objProp->fetch($sPropName);
    }
    
    function fetchAllProperties(){
    }
    
    function getPropery($sPropName){
        if(!isset($this->arProps[$sPropName])){
            $this->addError("Неизвестное свойство заказа "
                .htmlspecialchars($sPropName));
            return false;
        }
        return $this->arProps[$sPropName];
    }
    
    function getAllProperties(){
        return $this->arProps;
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

    function __setParamDateInsert($sValue){
        if(!$sDate = $this->getDateISO($sValue))return false;
        $this->arOrderParams["DateInsert"] = $sDate;
        return true;
    }

    function __setParamDateUpdate($sValue){
        if(!$sDate = $this->getDateISO($sValue))return false;
        $this->arOrderParams["DateUpdate"] = $sDate;
        return true;
    }

    function __getParamNum(){
        return $this->arOrderParams["Num"];
    }

    function __getParamDateInsert(){
        return $this->arOrderParams["DateInsert"];
    }

    function __getParamDateUpdate(){
        return $this->arOrderParams["DateUpdate"];
    }
    
}
