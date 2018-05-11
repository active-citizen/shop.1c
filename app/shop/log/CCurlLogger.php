<?

namespace app\shop\log;

use app\shop\CAGShop;

class CCurlLogger extends CAGShop
{

    var $error = '';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Добавление лога
     */
    function addLog(
        $arParams = array() //!< Массив параметров лога
        /**
         * $arParams = array(
         * "ORDER_NUM" =>  "Номер заказа в виде Б-NNNNNNNN или NNNNNNN",
         * "URL"       =>  "url на который отправлялся запрос",
         * "DATA"      =>  "Полученные в результате запроса данные",
         * "POST_DATA" =>  "Отправленные запросом данные"
         * )
         */
    )
    {
        global $DB;

        if (!isset($arParams["ORDER_NUM"])) {
            $this->addError("Не указан номер заказа");
            return false;
        }
        if (!isset($arParams["URL"])) {
            $this->addError("Не указан URL");
            return false;
        }
        if (!preg_match("#^https?://.*?/.*$#", $arParams["URL"])) {
            $this->addError("Некорректный URL");
            return false;
        }
        if (!isset($arParams["DATA"])) {
            $this->addError("Не указаны данные");
            return false;
        }

        $sQuery = "INSERT INTO `int_curl_logger`(
            `id`,`ctime`,`url`,`order_num`,`data`,`post_data`
        )";

        $sQuery .= "VALUES(";
        $sQuery .= "NULL";
        $sQuery .= "," . time();
        $sQuery .= ",'" . $DB->ForSql($arParams["URL"]) . "'";
        $sQuery .= ",'" . $DB->ForSql($arParams["ORDER_NUM"]) . "'";
        $sQuery .= ",'" . $DB->ForSql($arParams["DATA"]) . "'";
        $sQuery .= ",'" . $DB->ForSql($arParams["POST_DATA"]) . "'";
        $sQuery .= ");";


        $DB->Query($sQuery);

        $nLastId = 0;
        if ($nLastId = $DB->LastID()) {
            return $nLastId;
        }

        return false;
    }

    function getById($nLogId)
    {
        global $DB;
        if (!$nLogId = intval($nLogId)) {
            $this->addError("Некорректный ID лога для выборки");
            return false;
        }

        $sQuery = "SELECT * FROM `int_curl_logger` WHERE `id`='" .
            $nLogId
            . "' LIMIT 1";

        return $DB->Query($sQuery)->Fetch();
    }

    /**
     * Удаление лога по его ID
     */
    function remove($nLogId)
    {
        global $DB;
        if (!$nLogId = intval($nLogId)) {
            $this->addError("Некорректный ID лога для удаления");
            return false;
        }
        $DB->Query("DELETE FROM `int_curl_logger` WHERE `id`='" .
            $DB->ForSql($nLogId)
            . "' LIMIT 1");
        return true;
    }

    /**
     * Удаление логов указанного заказа
     */
    function removeByOrderNum($nOrderNum)
    {
        global $DB;

        if (!preg_match("#^(Б\-\d+|\d+)$#", $nOrderNum)) {
            $this->addError("Некорректный номер заказа");
            return false;
        }

        $sQuery = "DELETE FROM `int_curl_logger`";
        $sQuery .= "WHERE `order_num`='" . $nOrderNum . "' ";
        $DB->Query($sQuery);
        return true;
    }

    /**
     * Получение массива логов по указанному номеру заказа
     */
    function getByOrderNum(
        $nOrderNum,
        $sSort = 'DESC'
    ) {
        global $DB;

        $sQuery = "SELECT * FROM `int_curl_logger` WHERE";
        $sQuery .= "`order_num`='" . $DB->ForSql($nOrderNum) . "' ORDER BY `id` "
            . $DB->ForSql($sSort);

        $arResult = array();
        $res = $DB->Query($sQuery);
        while ($arr = $res->Fetch()) {
            $arResult[$arr["id"]] = $arr;
        }

        return $arResult;
    }

}

