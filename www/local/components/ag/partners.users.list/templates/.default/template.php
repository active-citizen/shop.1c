<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<h1>Партнёры и операторы МФЦ</h1>
<table class="table table-bordered">
    <tr>
        <th style="width: 100px">
            Логин
        </th>
        <th>
            Ф.И.О.
        </th>
        <th style="width:100px;">
            Тип
        </th>
        <th>
            Производители
        </th>
        <th>
            Склады
        </th>
        <th style="width:50px;">
            Действие
        </th>
    </tr>
    <tr>
        <form>
        <td>
            <input type="text" class="form-control" name="FILTER[LOGIN]"
            value="<?= $arParams["FILTER"]["LOGIN"]?>">
        </td>
        <td>
            <input type="text" class="form-control" name="FILTER[SURNAME]"
            value="<?= $arParams["FILTER"]["SURNAME"]?>" placeholder="Фамилия"
            style="width: 200px;float:left;"
            >
            <input type="text" class="form-control" name="FILTER[NAME]"
            value="<?= $arParams["FILTER"]["NAME"]?>" placeholder="Имя"
            style="width: 200px;float:left;"
            >
        </td>
        <td>
            <select name="FILTER[TYPE]" class="form-control">
                <option value="0">-все-</option>
                <option value="<?= PARTNERS_GROUP_ID ?>"
                <? if($arParams["FILTER"]["TYPE"]==PARTNERS_GROUP_ID):
                ?>selected<? endif ?>>Партнёры</option>
                <option value="<?= OPERATORS_GROUP_ID ?>"
                <? if($arParams["FILTER"]["TYPE"]==OPERATORS_GROUP_ID):
                ?>selected<? endif ?>
                >Операторы МФЦ</option>
            </select>
        </td>
        <td>
            <select name="FILTER[MAN_ID]" class="form-control">
                <option value="0">-все-</option>
                <? foreach($arResult["MANS"] as $arMan):?>
                <option value="<?= $arMan["ID"]?>"
                <? if($arMan["ID"]==$arParams["FILTER"]["MAN_ID"]):
                ?>selected<? endif ?>>
                    <?= $arMan["NAME"]?>
                </option>
                <? endforeach ?> 
            </select>
        </td>
        <td>
            <select name="FILTER[STORES]" class="form-control">
                <option value="0">-все-</option>
                <? foreach($arResult["STORES"] as $arStore):?>
                <option value="<?= $arStore["ID"]?>"
                <? if($arStore["ID"]==$arParams["FILTER"]["STORES"]):
                ?>selected<? endif ?>>
                    <?= $arStore["TITLE"]?>
                </option>
                <? endforeach ?> 
             </select>
        </td>
        <td>
            <input type="submit" name="FILTER[GO]" value="Фильтровать"
            class="btn btn-success">
        </td>
        </form>
    </tr>
    <? foreach($arResult["USERS"] as $arUser):?>
    <tr>
        <td><?= $arUser["LOGIN"]?></td>
        <td class="td-fio"><?= $arUser["LAST_NAME"]?> <?= $arUser["NAME"]?></td>
        <td>
            <? if($arUser["GROUPS"]["PARTNER"]):?>
            <div class="partner">
                Партнёр
            </div>
            <? endif ?>
            <? if($arUser["GROUPS"]["OPERATOR"]):?>
            <div class="operator">
                Оператор
            </div>
            <? endif ?>
        </td>
        <td class="td-product">
            <? if($arUser["UF_USER_MAN_ALL"]):?>
                Все
            <? else: ?>
                <? foreach($arUser["UF_USER_MAN_ID"] as $nManId):?>
                <div class="manufacturer">
                    <?= $arResult["MANS"][$nManId]["NAME"]?>
                    (ID=<?= $arResult["MANS"][$nManId]["ID"]?>)
                </div>
                <? endforeach ?>
            <? endif ?>
        </td>
        <td class="td-product">
            <? if($arUser["UF_USER_STORAGE_ALL"]):?>
                Все
            <? else: ?>
                <? foreach($arUser["UF_USER_STORAGE_ID"] as $nStoreId):?>
                <div class="storage">
                    <?= $arResult["STORES"][$nStoreId]["TITLE"]?>
                </div>
                <? endforeach ?>
            <? endif ?>
        </td>
        <td class="td-action">
            [<a href="<? $arParams["BASE_URL"]?>edit/?ID=<?= $arUser["ID"]?>">
                Править
            </a>]
        </td>
    </tr>
    <? endforeach ?>
</table>
<div class="pagination"><?
        $arResult["resUser"]->NavStart();
        $arResult["resUser"]->NavPrint("Страницы",false,"text");
?></div>



