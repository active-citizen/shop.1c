<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Аукционы");
require("../group_access.php");
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CAuction/CAuction.class.php"
);

use AGShop\Auction as Auction;


$objAuction = new \Auction\CAuction;

$arParams  = [];
$arAuctions = $objAuction->getAuctionProducts();

?>
<div class="auctions">
    <h1>Аукционы</h1>
    <? include("../menu.php"); ?>

<table class="table table-striped">
    <tr>
        <th>Поощрение</th>
        <th>Идёт</th>
        <th>Окончен</th>
        <th>Дата подведения итогов</th>
        <th>Ставки</th>
    </tr>
    <? foreach($arAuctions["result"] as $arAuction):?>
    <tr>
        <td>
            <?= $arAuction["PRODUCT_NAME"]?>
        </td>
        <td>
            <?= $arAuction["CURRENT"]?>
        </td>
        <td>
            <?= $arAuction["FINESHED"]?>
        </td>
        <td>
            <?= $arAuction["OFF_DATE"]?>
        </td>
        <td>
            <a href="/partners/auctions/result.php?OFFER_ID=<?= 
                $arAuction["OFFER_ID"]
            ?>&OFF_DATE=<?= $arAuction["OFF_DATE"]?>">Просмотреть
            </a>
        </td>
    </tr>
    <? endforeach?>
</table>
<div class="pages">
    Страницы: 
    <? foreach($arAuctions["pages"] as $nPageOffset=>$sPageTitle):?>
    <a href="?OFFSET=<?= $nPageOffset?>"><?= $sPageTitle ?></a>
    <? endforeach ?>
</div>
</div>

<style>
.auctions table{
    width: 95%;
}
.auctions table td{

    text-align: center;
}
</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
