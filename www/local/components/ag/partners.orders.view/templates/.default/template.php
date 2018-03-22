<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
    <? if(
        isset($arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"])
        &&
        trim($arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"])
    ):?>
    <p class="<?= isset($_REQUEST["error"])?"alert alert-danger":"alert alert-warning" ?>">
        <? if(isset($_REQUEST["error"])):?>
            <b>ВНИМАНИЕ!!! </b>
        <? endif ?>
        Отправлен запрос изменения статуса на &laquo;<span style="color:<?= 
        $arResult["STATUSES"][
            $arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]
        ]["COLOR"]
    ?>"><?= getStatusAlias($arResult["STATUSES"][
            $arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]
        ]["NAME"])
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
    <? if($arResult["MAILS"]):?>
    <li>
        <a href="#" rel="order-mails">
            Письма
        </a>
    </li>
    <? endif ?>
</ul>
<? if($arResult["MAILS"]):?>
<div class="partners-order-main" id="order-mails" style="display:none">
    <table class="table table-striped mails">
        <tr>
            <th style="width:50px">
                Статус
            </th>
            <th>
                В очереди
            </th>
            <th>
                Отправлено
            </th>
            <th>
                Прочтено
            </th>
            <th>
                Действия
            </th>
        </tr>
        <? foreach($arResult["MAILS"] as $arMail):?>
        <?
            if(!$arMail["FILENAME"])continue;
            $tmp = explode("-",$arMail["FILENAME"]);
            $tmp2 = $tmp;
            for($i=0;$i<7;$i++)unset($tmp2[$i]);
            $sFolder = "/".$tmp[0]."-".$tmp[1]
                ."/".$tmp[0]."-".$tmp[1]."-".$tmp[2]
                ."/".preg_replace("#^(.*)\.eml$#", "$1",implode("-",$tmp2));
        ?>
        <tr>
            <td>
                <?if($arMail["DATE_RECEIVE"]):?>
                    <i class="glyphicon glyphicon-ok"
                    title="Прочитано"
                    ></i>
                <? elseif($arMail["DATE_SENT"]):?>
                    <i class="glyphicon glyphicon-send"
                    title="Отправлено"
                    ></i>
                <? elseif($arMail["DATE_CREATE"]):?>
                    <i class="glyphicon glyphicon-envelope" 
                    title="Готово к отправке"></i>
                <? endif ?>
            </td>
            <td>
                <?= $arMail["DATE_CREATE"]?>
            </td>
            <td>
                <?= $arMail["DATE_SENT"]?>
            </td>
            <td>
                <?= $arMail["DATE_RECEIVE"]?>
            </td>
            <td>
                <? if($arMail["DATE_SENT"]):?>
                [
                <a target="_blank"
                href="/partners/mails/request.frame.php?folder=<?= $sFolder ?>&request=<?= 
                $arMail["FILENAME"]
                ?>">
                    Просмотр
                </a>
                ]
                <? endif ?>
            </td>
        </tr>
        <? endforeach ?>
    </table>
