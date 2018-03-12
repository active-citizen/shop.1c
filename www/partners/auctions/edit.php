<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Правка ставки по аукциону");
require("../group_access.php");
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CAuction/CAuction.class.php"
);
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCatalog/CCatalogStore.class.php"
);

use AGShop\Auction as Auction;
use AGShop\Catalog as Catalog;


$objAuction = new \Auction\CAuction;

if(isset($_REQUEST["save"])){
    $objAuction->editBet(
        intval($_REQUEST["ID"]),
        $_REQUEST["fields"]
    );
    LocalRedirect($_REQUEST["BACK_URL"]);
    die;
}

$objStore = new \Catalog\CCatalogStore;

$arBet = $objAuction->getBet(intval($_REQUEST["ID"]));
$arStatuses = $objAuction->getStatuses();
$arStores = $objStore->getAllActive();

?>
<h1>Редактирование ставки</h1>
<form method="POST">
    <table class="table">
        <tr>
            <td colspan="2">
                <h4>Состояние ставки</h4>
            </td>
        </tr>
        <tr>
            <td>
                Статус
            </td>
            <td>
                <select class="form-control" name="fields[STATUS]">
                <? foreach($arStatuses as $sStatusCode=>$sStatusName):?>
                    <option
                    value="<?= $sStatusCode?>"
                    <? if($sStatusCode==$arBet["STATUS"]):?>selected<? endif?>
                    >
                    <?= $sStatusName?>
                    </option>
                <? endforeach?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
               Комментарий
            </td>
            <td>
                <input type="text" class="form-control" value="<?= 
                htmlspecialchars($arBet["COMMENT"])?>" name="fields[COMMENT]"
                placeholder="Комментарий(до 128 символов)"
                maxlength="128"
                >
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4>Параметры ставки</h4>
            </td>
        </tr>
        <tr>
            <td>
               Цена
            </td>
            <td>
                <input type="text" class="form-control" value="<?= 
                htmlspecialchars($arBet["PRICE"])?>" name="fields[PRICE]">
            </td>
        </tr>
        <tr>
            <td>
               Количество
            </td>
            <td>
                <input type="text" class="form-control" value="<?= 
                htmlspecialchars($arBet["AMOUNT"])?>" name="fields[AMOUNT]">
            </td>
        </tr>
        <tr>
            <td>
                Склад
            </td>
            <td>
                <select class="form-control" name="fields[STORE_ID]">
                <? foreach($arStores as $arStore):?>
                    <option
                    value="<?= $arStore["ID"]?>"
                    <? if($arStore["ID"]==$arBet["STORE_ID"]):?>selected<? endif?>
                    >
                    <?= $arStore["TITLE"]?>
                    </option>
                <? endforeach?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4>Информация об аукционе</h4>
            </td>
        </tr>
        <tr>
            <td>
                Поощрение
            </td>
            <td>
                <a href="<?= $arBet["OFFER"]["PRODUCT"]["DETAIL_PAGE_URL"]?>"
                target="_blank"
                >
                <?= $arBet["OFFER"]["PRODUCT"]["NAME"] ?>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                Стартовая цена
            </td>
            <td>
                <?= $arBet["OFFER"]["PRODUCT_PROPERTIES"]["MAXIMUM_PRICE"] ?>
            </td>
        </tr>
        <tr>
            <td>
                Дата начала
            </td>
            <td>
                <?=
                    date("d.m.Y H:i:s",MakeTimeStamp(
                    $arBet["OFFER"]["PRODUCT_PROPERTIES"]["AUCTION_START_DATE"],
                    "YYYY-MM-DD HH:MI:SS"
                    ));
                ?>
            </td>
        </tr>
        <tr>
            <td>
                Дата окончания
            </td>
            <td>
                <?= 
                    date("d.m.Y H:i:s",MakeTimeStamp(
                    $arBet["OFFER"]["PRODUCT_PROPERTIES"]["AUCTION_STOP_DATE"], 
                    "YYYY-MM-DD HH:MI:SS"
                    ));
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right;">
                <input type="submit" name="save" value="Сохранить" 
                class="btn btn-primary">
            </td>
        </tr>
    </table>
</form>



