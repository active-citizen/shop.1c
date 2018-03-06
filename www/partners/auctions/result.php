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
                <th style="width:32px;">ID</th>
                <th style="width:50px;">Место в очереди</th>
                <th style="width: 120px;">Телефон</th>
                <th style="width: 250px;">ФИО</th>
                <th style="width: 50px;">Цена заявки</th>
                <th style="width:50px;">Количество в заявке</th>
                <th style="width: 100px;">Дата ставки</th>
                <th style="width: 100px;">Дата итога</th>
                <th style="width: 100px;">Дата исполнения</th>
                <? if(!$arParams["OFF_DATE"]):?>
                    <th style="width:50px;">Остаток к моменту подхода очереди</th>
                <? endif ?>
                <th style="width: 100px;">Статус текущий</th>
                <? if(!$arParams["OFF_DATE"]):?>
                    <th style="width: 100px;">Статус предлагаемый</th>
                <? endif ?>
                <th style="width:80px;">Номер заказа</th>
                <th>Комментарий</th>
                <? if(!$arParams["OFF_DATE"]):?>
                    <th style="width:50px;"></th>
                    <th style="width:50px;"></th>
                <? endif ?>
            </tr>
        <? $nNum=0;?>
        <? foreach($arStoreBets["BETS"] as $arBet):?>
        <? $nNum++?>
            <tr class="<? 
            if($arBet["AMOUNT"]>$arBet["ODD"] && $arBet["ODD"]):?>greed<? 
            elseif($arBet["AMOUNT"]<=$arBet["ODD"] && $arBet["TRADE_STATUS"]=='win'):?>win<? endif ?><?
            if($arBet["STATUS"]=='error'): ?> error<? endif?><?
            if($arBet["STATUS"]=='reject'): ?> reject<? endif?>">
                <td><?= $arBet["BET_ID"]?></td>
                <td><?= $nNum?></td>
                <td><?= $arBet["PHONE"]?></td>
                <td><?= $arBet["FIO"]?></td>
                <td><?= $arBet["PRICE"]?></td>
                <td><?= $arBet["AMOUNT"]?></td>
                <td><?= $arBet["CTIME"]?></td>
                <td><?= $arBet["OFF_DATE"]?></td>
                <td><?= $arBet["CLOSE_DATE"]?></td>
                <? if(!$arParams["OFF_DATE"]):?>
                    <td><?= $arBet["ODD"]?></td>
                <? endif ?>
                <td><?= $arStatuses[$arBet["STATUS"]]?></td>
                <? if(!$arParams["OFF_DATE"]):?>
                    <td><?= $arStatuses[$arBet["TRADE_STATUS"]]?><? if(
                        $arBet["AMOUNT"]>$arBet["ODD"] && $arBet["ODD"]
                    ):?>(жмот)<? endif ?></td>
                <? endif ?>
                <td>
                    <?if($arBet["ORDER_ID"]):?>
                    <a href="/partners/orders/<?= $arBet["ORDER_ID"]?>/"
                    target="_blank">
                    Ц-<?= $arBet["ORDER_ID"]?>
                    </a>
                    <? endif ?>
                </td>
                <td><?= $arBet["COMMENT"]?></td>
                <? if(!$arParams["OFF_DATE"]):?>
                    <td><a href="/partners/auctions/edit.php?ID=<?= $arBet["BET_ID"]
                    ?>&BACK_URL=<?= $_SERVER["REQUEST_URI"]?>">[править]</a></td>
                    <td><a href="/partners/auctions/delete.php?ID=<?= $arBet["BET_ID"]
                    ?>&BACK_URL=<?= $_SERVER["REQUEST_URI"]?>" 
                    onclick="return confirm('Точно удалить?');">[Удалить]</a></td>
                <? endif ?>
            </tr>
        <? endforeach ?>
            <tr>
                <td colspan="9"><b>Осталось нераспродано</b> : <b><?=
                $arBet["ODD"]?></b></td>
                <td></td>
            </tr>
        </table>
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
.reject{
    color: purple;
}
.status-win{
    color: green;
}
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
