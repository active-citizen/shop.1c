<?
require_once($_SERVER["DOCUMENT_ROOT"].
            "/bitrix/modules/main/include/prolog_before.php");

require("../group_access.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegrationInfotech.class.php");

use AGShop\Integration as Integration;

$objInfotech = new \Integration\CIntegrationInfotech;

if($nEventId = intval($_REQUEST["event_id"]))
    $arSeats = $objInfotech->getSeats($nEventId);

if(!$arSeats){
    new XPrint($objInfotech->getErrors());
    die;
}


?><!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="HandheldFriendly" content="True"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="/local/assets/bootstrap/css/bootstrap.min.css" type="text/css"  data-template-style="true"  rel="stylesheet"/>
    <link href="/local/assets/bootstrap/css/bootstrap-theme.min.css" type="text/css"  data-template-style="true"  rel="stylesheet"/>
    <link href="/local/assets/styles/infotech.css" type="text/css"  data-template-style="true"  rel="stylesheet"/>
  </head>

<body>
    
<div class="partners-main">
    <h1>Места</h1>
    <ol class="actions">
        <? foreach($arSeats as $arSeat):?>
        <li class="action">
            <a href="#" onclick="return false;" class="info-picker"><?= $arSeat["seatId"]?>
            </a><i class="info-block-picker glyphicon glyphicon-chevron-down"></i>
            <div class="info-block"><? 
            new XPrint($arSeat);
            ?></div>
        </li>
        <? endforeach?>
    </ol>
</div>

    <script type="text/javascript"  src="/local/assets/libs/jquery.min.js"></script>
    <script type="text/javascript"  src="/local/assets/scripts/infotech.js"></script>
</body>
</html>


