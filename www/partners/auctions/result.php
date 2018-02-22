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

if(
    !isset($_REQUEST["OFFER_ID"]) 
    || !$arParams["OFFER_ID"] = intval($_REQUEST["OFFER_ID"])
)
    $arParams["OFFER_ID"] = 0;
else
    $arParams["OFFER_ID"] = intval($_REQUEST["OFFER_ID"]);
if(
    isset($_REQUEST["OFF_DATE"])
    &&
    trim($_REQUEST["OFF_DATE"])
){
    $arParams["OFF_DATE"] = date("Y-m-d H:i:s",MakeTimeStamp(
        $_REQUEST["OFF_DATE"],"DD.MM.YYYY HH:MI:SS"
    ));
}

$arBets = $objAuction->getAuctionBets(
    $arParams["OFFER_ID"],
    $arParams["OFF_DATE"]
);

$arIsAuction = $objAuction->isAuctionOffer(
    $arParams["OFFER_ID"]
);

$arStatuses = $objAuction->getStatuses();

if(
    isset($_REQUEST["commit"]) 
    && isset($_REQUEST["OFFER_ID"])
    && $nOfferId = intval($_REQUEST["OFFER_ID"])
){

    $sDate = $objAuction->makeResult($nOfferId);
    LocalRedirect("/partners/auctions/result.php?OFFER_ID="
        .$nOfferId."&OFF_DATE=".$sDate);
    die;
}

?>
<div class="auction">
    <h1>Ставки аукциона</h1>
    <? include("../menu.php"); ?>
    <? foreach($arBets as $arBet)break;?>
    <h2><?= $arBet["PRODUCT"]["NAME"]?></h2>
    <? foreach($arBets as $nStoreId => $arStoreBets):?>  

        <h3><?= $arStoreBets["STORE"]["TITLE"]?> (<?=
        $arStoreBets["STORE"]["AMOUNT"]?>шт.)</h3>
        <form method="post">
        <input type="hidden" name="store_id" value="<?=
        $arStoreBets["STORE"]["ID"]?>"/>
        <table class="table">
            <tr>
                <th>Место в очереди</th>
                <th>Телефон</th>
                <th>ФИО</th>
                <th>Цена заявки</th>
                <th>Количество в заявке</th>
                <th>Дата ставки</th>
                <th>Дата итога</th>
                <th>Дата исполнения</th>
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
            elseif($arBet["AMOUNT"]<=$arBet["ODD"]):?>win<? endif ?><?
            if($arBet["STATUS"]=='error'): ?> error<? endif?>">
                <td><?= $nNum?></td>
                <td><?= $arBet["PHONE"]?></td>
                <td><?= $arBet["FIO"]?></td>
                <td><?= $arBet["PRICE"]?></td>
                <td><?= $arBet["AMOUNT"]?></td>
                <td><?= $arBet["CTIME"]?></td>
                <td><?= $arBet["OFF_DATE"]?></td>
                <td><?= $arBet["CLOSE_DATE"]?></td>
                <td><?= $arBet["ODD"]?></td>
                <td><?= $arStatuses[$arBet["STATUS"]]?></td>
                <td><?= $arStatuses[$arBet["TRADE_STATUS"]]?></td>
                <td><?= $arBet["ORDER_ID"]?></td>
            </tr>
        <? endforeach ?>
        </table>
        <? foreach($arBets["BETS"] as $arBet)break;?>
        <? if(
            array_key_exists("OFF_DATE",$arBet)
            && !$arBet["OFF_DATE"]
        ):?>
            <? if($arIsAuction["IS_FINISHED"]):?>
            <? endif?>
        <? endif ?>
            <input type="submit" name="commit" 
            value="Раздать слонов. (сформировать заказы победителям и вернуть баллы проигравшим)"/>
        </form>

    <? endforeach?>
</div>

<style>
.greed{
    background-color: #FFDDDD;
}
.win{
    background-color: #DDFFDD;
}
.error{
    color: red;
}
.status-win{
    color: green;
}
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