</div>
<style>
table.mails td{
    text-align: center;
}
</style>
<? endif ?>
<div class="partners-order-main" id="order-detail">
    <table class="table table-striped" >
        <tr>
            <td class="field-name">
                № заказа:   
            </td><td>
                <?= $arResult["ORDER"]["ADDITIONAL_INFO"]?>
                <? if($USER->isAdmin()):?>
                <!--
                (
                    <a href="/partners/logs/?query=<?=
                    $arResult["ORDER"]["ADDITIONAL_INFO"]?>"
                    target="_blank"
                    >
                    Посмотреть в логах
                    </a>
                )
                -->
                <? endif?>
            </td>
        </tr>
        <? if(
            !in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
            &&
            !in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())

        ):?>
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
        <? endif ?>
        <tr>
            <td class="field-name">
                 Телефон:    
            </td><td>
                <?= str_replace("u","",$arResult["ORDER"]["USER_LOGIN"])?>
            </td>
        </tr>
        <? if($arResult["TROYKA_CARDS"]):?>
        <tr>
            <td>Прикреплённые номера троек</td>
            <td>
                <? foreach($arResult["TROYKA_CARDS"] as $arTroyka):?>
                <div class="troyka">
                <a href="?act=troyka_del&num=<?= $arTroyka["cardnum"]?>" 
                title="Отвязать" onclick="return confirm('Точно отвязать \n карту <?= $arTroyka["cardnum"]
                ?> \n от номера <?= str_replace("u","",$arResult["ORDER"]["USER_LOGIN"])?>?');">
                    <?= $arTroyka["cardnum"]?><i class="glyphicon glyphicon-remove"></i>
                </a>
                </div>
                <? endforeach ?>
            </td>
        </tr>
        <? endif ?>
        <tr>
            <td style="width:200px; color: red;">
                Количество
            </td>
            <td style="color: red;">
                <?= $arResult["ORDER"]["BASKET"][0]["BASKET_ITEM"]["QUANTITY"]?>
            </td>
        </tr>
        <tr>
            <td class="field-name">
                 Общая сумма:  
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
                <?= getStatusAlias($arResult["STATUSES"][
                    $arResult["ORDER"]["STATUS_ID"]
                ]["NAME"])?>
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
        <? if($arResult["ORDER"]["STATUS_ID"]=='F'):?>
        <tr>
            <td class="field-name">
                 Дата выполнения:
            </td><td>
                <?= date(
                    "d.m.Y H:i:s", 
                    MakeTimeStamp(
                        $arResult["ORDER"]["PROPERTIES"]["SHIPDATE"]["VALUE"],
                        "YYYY-MM-DD HH:MI:SS"
                    )
                );?>
            </td>
        </tr>
        <? endif ?>
        <? if($arResult["ORDER"]["PROPERTIES"]["CLOSE_DATE"]["VALUE"]):?>
        <tr>
            <td class="field-name">
                 Дата истечения бронирования:
            </td><td>
                <?= 
                    $DB->FormatDate(
                        $arResult["ORDER"]["PROPERTIES"]["CLOSE_DATE"]["VALUE"],
                        "YYYY-MM-DD",
                        "DD.MM.YYYY"
                    );
                ?>
            </td>
        </tr>
        <? endif ?>
        <tr>
            <td class="field-name">
                Cертификат во вложении к уведомлениям   
            </td><td>
                <?
                if($arResult["ORDER"]["BASKET"][0]["PRODUCT"]['PROPERTY_SEND_CERT_VALUE']=='да'):?> 
                Да
                <? else: ?>
                Нет
                <? endif ?>
            </td>
        </tr>
        <? if($arResult["ORDER"]["BASKET"][0]["PRODUCT"]['PROPERTY_SEND_CERT_VALUE']=='да'):?> 
        <? if(
            !in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
            &&
            !in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())

        ):?>
        <tr>
            <td class="field-name">
                Сертификат
            </td>
            <td>
                <span class="glyphicon glyphicon-eye-open">
                <a href="/profile/order/print.png.ajax.php?generate=1&act=get&id=<?=
                $arResult["ORDER"]["ID"]?>"
                target="_blank">Посмотреть</a> 
                </span>
                &#160;&#160;&#160;&#160;
                <span class="glyphicon glyphicon-download-alt">
                <a href="/profile/order/print.png.ajax.php?act=download&id=<?=
                $arResult["ORDER"]["ID"]?>"
                target="cert">Скачать</a> 
                </span>
                &#160;&#160;&#160;&#160;
                <span class="glyphicon glyphicon-print">
                <a href="/profile/order/print.png.ajax.php?act=print&id=<?=
                $arResult["ORDER"]["ID"]?>"
                target="cert">Печатать</a> &#160;
                </span>
                <iframe src="" name="cert" style="display:none;"></iframe>
            </td>
        </tr>
        <? endif ?>
        <? endif ?>
        <? if(
            isset($arResult["ORDER"]["PROPERTIES"]["TROIKA"]["VALUE"])
            &&
            $arResult["ORDER"]["PROPERTIES"]["TROIKA"]["VALUE"]
        ):?>
        <tr>
            <td class="field-name">
                Номер карты Тройки
            </td><td>
                <?= $arResult["ORDER"]["PROPERTIES"]["TROIKA"]["VALUE"] ?>
            </td>
        </tr>
        <? endif ?>
        <? if($arResult["ORDER"]["PROPERTIES"]["PROMOCODES"]["VALUE"]):?>
        <tr>
            <td class="field-name">
                Промокоды
            </td><td>
                <?= $arResult["ORDER"]["PROPERTIES"]["PROMOCODES"]["VALUE"] ?>
            </td>
        </tr>
        <? endif ?>
        <? if(
            isset($arResult["ORDER"]["PROPERTIES"]["TROIKA_TRANSACT_ID"]["VALUE"])
            &&
            $arResult["ORDER"]["PROPERTIES"]["TROIKA_TRANSACT_ID"]["VALUE"]
        ):?>
        <tr>
            <td class="field-name">
                ID транзакции в БМ
            </td><td>
                <?= $arResult["ORDER"]["PROPERTIES"]["TROIKA_TRANSACT_ID"]["VALUE"] ?>
            </td>
        </tr>
        <? endif ?>
        <? if(
            isset($arResult["ORDER"]["PROPERTIES"]["PARKING_TRANSACT_ID"]["VALUE"])
        ):?>
        <tr>
            <td class="field-name">
                ID транзакции в парковках
            </td><td>
                <?= $arResult["ORDER"]["PROPERTIES"]["PARKING_TRANSACT_ID"]["VALUE"] ?>
            </td>
        </tr>
        <? endif ?>
        <? if( isset($arResult["ERROR_CODE"])):?>
        <tr>
            <td class="field-name">
                Код ошибки
            </td><td>
                <?= $arResult["ERROR_CODE"] ?>
            </td>
        </tr>
        <? endif ?>
        <? if( isset($arResult["ERROR_DESC"])):?>
        <tr>
            <td class="field-name">
                Код описания ошибки
            </td><td>
                <?= $arResult["ERROR_DESC"] ?>
            </td>
        </tr>
        <? endif ?>
    </table>
        <? if(isset($arResult["ORDER"]["CURL_LOG"]) && (
            $USER->isAdmin()
            ||
            in_array(SHOP_ADMIN, $USER->GetUserGroupArray())
            )
        ):?>
            <div id="accordion">
                <h3>
                    Журнал обмена (доступен администраторам)
                </h3>
                <div>
                    <? foreach($arResult["ORDER"]["CURL_LOG"] as $arLog):?>
                    <table class="table">
                        <tr>
                            <td>Время</td>
                            <td><?= date("d.m.Y H:i:s",$arLog['ctime'])?></td>
                        </tr>
                        <tr>
                            <td>URL</td>
                            <td><?= $arLog['url']?></td>
                        </tr>
                        <tr>
                            <td>Запрос</td>
                            <td><pre><?= print_r(json_decode($arLog['post_data']),1)?></pre></td>
                        </tr>
                        <tr>
                            <td>Ответ</td>
                            <td><pre><?= print_r(json_decode($arLog['data']),1)?></pre></td>
                        </tr>
                    </table>
                    <? endforeach ?>
                </div>
            </div>
            <script>
              $( function() {
                $( "#accordion" ).accordion({
                    active: false,
                    heightStyle: "content",
                    collapsible: true
                });
              } );
            </script>
        <? endif ?>
