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

        $sData ='<h1>Дополнительная информация</h1>';
        $sData .= '<div>Git-ветка: '.$arData["branch"].'</div>';
        $sData .= '<div>Время: '.date("d.m.Y H:i:s").'</div>';

        return $sData;
    }

}
