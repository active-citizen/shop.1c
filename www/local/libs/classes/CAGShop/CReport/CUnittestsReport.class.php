<?
namespace Report;

require_once(realpath(__DIR__)."/CReportType.class.php");
require_once(realpath(__DIR__)."/../CCurl/CCurlSimple.class.php");

use AGShop;
use Curl;

class CUnittestsReport extends CReportType{

    private $arFields = ["summary","resolutiondate","status","issuetype"];
    private $arTypes = ["История","Ошибка"];

    function __construct($sConf=[]){
        parent::__construct($sConf);
    }

    function build(){
        ob_start();
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/testscount.php");
        ob_end_clean();

        $sStartFolder = realpath(__DIR__."/..");
        $arFiles = getTree($sStartFolder);
        $arSummary = getSummary($arFiles);
        
        exec("/home/bitrix/www/local/libs/classes/unittests.sh",$arOutputs);
        $this->setResult([
            "result_text"   =>  array_pop($arOutputs),
            "coverage"      =>  $arSummary["tested_percent"]
        ]);
        return true;
    }

    function render(){

        $arData = $this->getResult();
        $nTotalTests = 0;
        if(preg_match("#\((\d+) tests\,#",$arData["result_text"],$m)){
            $nTotalTests = $m[1];
        }
        elseif(preg_match("#tests:\s*(\d+)#i",$arData["result_text"],$m)){
            $nTotalTests = $m[1];
        }

        $nFailedTests = 0;
        if(preg_match("#Failures:\s+(\d+)#i",$arData["result_text"],$m)){
             $nFailedTests = $m[1];
        }

        $sData ='Unit-тесты:<br/>';
        $sData .= 'Всего тестов '.$nTotalTests.'<br/>';
        $sData .= 'Не пройдено тестов '.$nFailedTests.'<br/>';
        $sData .= 'Покрытие  '.$arData["coverage"]."%<br/>";
        $sData .= '<br/>';

        return $sData;
    }

}