</div>
<div class="partners-order-main" id="order-products">
<? $nTotal = 0;?>
<div class="print-buttons">
<span class="glyphicon glyphicon-print"><a href="/partners/orders/print.php?print=act&order=<?= 
    $arResult["ORDER"]["ID"]?>" target="print">Акт</a></span>
<!-- <span class="glyphicon glyphicon-print"><a
href="/partners/orders/print.php?print=cancel&order=<?= 
    $arResult["ORDER"]["ID"]?>" target="print">Отказ</a></span> -->
<iframe src="" name="print" style="display:none;"></iframe>
</div>
<?foreach($arResult["ORDER"]["BASKET"] as $arBasket):?>
    <div class="product-current-desc">
        Текущее описание (обновлено <?=
        $arBasket["PRODUCT"]["TIMESTAMP_X"]?>)
    </div>
<table><tr><td style="width:450px; vertical-align: top;">
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
                <!-- <a href="/catalog/<?= 
                    $arBasket["SECTION"]["CODE"]?>/<?= 
                    $arBasket["PRODUCT"]["CODE"]?>/<? 
                ?>" target="_blank"> -->
                <?= $arBasket["PRODUCT"]["NAME"]?>
                <!-- </a> -->
            </td>
        </tr>
        <tr>
            <td style="width:200px;">
                Единица измерения
            </td>
            <td>
                <?= $arBasket["PRODUCT"]["PROPERTY_QUANT_VALUE"]?> 
            </td>
        </tr>
        <tr>
            <td style="width:200px;">
                Категория
            </td>
            <td>
                <!-- <a href="/catalog/<?= 
                    $arBasket["SECTION"]["CODE"]?>/<? 
                ?>" target="_blank"> -->
                <?= $arBasket["SECTION"]["NAME"]?>
                <!-- </a> -->
            </td>
        </tr>
        <tr>
            <td style="width:200px;">
                Получение
            </td>
            <td>
                <!-- <a href="/rules/stores/#<?= $arResult["ORDER"]["STORE_INFO"]["ID"]
                ?>" target="_blank"> -->
                <?= $arResult["ORDER"]["STORE_INFO"]["TITLE"]?>
                <!-- </a> -->
            </td>
        </tr>
        <tr>
            <td style="width:200px; color: red;">
                Количество
            </td>
            <td style="color: red;">
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
        <tr>
            <td style="width:200px;" colspan="2">
            <?= 
                $arBasket["PRODUCT"]["DETAIL_TEXT"]
            ?>
            <? if($arBasket["PRODUCT"]["PROPERTY_RECEIVE_RULES_VALUE"]["TEXT"]):?>
            <h3>Правила получения</h3>
            <?= $arBasket["PRODUCT"]["PROPERTY_RECEIVE_RULES_VALUE"]["TEXT"] ?>
            <? endif ?>
            <? if($arBasket["PRODUCT"]["PROPERTY_CANCEL_RULES_VALUE"]["TEXT"]):?>
            <h3>Правила получения</h3>
            <?= $arBasket["PRODUCT"]["PROPERTY_CANCEL_RULES_VALUE"]["TEXT"] ?>
            <? endif ?>
            </td>
        </tr>
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
        <? if(
            !in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
            &&
            !in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())

        ):?>
            <?= $arItem["USER_INFO"]["LAST_NAME"]?>
            <?= $arItem["USER_INFO"]["NAME"]?>
        <? endif ?>
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
                <?=
                getStatusAlias($arResult["STATUSES"][$arItem["DATA"]["STATUS_ID"]]["NAME"])?>
            </td>
            <td style="color: <?=
                $arResult["STATUSES"][$arItem["DATA"]["OLD_STATUS_ID"]]["COLOR"]
            ?>">
                <?= 
                    getStatusAlias(
                    $arResult["STATUSES"][$arItem["DATA"]["OLD_STATUS_ID"]]["NAME"]
                    )
                ?>
            </td>
        <? else:?>
            <td colspan="2">
            </td>
        <? endif ?>
    </tr>
    <? endforeach ?>
