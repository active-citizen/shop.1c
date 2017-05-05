<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
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
                    $arResult["ORDER"]["BASKET"]["PRICE"]
                    *
                    $arResult["ORDER"]["BASKET"]["QUANTITY"]
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
adasdasdasdasd
asdadsasdasdasd
asdasdasdasda

</div>
<div class="partners-order-main" id="order-history">
<pre>
<?
    print_r($arResult["ORDER"]["HISTORY"]);
?>
</pre>
</div>


<script src="/local/assets/scripts/partners.js"></script>

