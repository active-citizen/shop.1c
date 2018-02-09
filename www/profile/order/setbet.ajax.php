<?
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CAuction/CAuction.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CAGShop.class.php");
require(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CLock/CLock.class.php"
);
// Пользователь не может сделать более одного заказа в 10 секунд
$objLock = new \Lock\CLock("USERORDER", $USER->GetID(), 10);
if($objLock->isLocked()){echo json_encode(["no"]);die;}

use AGShop\Auction as Auction;

$objAuction = new \Auction\CAuction;

$arAnswer = ["errors"=>[]];
if(!$objAuction->pushBet(
    intval($_REQUEST["offer_id"])?intval($_REQUEST["offer_id"]):0,
    $USER->GetID()?$USER->GetID():0,
    intval($_REQUEST["price"])?intval($_REQUEST["price"]):0,
    intval($_REQUEST["store_id"])?intval($_REQUEST["store_id"]):0,
    intval($_REQUEST["amount"])?intval($_REQUEST["amount"]):0
)){
    $arAnswer["errors"] = $objAuction->getErrors();
}
else{
    $arAnswer["redirecturl"] = $_REQUEST["redirecturl"];
}
echo json_encode($arAnswer);

