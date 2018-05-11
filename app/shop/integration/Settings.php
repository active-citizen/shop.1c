<?
namespace app\shop\integration;

use app\shop\CAGShop;

class Settings extends CAGShop {
    
    var $code = 'TROYKA'; //!< Код настройки
    var $error = '';
    var $table = "int_settings";

    function __construct($sCode=''){
        parent::__construct();
        $sCode = htmlspecialchars($sCode);
        if($sCode)$this->code = $sCode;
        $CDB = new \DB\CDB;
        $arSettings = $CDB->searchOne($this->table,[
            "code"=>$this->code
        ],["id"]);
        if(!$arSettings){
            $this->addError("Неизвестный код настройки $sCode");
            return false;
        }
        return true;
    }

    /**
        Получение настроек в виде массива
    */
    function get(){
        $CDB = new \DB\CDB;
        
        $arResult = $CDB->searchOne($this->table,[
            "code"=>$this->code
        ],["data"]);
        
        if(!isset($arResult["data"]) || !$arResult["data"]){
            $this->addError('Нет настроек для "'.$this->code.'".');
            return false;
        }
        $data = json_decode($arResult["data"]);
        $data = json_decode(json_encode((array)$data), TRUE);
        
        return $data;
        
    }

    function set($arData){
        $CDB = new \DB\CDB;
        
        $CDB->update($this->table,[
            "code" => $this->code
        ],[
            "data" => json_encode($arData)
        ]);
        return true;
    }

    function add($arData){
        $CDB = new \DB\CDB;
        $CDB->insert($this->table,[
            "code"=>$this->code,"data"=>json_encode($arData)
        ]);
        return true;
    }

}

?>
