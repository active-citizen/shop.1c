<? if($arResult["AUCTION_WINNERS"]):?>
    <h4>Победители последнего аукциона</h4>
    <? foreach($arResult["AUCTION_WINNERS"] as $nStoreId=>$arStoreInfo):?>
    <h5><?= $arStoreInfo["STORE"]["TITLE"]?></h5>
    <table class="auction-winners">
        <tr>
            <th style="width: 100px;">Дата ставки</th>
            <th>Пользователь</th>
            <th>Цена</th>
            <th>Кол-во</th>
        </tr>
        <? foreach($arStoreInfo["BETS"] as $nBetId=>$arBet):?>    
        <tr <? if($arBet["USER_ID"]==$arResult["USER_INFO"]["ID"]):?>
        class="winner"<? endif ?>>
            <td><?= $arBet["CTIME"]?></td>
            <td class="auction-user">
            <?= $arBet["USER_HASH"]?>
            <? if($arBet["USER_ID"]==$arResult["USER_INFO"]["ID"]):?>
                (моя ставка)
            <? endif ?>
            </td>
            <td class="auction-price">
                <?= $arBet["PRICE"]?>
            </td>
            <td class="auction-amount">
                <?= $arBet["AMOUNT"]?>
            </td>
        </tr>
        <? endforeach ?>
    </table>
    <? endforeach?>
    <?
        /*
        echo "<pre>";
        print_r($arResult["AUCTION_WINNERS"]);
        echo "</pre>";
        */
    ?>
<? endif ?>

