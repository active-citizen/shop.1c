<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Запросы изменения статуса");
require("../group_access.php");
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CAuction/CAuction.class.php"
);

use AGShop\Auction as Auction;


$objAuction = new \Auction\CAuction;

$arParams["OFF_DATE"] = '';
if(
    !isset($_REQUEST["OFFER_ID"]) 
    || !$arParams["OFFER_ID"] = intval($_REQUEST["OFFER_ID"])
)
    $arParams["OFFER_ID"] = 0;
else
    $arParams["OFFER_ID"] = intval($_REQUEST["OFFER_ID"]);

$arBets = $objAuction->getAuctionBets(
    $arParams["OFFER_ID"],
    $arParams["OFF_DATE"]
);

?>
<div class="auction">
    <h1>Ставки аукциона</h1>
    <? include("../menu.php"); ?>
    <? foreach($arBets as $arBet)break;?>
    <h2><?= $arBet["PRODUCT"]["NAME"]?></h2>
    <? foreach($arBets as $nStoreId => $arStoreBets):?>  

        <h3><?= $arStoreBets["STORE"]["TITLE"]?> (<?=
        $arStoreBets["STORE"]["AMOUNT"]?>шт.)</h3>
        <table class="table">
            <tr>
                <th>Место в очереди</th>
                <th>Телефон</th>
                <th>ФИО</th>
                <th>Цена заявки</th>
                <th>Количество в заявке</th>
                <th>Дата</th>
                <th>Остаток к моменту подхода очереди</th>
                <th>Статус текущий</th>
                <th>Статус предлагаемый</th>
                <th>Номер заказа</th>
                <th
            </tr>
        <? $nNum=0;?>
        <? foreach($arStoreBets["BETS"] as $arBet):?>
        <? $nNum++?>
            <tr class="<? 
            if($arBet["AMOUNT"]>$arBet["ODD"] && $arBet["ODD"]):?>greed<? 
            elseif($arBet["AMOUNT"]<=$arBet["ODD"]):?>win<? endif ?>">
                <td><?= $nNum?></td>
                <td><?= $arBet["PHONE"]?></td>
                <td><?= $arBet["FIO"]?></td>
                <td><?= $arBet["PRICE"]?></td>
                <td><?= $arBet["AMOUNT"]?></td>
                <td><?= $arBet["CTIME"]?></td>
                <td><?= $arBet["ODD"]?></td>
                <td><?= $arBet["STATUS"]?></td>
                <td><?= $arBet["TRADE_STATUS"]?></td>
                <td><?= $arBet["ORDER_ID"]?></td>
            </tr>
        <? endforeach ?>
        </table>

    <? endforeach?>
</div>

<style>
.greed{
    background-color: #FFDDDD;
}
.win{
    background-color: #DDFFDD;
}
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
