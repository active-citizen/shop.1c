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
        $sData = '<h1>Билд</h1>';
        $sData .= $arData["is_success"]
            ?
            '<div style="color:green">Успешен</div>'
            :
            '<div style="color:red">Неуспешен</div>'
        ;

        $sUrl = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"];
        $sData .= '<a href="'.$sUrl.'">'.$sUrl.'</a>';

        return $sData;
    }

}
