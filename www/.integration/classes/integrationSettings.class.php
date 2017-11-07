<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

    class CIntegrationSettings{
        
        var $code = 'TROYKA'; //!< Код настройки
        var $error = '';
        var $table = "int_settings";

        function __construct($sCode=''){
            global $DB;
            if($sCode)$this->code = $sCode;
            if(!$DB->Query(
                "SELECT 
                    `id` 
                 FROM 
                    `".$this->table."` 
                 WHERE
                    `code`='".$DB->ForSql($this->code)."'
                 LIMIT 1"
            )->Fetch()){
                $this->error = __CLASS__.":".__LINE__
                    .':Неизвестный код настройки "'.$sCode.'"';
                return false;
            }
            return true;
        }

        /**
            Получение настроек в виде массива
        */
        function get(){
            global $DB;
            $arResult = $DB->Query($sQuery = "
                SELECT
                    `data`
                FROM    
                    `".$this->table."`
                WHERE   
                    `code`='".$this->code."'
                LIMIT 
                    1
            ")->Fetch(); 
            if(!isset($arResult["data"]) || !$arResult["data"]){
                $this->error = 'Нет настроек для "'.$this->code.'".'.$sQuery;
                return false;
            }
            $data = json_decode($arResult["data"]);
            $data = json_decode(json_encode((array)$data), TRUE);
            
            return $data;
            
        }

        function set($arData){
            global $DB;
            $sQuery = "
                UPDATE  
                    `".$this->table."`
                SET
                    `data`='".$DB->ForSql(json_encode($arData))."'
                WHERE   
                    `code`='".$DB->ForSql($this->code)."'    
                LIMIT
                    1
            ";
            $DB->Query($sQuery);
            return true;
        }

        function add($arData){
            global $DB;
            $sQuery = "
                INSERT INTO `".$this->table."`(
                    `code`,`data`
                )
                VALUE(
                    '".$this->code."','".
                    $DB->ForSql(json_encode($arData))
                    ."'
                )
            ";
            $DB->Query($sQuery);
            return true;
        }

    }

?>
