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
    private $sCC = '';

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
        </html>
        <body>';
        $sBody .= $this->sReportBody;
        $sBody .= '</body></html>';
        $this->sReportBody = $sBody;
        return $sBody;
    }

    function setTemplate($sFilename){
        
    }

    function setMailSublect($sSubject){
        $this->sSubject = $sSubject;
    }
    
    function addRecepient($sEmail){
        $this->sTo = $sEmail;
    }

    function addCC($sEmail){
        $this->sCC = $sEmail;
    }

    function getResult(){
    }

    function send(){
        require($_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/common.php");
        custom_mail(
            $this->sTo,
            $this->sSubject,
            $this->sReportBody,
            "Content-type: text/html; charset=utf-8\r\n"
            ."CC: ".$this->sCC."\r\n"
        );
    }

}
