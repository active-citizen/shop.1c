<?
namespace Report;

require_once(realpath(__DIR__)."/CReportType.class.php");
require_once(realpath(__DIR__)."/../CCurl/CCurlSimple.class.php");

use AGShop;
use Curl;

class CJiraReport extends CReportType{

    private $arFields = ["summary","resolutiondate","status","issuetype"];
    private $arTypes = ["История","Ошибка"];

    function __construct($sConf=[]){
        parent::__construct($sConf);
    }

    function build(){
        $objCurl = new \Curl\CCurlSimple;

        $sJql = urlencode("project=".$this->getParam("project")
            ." AND component=".$this->getParam("component")." "
            ." AND issuetype in (".implode(",",$this->arTypes).")"
            ." AND status changed to \"".$this->getParam("toStatus")
                ."\" during (startOfDay(".$this->getParam("fromDay")
                ."), endOfDay(".$this->getParam("toDay").")) ");


        $sData =  $objCurl->get(
            $sUrl = $this->getParam("jiraSearchUrl")."?jql=$sJql&fields=".implode(",",$this->arFields),
            $arHeaders = [
                "Authorization: Basic ".base64_encode(
                    $this->getParam("username").":".$this->getParam("password")
                ),
            ]
        );
        $arData = json_decode($sData, true);
        if(!$arData)return false;
        $this->setResult($arData);
        return true;
    }

    function render(){
        $sBaseUrl = '';
        $arParse = parse_url($this->getParam("jiraSearchUrl"));
        $sBaseUrl = $arParse["scheme"]."://".$arParse["host"]."/browse/";

        $arData = $this->getResult();
        if(!$arData['issues']){
            $sData = 'Новых выполненных задач нет';
        }
        else{
            $sData = 'Выполненные задачи:';
            foreach($arData["issues"] as $arIssue){
                $sData .= '<br/>'
                    .$arIssue["key"]
                    .' '.$arIssue["fields"]["summary"]
                    .' (<a href="'.$sBaseUrl.$arIssue["key"].'">'
                        .$sBaseUrl.$arIssue["key"]
                    .'</a>)';
            }
        }
        $sData .= '
            <br/><br/>
        '; 

        return $sData;
    }

}