</table>

<? if($USER->IsAdmin()):?>
    <? if(
        $arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]==''
    ):?>
    <select name="status_id" class="form-control" id="status_id">
        <option value="F"><?= getStatusAlias('Выполнен')?></option>
        <option value="AI"><?= getStatusAlias('Аннулирован')?></option>
        <option value="N"><?= getStatusAlias('В работе')?></option>
        <option value="AF"><?= getStatusAlias('Отклонён')?></option>
        <option value="AC"><?= getStatusAlias('Брак')?></option>
        
    </select>    
    <input type="submit" name="chansge_status" value="Запросить смену статуса"
    class="btn btn-primary">
    <? endif ?>
<? else:?>
    <? if(
        $arResult["ORDER"]["STATUS_ID"]=='N'
        &&
        $arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]==''
    ):?>
    <select name="status_id" class="form-control" id="status_id">
        <option value="F"><?= getStatusAlias('Выполнен')?></option>
    </select>
    <input type="submit" name="chansge_status" value="Запросить смену статуса"
    class="btn btn-primary">
    <? endif ?>

    <? if(
        $arResult["ORDER"]["STATUS_ID"]=='F' 
        &&
        isset($arResult["ORDER"]["CURL_LOG"])
        &&
        $arResult["ORDER"]["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]==''
    ):?>
    <select name="status_id" class="form-control" id="status_id">
        <option value="AG"><?= getStatusAlias('Отменён')?></option>
    </select>
    <input type="submit" name="chansge_status" value="Запросить смену статуса"
    class="btn btn-primary">
    <? endif ?>
<? endif ?>
</form>
</div>


<script src="/local/assets/scripts/partners.js"></script>

