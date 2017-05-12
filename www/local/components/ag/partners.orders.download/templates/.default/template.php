<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="partners-orders-dowmload">
    <form name="form_filter" target="download_iframe"
    action="/partners/orders/download.ajax.php">
    <table class="table table-striped">
        <tr>
            <td>
                Телефон
            </td>
            <td>
                <input type="text" name="filter_phone" id="filter-phone" 
                class="form-control"
                value="<?= $arResult["FILTER"]["PHONE"]?>"
                >
            </td>
        </tr>
        <tr>
            <td>
                Имя покупателя
            </td>
            <td>
                <input type="text" name="filter_fio" id="filter-fio" 
                class="form-control"
                value="<?= $arResult["FILTER"]["FIO"]?>"
                >
            </td>
        </tr>
        <tr>
            <td>
                Статус
            </td>
            <td>
                <select name="filter_status" id="filter-status" class="form-control">
                    <option value="">-все-</option>
                    <? foreach($arResult["STATUSES"]as $arStatus):?>
                    <option value="<?= $arStatus["ID"]?>" style="color:
                    <?= $arStatus["COLOR"]?>"
                    <? if($arStatus["ID"]==$arResult["FILTER"]["STATUS"]):?>selected<? endif?>
                    ><?= $arStatus["NAME"]?></option>
                    <? endforeach?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                Производитель
            </td>
            <td>
                <select name="filter_man" class="form-control"
                id="filter-man"
                onchange="document.getElementById('form_filter').submit();"
                >
                    <option value="all">-все доступные мне производители-</option>
                    <? foreach($arResult["MANS"] as $arMan):?>
                        <option value="<?= $arMan["ID"]?>"
                            <? if($arResult["FILTER"]["MAN"]==$arMan["ID"]):?>
                            selected
                            <? endif?>
                        ><?= $arMan["NAME"]?></option>
                    <? endforeach ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                Дата добавления, c
            </td>
            <td>
                <div class="partners-date">
                <input type="text" name="filter_adddate_from"
                id="filter-adddate-from" 
                class="form-control" 
                value="<?= $arResult["FILTER"]["ADDDATE_FROM"]?>"
                >
                <?
                echo Calendar( 'filter_adddate_from', '','form_filter');
                ?></div>
            </td>
        </tr>
        <tr>
            <td>
                Дата добавления, до
            </td>
            <td>
                <div class="partners-date">
                <input type="text" name="filter_adddate_to"
                id="filter-adddate-to" 
                class="form-control" 
                value="<?= $arResult["FILTER"]["ADDDATE_TO"]?>"
                >
                <?
                echo Calendar( 'filter_adddate_to', '','form_filter');
                ?></div>
            </td>
        </tr>
        <tr>
            <td>
                Дата истечения бронирования, c
            </td>
            <td>
                <div class="partners-date">
                <input type="text" name="filter_lockdate_from"
                id="filter-lockdate-from" 
                class="form-control" 
                value="<?= $arResult["FILTER"]["LOCKDATE_FROM"]?>"
                >
                <?
                echo Calendar( 'filter_lockdate_from', '','form_filter');
                ?></div>
            </td>
        </tr>
        <tr>
            <td>
                Дата истечения бронирования, до
            </td>
            <td>
                <div class="partners-date">
                <input type="text" name="filter_lockdate_to"
                id="filter-lockdate-to" 
                class="form-control" 
                value="<?= $arResult["FILTER"]["LOCKDATE_TO"]?>"
                >
                <?
                echo Calendar( 'filter_lockdate_to', '','form_filter');
                ?></div>
            </td>
        </tr>
        <tr>
            <td>
                Дата изменения, c
            </td>
            <td>
                <div class="partners-date">
                <input type="text" name="filter_update_from"
                id="filter-update-from" 
                class="form-control" 
                value="<?= $arResult["FILTER"]["UPDATE_FROM"]?>"
                >
                <?
                echo Calendar( 'filter_update_from', '','form_filter');
                ?></div>
            </td>
        </tr>
        <tr>
            <td>
                Дата изменения, до
            </td>
            <td>
                <div class="partners-date">
                <input type="text" name="filter_update_to"
                id="filter-update-to" 
                class="form-control" 
                value="<?= $arResult["FILTER"]["UPDATE_TO"]?>"
                >
                <?
                echo Calendar( 'filter_update_to', '','form_filter');
                ?></div>
            </td>
        </tr>
        <tr>
            <td>
                Сортировка
            </td>
            <td>
                <select name="filter_sort" id="filter-sort" class="form-control">
                    <option value="order_id">Номер заказа</option>
                    <option value="customer">Покупатель</option>
                    <option value="email">Email</option>
                    <option value="status">Статус заказа</option>
                    <option value="date_added">Дата оформления заказа</option>
                    <option value="date_modified">Дата изменения заказа</option>
                    <option value="telephone">Телефон</option>
                    <option value="product_name">Наименование продукта</option>
                    <option value="storage_name">Центр выдачи</option>
                    <option value="expire_date">Дата истечения бронирования</option>
                    <option value="category_name">Категория</option>
                    <option value="manufacturer_name">Производитель</option>
                    <option value="price">Цена в баллах</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                Направление сортировки
            </td>
            <td>
                <select name="filter_sort" id="filter-sort" class="form-control">
                    <option value="ASC">По возрастанию</option>
                    <option value="DESC">По убыванию</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right;">
                <input type="submit" name="download" class="btn btn-primary"
                value="Выгрузить" onclick="this.style.display='none';">
            </td>
        </tr>
    </table>
    </form>
    <iframe name="download_iframe" id="download-iframe"></iframe>
</div>
