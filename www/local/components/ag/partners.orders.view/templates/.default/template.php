<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
    <? if(
        isset($arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"])
        &&
        trim($arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"])
    ):?>
    <p class="alert alert-warning">Отправлен запрос изменения статуса на &laquo;<span style="color:<?= 
        $arResult["STATUSES"][
            $arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]
        ]["COLOR"]
    ?>"><?= 
        $arResult["STATUSES"][
            $arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]
        ]["NAME"]
    ?></span>&raquo;</p>
    <? endif ?>

<ul class="nav nav-pills nav-stacked partners-order-menu">
    <li class="active">
        <a href="#" rel="order-detail">
            Детали заказа
        </a>
    </li>
    <li>
        <a href="#" rel="order-products">
            Товары
        </a>
    </li>
    <li>
        <a href="#" rel="order-history">
            История
        </a>
    </li>
</ul>
<div class="partners-order-main" id="order-detail">
    <table class="table table-striped" >
        <tr>
            <td class="field-name">
                № заказа:   
            </td><td>
                <?= $arResult["ORDER"]["ADDITIONAL_INFO"]?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                Клиент:     
            </td><td>
                <?= $arResult["ORDER"]["USER_LAST_NAME"]?>
                <?= $arResult["ORDER"]["USER_NAME"]?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                 E-Mail:     
            </td><td>
                <?= $arResult["ORDER"]["USER_EMAIL"]?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                 Телефон:    
            </td><td>
                <?= str_replace("u","",$arResult["ORDER"]["USER_LOGIN"])?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                 Итого:  
            </td><td>
                <?= 
                    $arResult["ORDER"]["PRICE"]
                ?>
                б.
            </td>
        </tr>
        <tr>
            <td class="field-name">
                Статус заказа:  
            </td><td style="color:<?= $arResult["STATUSES"][
                    $arResult["ORDER"]["STATUS_ID"]]["COLOR"]?>">
                <?= $arResult["STATUSES"][
                    $arResult["ORDER"]["STATUS_ID"]
                ]["NAME"]?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                 Дата добавления:    
            </td><td>
                <?= $arResult["ORDER"]["DATE_INSERT"]?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                 Дата изменения:     
            </td><td>
                <?= $arResult["ORDER"]["DATE_UPDATE"]?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                Cертификат во вложении к уведомлениям   
            </td><td>
                <? if($arResult["ORDER"]["PRODUCT"]['PROPERTY_SEND_CERT_VALUE']):?> 
                Да
                <? else: ?>
                Нет
                <? endif ?>
            </td>
        </tr>
    </table>
</div>
<div class="partners-order-main" id="order-products">
<? $nTotal = 0;?>
<div class="print-buttons">
<span class="glyphicon glyphicon-print"><a href="/partners/orders/print.php?print=act&order=<?= 
    $arResult["ORDER"]["ID"]?>" target="print">Акт</a></span>
<span class="glyphicon glyphicon-print"><a
href="/partners/orders/print.php?print=cancel&order=<?= 
    $arResult["ORDER"]["ID"]?>" target="print">Отказ</a></span>
<iframe src="" name="print" style="display:none;"></iframe>
</div>
<?foreach($arResult["ORDER"]["BASKET"] as $arBasket):?>
<table><tr><td style="width:450px;">
    <div class="product-image">
        <img src="<?= $arBasket["PRODUCT"]["IMAGE"]?>"
        class="img-thumbnail partners-product-photo" alt="Responsive image"> 
    </div>
</td><td style="vertical-align: top;">
    <table class="table table-striped">
        <tr>
            <td style="width:200px;">
                Товар
            </td>
            <td>
                <a href="/catalog/<?= 
                    $arBasket["SECTION"]["CODE"]?>/<?= 
                    $arBasket["PRODUCT"]["CODE"]?>/<? 
                ?>" target="_blank">
                <?= $arBasket["PRODUCT"]["NAME"]?>
                </a>
            </td>
        </tr>
        <tr>
            <td style="width:200px;">
                Категория
            </td>
            <td>
                <a href="/catalog/<?= 
                    $arBasket["SECTION"]["CODE"]?>/<? 
                ?>" target="_blank">
                <?= $arBasket["SECTION"]["NAME"]?>
                </a>
            </td>
        </tr>
        <tr>
            <td style="width:200px;">
                Получение
            </td>
            <td>
                <a href="/rules/stores/#<?= $arResult["ORDER"]["STORE_INFO"]["ID"]
                ?>" target="_blank">
                <?= $arResult["ORDER"]["STORE_INFO"]["TITLE"]?>
                </a>
            </td>
        </tr>
        <tr>
            <td style="width:200px;">
                Количество
            </td>
            <td>
                <?= $arBasket["BASKET_ITEM"]["QUANTITY"]?> &times; 
                <?= $arBasket["PRODUCT"]["PROPERTY_QUANT_VALUE"]?> 
            </td>
        </tr>
        <tr>
            <td style="width:200px;">
                Цена за единицу
            </td>
            <td>
                <?= intval($arBasket["BASKET_ITEM"]["PRICE"])?> б. 
             </td>
        </tr>
    </table>
</td></tr></table>
<? $nTotal+=$arBasket["BASKET_ITEM"]["QUANTITY"]*$arBasket["BASKET_ITEM"]["PRICE"]?>
<? endforeach ?>
<div class="total">
    Итого:
    <span class="total-sum">
    <?= intval($nTotal) ?> б.
    </span>
</div>
</div>
<div class="partners-order-main" id="order-history">
<form method="post">
<table class="table">
    <tr>
        <th style="width:100px;">
            Дата
        </th>
        <th style="width: 300px;">
            Автор
        </th>
        <th style="width:100px;">
            Тип события
        </th>
        <th style="width:100px;">
            Статус
        </th>
        <th style="width:100px;">
            Предыдущий статус
        </th>
    </tr>
    <? foreach($arResult["ORDER"]["HISTORY"] as $arItem):?>
    <tr>
        <td>
            <?=$arItem["DATE_CREATE"] ?>
        </td>
        <td>
            <?= $arItem["USER_INFO"]["LAST_NAME"]?>
            <?= $arItem["USER_INFO"]["NAME"]?>
        </td>
        <td>
            <?=
                isset($arResult["HISTORY_TYPES"][$arItem["TYPE"]])
                ?
                $arResult["HISTORY_TYPES"][$arItem["TYPE"]]
                :
                $arItem["TYPE"]
            ?>
        </td>
        <? if(isset($arItem["DATA"]["OLD_STATUS_ID"])):?>
            <td style="color: <?=
                $arResult["STATUSES"][$arItem["DATA"]["STATUS_ID"]]["COLOR"]
            ?>">
                <?= $arResult["STATUSES"][$arItem["DATA"]["STATUS_ID"]]["NAME"]?>
            </td>
            <td style="color: <?=
                $arResult["STATUSES"][$arItem["DATA"]["OLD_STATUS_ID"]]["COLOR"]
            ?>">
                <?= $arResult["STATUSES"][$arItem["DATA"]["OLD_STATUS_ID"]]["NAME"]?>
            </td>
        <? else:?>
            <td colspan="2">
            </td>
        <? endif ?>
    </tr>
    <? endforeach ?>
</table>
<? if($arResult["ORDER"]["STATUS_ID"]=='N'):?>
<select name="status_id" class="form-control" id="status_id">
    <option value="F">Выполнен</option>
</select>
<input type="submit" name="chansge_status" value="Запросить смену статуса"
class="btn btn-primary">
<? endif ?>
</form>
</div>


<script src="/local/assets/scripts/partners.js"></script>

