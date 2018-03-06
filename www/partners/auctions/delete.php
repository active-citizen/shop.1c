<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Ставки по аукциону");
require("../group_access.php");
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CAuction/CAuction.class.php"
);

use AGShop\Auction as Auction;


$objAuction = new \Auction\CAuction;

$arBets = $objAuction->removeBet(intval($_REQUEST["ID"]));
LocalRedirect($_REQUEST["BACK_URL"]);
die;

