<?
namespace Report;

require_once(realpath(__DIR__)."/CReport.class.php");

use AGShop;

class CJiraReport extends CReport{

    function __construct(){
        require("config/jira.conf.php");    
    }

    function build(){
        
        $sData = 
    }

}
