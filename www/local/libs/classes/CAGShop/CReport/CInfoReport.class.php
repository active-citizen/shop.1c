<?
namespace Report;

require_once(realpath(__DIR__)."/CReportType.class.php");
require_once(realpath(__DIR__)."/../CCurl/CCurlSimple.class.php");

use AGShop;
use Curl;

class CInfoReport extends CReportType{

    private $arFields = ["summary","resolutiondate","status","issuetype"];
    private $arTypes = ["История","Ошибка"];

    function __construct($sConf=[]){
        parent::__construct($sConf);
    }

    function build(){
        exec("git status",$arOutput);
        $this->setResult([
            "branch"=>$arOutput[0]
        ]);
        return true;
    }

    function render(){

        $arData = $this->getResult();

        $sData ='Дополнительная информация<br/>';
        $sData .= 'Git-ветка: '.$arData["branch"].'<br/>';
        $sData .= 'Время: '.date("d.m.Y H:i:s").'<br/>';

        return $sData;
    }

}
