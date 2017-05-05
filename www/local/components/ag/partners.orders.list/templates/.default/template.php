<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<form name=form_filter"" id="form_filter">
<select name="filter_storage" class="form-control"
id="filter-storage"
onchange="document.getElementById('form_filter').submit();"
>
    <? if(count($arResult["STORES"])):?>
        <option value="all">-все доступные мне склады-</option>
    <? endif ?>
    <? foreach($arResult["STORES"] as $arStore):?>
        <option value="<?= $arStore["ID"]?>"
            <? if($arResult["FILTER"]["STORE"]==$arStore["ID"]):?>
            selected
            <? endif?>
        ><?= $arStore["TITLE"]?></option>
    <? endforeach ?>
</select>
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
<table class="table table-bordered">
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
        <th width="80px">
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
        <!--
        <th width="120px">
            Номер Тройки
            <input name="sort_troika" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_troika" type="submit" value="&#9660;" 
            class="partners-sort-down">
        </th>
        -->
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
            <!--
            <input name="sort_closedate" type="submit" value="&#9650;" 
            class="partners-sort-up">
            <input name="sort_closedate" type="submit" value="&#9660;" 
            class="partners-sort-down">
            -->
        </th>
        <th>Действие</th>
    </tr>
    <tr>
        <td>
        </td>
        <td>
            <input type="text" name="filter_num" id="filter-num" 
            class="form-control"
            value="<?= $arResult["FILTER"]["NUM"]?>"
            >
        </td>
        <td>
            <input type="text" name="filter_name" id="filter-name" 
            class="form-control"
            value="<?= $arResult["FILTER"]["LAST_NAME"]?>"
            >
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
        <td>
            <div class="partners-date">
            <input type="text" name="filter_adddate" id="filter-adddate" 
            class="form-control" 
            value="<?= $arResult["FILTER"]["ADDDATE"]?>"
            >
            <?
            echo Calendar( 'filter_adddate', '','form_filter');
            ?></div>
        </td>
        <td>
            <input type="text" name="filter_email" id="filter-email" 
            class="form-control"
            value="<?= $arResult["FILTER"]["EMAIL"]?>"
            >
        </td>
        <td>
            
            <input type="text" name="filter_product" id="filter-product" 
            class="form-control"
            value="<?= $arResult["FILTER"]["PRODUCT"]?>"
            >
            
        </td>
        <!--
        <td>
            <input type="text" name="filter_troika" id="filter-troika" 
            class="form-control">
        </td>
        -->
        <td>
            <input type="text" name="filter_phone" id="filter-phone" 
            class="form-control"
            value="<?= $arResult["FILTER"]["PNONE"]?>"
            >
        </td>
        <td>
            
            <select name="filter_cat" id="filter-cat" class="form-control">
                <option value="">-все-</option>
                <? foreach($arResult["SECTIONS"] as $arSection):?>
                <option value="<?= $arSection["ID"]?>"
                <? if($arSection["ID"]==$arResult["FILTER"]["SECTION"]):?>
                selected
                <? endif?>
                >
                    <?= $arSection["NAME"]?>
                </option>
                <? endforeach ?>
            </select>
        </td>
        <td>
            <div class="partners-date">
            <input type="text" name="filter_closedate" id="filter-closedate" 
            class="form-control" 
            value="<?= $arResult["FILTER"]["CLOSE_DATE"]?>"
            >
            <?
            echo Calendar( 'filter_closedate', '','form_filter');
            ?></div>
        </td>
        <td>
            <input type="submit" name="filter" id="filter" 
            class="btn btn-primary" value="Фильтровать">
        </td>
    </tr>
    <? foreach($arResult["ORDERS"] as $arOrder):?>
    <tr style="color:<?= $arResult["STATUSES"][$arOrder["STATUS_ID"]]["COLOR"]?>">
        <td class="td-checkbox">
            <input type="checkbox" name="chk[<?= $arOrder["ID"]?>]">
        </td>
        <td class="td-num">
            <?= $arOrder["ADDITIONAL_INFO"]?>
        </td>
        <td class="td-fio">
            <?= $arOrder["PROPERTIES"]["NAME_LAST_NAME"]["VALUE"]?>
        </td>
        <td class="td-status">
            <?= $arResult["STATUSES"][$arOrder["STATUS_ID"]]["NAME"]?><?
            if($arOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]):?><br/>
            &#8595;<br/>
            <?= $arOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]?>
            <? endif ?>
        </td>
        <td class="td-date">
            <?= $arOrder["DATE_INSERT"]?>
        </td>
        <td class="td-email">
                <?= $arOrder["USER_EMAIL"]?>
        </td>
        <td class="td-product">
            <a href="<?= $arOrder["PROPERTIES"]["PRODUCT_URL"]["VALUE"]?>" 
            target="_blank">
                <?= $arOrder["PROPERTIES"]["PRODUCT_NAME"]["VALUE"]?>
            </a>
        </td>
        <!--
        <td>
            <?= $arOrder["PROPERTIES"]["TROIKA"]["VALUE"]?>
        </td>
        -->
        <td class="td-phone">
            <?= str_replace("u","",$arOrder["USER_LOGIN"])?>
        </td>
        <td class="td-section">
            <a href="<?= $arOrder["PROPERTIES"]["SECTION_URL"]["VALUE"]?>" 
            target="_blank">
                <?= $arOrder["PROPERTIES"]["SECTION_NAME"]["VALUE"]?>
            </a>
        </td>
        <td class="td-date">
            <?= 
                preg_match(
                    "#^(\d+)\-(\d+)\-(\d+)$#",
                    $arOrder["PROPERTIES"]["CLOSE_DATE"]["VALUE"],
                    $m
                )
                ?
                $m[3].".".$m[2].".".$m[1]
                :
                ""
            ?>
        </td>
        <td class="td-action">
            [<a href="/partners/orders/<?= $arOrder["ID"]?>/">
                Просмотр
            </a>]
        </td>
    </tr>
    <? endforeach?>
</table>
<?
    echo  $arResult["resOrders"]->GetPageNavStringEx($navComponentObject,
    'Заказы', '', 'Y');
?>
</form>


