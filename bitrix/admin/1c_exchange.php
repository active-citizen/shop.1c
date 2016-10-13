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

if(
    isset($_GET["type"]) && isset($_GET["mode"])
    && $_GET["type"]=='sale' && $_GET["mode"]=="query"
){
    include("../../.integration/order_export.ajax.php");
    die;
}

if(
    isset($_GET["type"]) && isset($_GET["mode"]) && isset($_GET["filename"])
    && $_GET["type"]=='sale' && $_GET["mode"]=="file" && $_GET["filename"]
){
    include("../../.integration/order_import.ajax.php");
    die;
}


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/1c_exchange.php"); 
?>
