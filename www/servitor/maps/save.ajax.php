<?
/**
 * Сохранение карты проезда по координатам, ID производителя и zoom
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!$USER->IsAdmin())die;

$Coords = '55.0000,55.0000';
if(isset($_GET["coords"]) && preg_match(
    "#\d+\.\d+\,\d+\.\d+#",
    $_GET["coords"]
    ))$Coords = $_GET["coords"];

$zoom = 5;
if(isset($_GET["zoom"]) && intval($_GET["zoom"]))$zoom = intval($_GET['zoom']);

$id = 1;
if(isset($_GET["id"]) && intval($_GET["id"]))$id = intval($_GET['id']);

$store = 0;
if(isset($_GET["store"]) && intval($_GET["store"]))$store = intval($_GET['store']);

$answer = array();
define("ROOTDIR",$_SERVER["DOCUMENT_ROOT"]."/upload/manufacturers/");


$url = 'https://static-maps.yandex.ru/1.x/?lang=ru-RU&ll='.$Coords
    .'&z='.$zoom.'&l=map,skl&size=300,300&pt='.$Coords.',flag';

require_once(realpath(dirname(__FILE__)."/../../.integration/classes/curl.class.php"));
$curl = new curlTool;
$content = $curl->get($url);
$url = "/upload/manufacturers/$id.png";
if($store)
    $url = "/upload/stores/$id.png";
$fd = fopen($_SERVER["DOCUMENT_ROOT"].$url,"w");
fwrite($fd,$content);
fclose($fd);
$answer["url"] = $url."?".rand();


echo json_encode($answer);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
