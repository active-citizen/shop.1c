<?
namespace Log;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");

use AGShop;

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
        fwrite($fd, "\n".date("Y-m-d H:i:s")." $sUrl $sInput $sOutput");
        fclose($fd);
    }

}

