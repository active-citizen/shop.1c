<?
include("../../.integration/logger.inc.php");
if(
    isset($_GET["type"]) && isset($_GET["mode"]) && isset($_GET["filename"])
    && $_GET["type"]=='catalog' && $_GET["mode"]=="import"
    && file_exists($_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$_GET["filename"])
){
    include($_SERVER["DOCUMENT_ROOT"]."/.integration/1c_catalog.ajax.php");
    die;
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/1c_exchange.php"); 
?>
