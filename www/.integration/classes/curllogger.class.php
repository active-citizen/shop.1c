<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

    class CCurlLogger{

        var $error = '';
        
        function __construct(){
//            global $DB;
//            if(!$DB->db_Conn->stat)
//            $DB->Connect($DB->DBHost,$DB->DBName,$DB->DBLogin,$DB->DBPassword);

        }

        /**
            Добавление лога
        */
        function addLog(
            $arParams=array() //!< Массив параметров лога
            /**
                $arParams = array(
                    "ORDER_NUM" =>  "Номер заказа в виде Б-NNNNNNNN или NNNNNNN",
                    "URL"       =>  "url на который отправлялся запрос",
                    "DATA"      =>  "Полученные в результате запроса данные",
                    "POST_DATA" =>  "Отправленные запросом данные"
                )
            */
        ){
            global $DB;
            $this->error = '';

            if(!isset($arParams["ORDER_NUM"])){
                $this->error = ''
                    ."Не указан номер заказа";
                return false;
            }
            if(!preg_match("#^(Б\-\d+|\d+)$#", $arParams["ORDER_NUM"])){
                $this->error = ''
                    ."Некорректный номер заказа";
                return false;
            }
            if(!isset($arParams["URL"])){
                $this->error = ''
                    ."Не указан URL";
                return false;
            }
            if(!preg_match("#^https?://.*?/.*$#", $arParams["URL"])){
                $this->error = ''
                    ."Некорректный URL";
                return false;
            }
            if(!isset($arParams["DATA"])){
                $this->error = ''
                    ."Не указаны данные";
                return false;
            }

            $sQuery = "INSERT INTO `int_curl_logger`(
                `id`,`ctime`,`url`,`order_num`,`data`,`post_data`
            )";

            $sQuery .= "VALUES(";
            $sQuery .= "NULL";
            $sQuery .= ",".time();
            $sQuery .= ",'".$DB->ForSql($arParams["URL"])."'";
            $sQuery .= ",'".$DB->ForSql($arParams["ORDER_NUM"])."'";
            $sQuery .= ",'".$DB->ForSql($arParams["DATA"])."'";
            $sQuery .= ",'".$DB->ForSql($arParams["POST_DATA"])."'";
            $sQuery .= ");";


            $DB->Query($sQuery);

            $nLastId  = 0;
            if($nLastId = $DB->LastID())return $nLastId;

            return false;
        }

        function getById($nLogId){
            global $DB;
            $this->error = '';
            if(!$nLogId =intval($nLogId)){
                $this->error = ''
                    ."Некорректный ID лога для выборки";
                return false;
            }
            
            $sQuery = "SELECT * FROM `int_curl_logger` WHERE `id`='".
                $nLogId
            ."' LIMIT 1";

            return $DB->Query($sQuery)->Fetch();
        }

        /**
            Удаление лога по его ID
        */
        function remove($nLogId){
            global $DB;
            $this->error = '';
            if(!$nLogId =intval($nLogId)){
                $this->error = ''
                    ."Некорректный ID лога для удаления";
                return false;
            }
            $DB->Query("DELETE FROM `int_curl_logger` WHERE `id`='".
                $DB->ForSql($nLogId)
            ."' LIMIT 1");
            return true;
        }

        /**
            Удаление логов указанного заказа
        */
        function removeByOrderNum($nOrderNum){
            global $DB;
            $this->error = '';

            if(!preg_match("#^(Б\-\d+|\d+)$#", $nOrderNum)){
                $this->error = ''
                    ."Некорректный номер заказа";
                return false;
            }

            $sQuery = "DELETE FROM `int_curl_logger`";
            $sQuery .="WHERE `order_num`='".$nOrderNum."' ";
            $DB->Query($sQuery);
            return true;
        }

        /**
            Получение массива логов по указанному номеру заказа
        */
        function getByOrderNum(
            $nOrderNum, 
            $sSort = 'DESC'
        ){
            global $DB;
            $this->error = '';

            if(!preg_match("#^(Б\-\d+|\d+)$#", $nOrderNum)){
                $this->error = ''
                    ."Некорректный номер заказа";
                return false;
            }

            $sQuery = "SELECT * FROM `int_curl_logger` WHERE";
            $sQuery .="`order_num`='".$nOrderNum."' ORDER BY `id` $sSort";

            $arResult = array();
            $res = $DB->Query($sQuery);
            while($arr = $res->Fetch())$arResult[$arr["id"]] = $arr;

            return $arResult;
        }

    }

