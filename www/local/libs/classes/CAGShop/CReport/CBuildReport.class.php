<?
namespace Report;

require_once(realpath(__DIR__)."/CReportType.class.php");
require_once(realpath(__DIR__)."/../CCurl/CCurlSimple.class.php");

use AGShop;
use Curl;

class CBuildReport extends CReportType{

    private $arFields = ["summary","resolutiondate","status","issuetype"];
    private $arTypes = ["История","Ошибка"];

    function __construct($sConf=[]){
        parent::__construct($sConf);
    }

    function build(){
        $this->setResult(["is_success"=>true]);
        return true;
    }

    function render(){
        $sBaseUrl = '';
        $arParse = parse_url($this->getParam("jiraSearchUrl"));
        $sBaseUrl = $arParse["scheme"]."://".$arParse["host"]."/browse/";

        $arData = $this->getResult();
        $sData = '';
        $sData .= $arData["is_success"]
            ?
            'Билд успешен'
            :
            'Билд не успешен'
        ;

        $sUrl = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"];
        $sData .= '<br/><a href="'.$sUrl.'">'.$sUrl.'</a><br/><br/>';

        return $sData;
    }

}
