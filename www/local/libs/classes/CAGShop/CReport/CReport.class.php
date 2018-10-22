<?
namespace Report;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");

use AGShop;

/**
    Отчеты
*/
class CReport extends \AGShop\CAGShop{

    private $arReports = [];
    private $sReportBody = '';
    private $sSubject = '';
    private $sTo = '';

    function __construct(){
        
    }

    function add($objReport){
        $this->arReports[] = $objReport;
    }


    function build(){
        foreach($this->arReports as $nKey=>$objReport)
            if(!$objReport->build())
                return $this->addError($objReport->getErrors());
        return true;
    }

    function render(){
        foreach($this->arReports as $nKey=>$objReport){
            if(!$sReportBody = $objReport->render())
                return $this->addError($objReport->getErrors());
            $this->sReportBody .= $sReportBody;
        }
        $sBody = '
        <!DOCTYPE html>
        <html>
            <meta charset="utf-8"/>
            <title>AG Nightly - shop</title>
            <style>
                table{
                    border: 1px #AAA solid;
                    width: 100%;
                    border-collapse: collapse;
                }
                th{
                    background-color: #EEE;
                }
                td{
                    padding: 5px;
                    border: 1px #aaa solid;
                }
            </style>
        </html>
        <body>';
        $sBody .= $this->sReportBody;
        $sBody .= '</body></html>';
        $this->sReportBody = $sBody;
        return $sBody;
    }


    function setMailSubject($sSubject){
        $this->sSubject = $sSubject;
    }
    
    function addRecepient($sEmail){
        $this->sTo = $sEmail;
    }


    function getResult(){
    }

    function send(){
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/common.php");
        custom_mail(
            $this->sTo,
            $this->sSubject,
            $this->sReportBody,
            "Content-type: text/html; charset=utf-8\r\n",
            ''
        );
    }

}
