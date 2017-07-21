<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);
define("BX_BUFFER_USED", true);
define("BX_CLUSTER_GROUP", 2);
$GLOBALS["DBType"] = 'mysql';

    if(!isset($_SERVER["DOCUMENT_ROOT"]) || !$_SERVER["DOCUMENT_ROOT"])
        $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$DB->Connect($DB->DBHost,$DB->DBName,$DB->DBLogin,$DB->DBPassword); 

/*
    $app = \Bitrix\Main\Application::getInstance();
    $con = $app->getConnection();
    $DB->db_Conn = $con->getResource();
    // "authorizing" as admin
    $_SESSION["SESS_AUTH"]["USER_ID"] = 1;
*/

while (ob_get_level()) {
    ob_end_flush();
}






