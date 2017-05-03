<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<form name=form_filter"" id="form_filter">
<select name="filter_storage" class="form-control"
id="filter-storage"
onchange="document.getElementById('form_filter').submit();"
>
    <option name="">-все склады-</option>
    <option></option>
</select>
<select name="filter_manufacturer" class="form-control"
id="filter-manufacturer"
onchange="document.getElementById('form_filter').submit();"
>
    <option name="">-все производители-</option>
    <option></option>
</select>
<table class="table table-striped table-bordered">
    <tr>
        <th><input type="checkbox" name="selectall" id="selectall"></th>
        <th width="120px">
            Номер
            <input name="sort_num" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_num" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th>
            Имя покупателя
            <input name="sort_name" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_name" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th width="100px">
            Статус
            <input name="sort_status" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_status" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th width="120px">
            Добавлено
            <input name="sort_adddate" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_adddate" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th width="200px">
            Email покупателя
            <input name="sort_email" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_email" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th>
            Название товара
            <input name="sort_product" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_product" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th width="120px">
            Номер Тройки
            <input name="sort_troika" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_troika" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th width="150px">
            Телефон
            <input name="sort_phone" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_phone" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th width="250px">
            Категория
            <input name="sort_cat" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_cat" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th width="120px">
            Дата истечения бронирования
            <input name="sort_closedate" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_closedate" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        <th>Действие</th>
    </tr>
    <tr>
        <th>
        </th>
        <th>
            <input type="text" name="filter_num" id="filter-num" 
            class="form-control">
        </th>
        <th>
            <input type="text" name="filter_name" id="filter-name" 
            class="form-control">
        </th>
        <th>
            <select name="filter_status" id="filter-status" class="form-control">
                <option name="">-все-</option>
                <option name="F" style="background-color: #AFA">Новый</option>
            </select>
        </th>
        <th>
            <div class="partners-date">
            <input type="text" name="filter_adddate" id="filter-adddate" 
            class="form-control" >
            <?
            echo Calendar( 'filter_adddate', '','form_filter');
            ?></div>
        </th>
        <th>
            <input type="text" name="filter_email" id="filter-email" 
            class="form-control">
        </th>
        <th>
            <input type="text" name="filter_product" id="filter-product" 
            class="form-control">
        </th>
        <th>
            <input type="text" name="filter_troika" id="filter-troika" 
            class="form-control">
        </th>
        <th>
            <input type="text" name="filter_phone" id="filter-phone" 
            class="form-control">
        </th>
        <th>
            <select name="filter_cat" id="filter-cat" class="form-control">
                <option name="">-все-</option>
            </select>
        </th>
        <th>
            <div class="partners-date">
            <input type="text" name="filter_closedate" id="filter-closedate" 
            class="form-control" >
            <?
            echo Calendar( 'filter_closedate', '','form_filter');
            ?></div>
        </th>
        <th>
            <input type="submit" name="filter" id="filter" 
            class="btn btn-primary" value="Фильтровать">
        </th>
    </tr>
    <? foreach($arResult["ORDERS"] as $arOrder):?>
    <tr class="order-status-<?= $arOrder["STATUS_ID"]?>">
        <td>
            <input type="checkbox" name="chk[<?= $arOrder["ID"]?>]">
        </td>
        <td>
            <?= $arOrder["ADDITIONAL_INFO"]?>
        </td>
        <td>
            <?= $arOrder["USER_LAST_NAME"]?>
            <?= $arOrder["USER_NAME"]?>
        </td>
        <td>
            <?= $arOrder["STATUS_ID"]?>
        </td>
        <td>
            <?= $arOrder["DATE_INSERT"]?>
        </td>
        <td>
            <?= $arOrder["USER_EMAIL"]?>
        </td>
        <td>
        </td>
        <td>
        </td>
        <td>
            <?= $arOrder["USER_LOGIN"]?>
        </td>
        <td>
        </td>
        <td>
        </td>
        <td>
            <a href="/partners/order/<?= $arOrder["ID"]?>">
                Просмотр
            </a>
        </td>
    </tr>
    <? endforeach?>
</table>
<?
    echo  $arResult["resOrders"]->GetPageNavStringEx($navComponentObject,
    'Заказы', '', 'Y');
?>
</form>


