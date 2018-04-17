<?
namespace Log;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\DB as DB;

class CSSAGLog extends \AGShop\CAGShop{

    var $error = '';
    
    function __construct(){
        parent::__construct();
    }

    /**
        Добавление лога
    */
    static function addLog($sUrl, $sInput, $sOutput){
        $sFilename = realpath($_SERVER["DOCUMENT_ROOT"]."/..")."/logs/agapi/"
            .date("Y-m-d").".log";
        $fd = fopen($sFilename,"a");
        $nUserId = \CUser::GetID();
        $nUserId = $nUserId?$nUserId:0;
        fwrite($fd, "\n".date("Y-m-d H:i:s")." $nUserId $sUrl $sInput $sOutput");
        fclose($fd);
    }

    /**
        Добавление лога ошибки транзакции баллов

        @param $sOrderNum Номер заказа (например Б-000000001)
        @param $nOrderId ID заказа
        @param $sText текст ответа СС
    */
    static function addFailedPointsLog($sUrl, $sRequest, $sAnswer){
        $CDB = new \DB\CDB;
        $arFields = [
            "CTIME"=>date("Y-m-d H:i:s"),
            "ORDER_ID"=>intval($nOrderId),
            "URL"=>$CDB->ForSql($sUrl),
            "REQUEST"=>$CDB->ForSql($sRequest),
            "ANSWER"=>$CDB->ForSql($sAnswer),
        ];
        return $CDB->insert("int_ssag_errorlog", $arFields);
    }

}

