<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<h1>Добавления партнёра или оператора</h1>
<? if($arResult["ERRORS"]):?>
    <? foreach($arResult["ERRORS"] as $sError):?>
    <? ShowMessage(array(
        "TYPE"=>"ERROR",
        "MESSAGE"=>$sError
    ))?>
    <? endforeach ?>
<? endif ?>
<form class="form-horizontal" method="POST">
    <table class="table">
        <tr>
            <th>
                Логин<sup>*</sup>
            </th>
            <td>
                <input type="text" class="form-control" name="LOGIN"
                value="<?= htmlspecialchars($_REQUEST["LOGIN"])?>"
                >
            </td>
        </tr>
        <tr>
            <th>
                Пароль<sup>*</sup>
            </th>
            <td>
                <input type="password" class="form-control" name="PASSWORD"
                value="<?= htmlspecialchars($_REQUEST["PASSWORD"])?>"
                >
            </td>
        </tr>
        <tr>
            <th>
                Пароль ещё раз<sup>*</sup>
            </th>
            <td>
                <input type="password" class="form-control" name="REPASSWORD"
                value="<?= htmlspecialchars($_REQUEST["REPASSWORD"])?>"
                >
            </td>
        </tr>
        <tr>
            <th>
                Email<sup>*</sup>
            </th>
            <td>
                <input type="text" class="form-control" name="EMAIL"
                value="<?= htmlspecialchars($_REQUEST["EMAIL"])?>"
                >
            </td>
        </tr>
        <tr>
            <th>
                Фамилия
            </th>
            <td>
                <input type="text" class="form-control" name="LAST_NAME"
                value="<?= htmlspecialchars($_REQUEST["LAST_NAME"])?>"
                >
            </td>
        </tr>
        <tr>
            <th>
               Имя и отчество 
            </th>
            <td>
                <input type="text" class="form-control" name="NAME"
                value="<?= htmlspecialchars($_REQUEST["NAME"])?>"
                >
            </td>
        </tr>
        <tr>
            <th>
               Тип пользователя<sup>*</sup>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="GROUPS_ID[<?= PARTNERS_GROUP_ID
                    ?>]"
                    <? if($_REQUEST["GROUPS_ID"][PARTNERS_GROUP_ID])
                        echo "checked";
                    ?>
                    > Партнёр
                </label>
                <br/>
                <label>
                    <input type="checkbox" name="GROUPS_ID[<?= OPERATORS_GROUP_ID
                    ?>]"
                    <? if($_REQUEST["GROUPS_ID"][OPERATORS_GROUP_ID])
                        echo "checked";
                    ?>
                    > Оператор МФЦ
                </label>
            </td>
        </tr>
        <tr>
            <th>
                Глобальные доступы
            </th>
            <td>
                <label>
                    <input type="checkbox" name="ALL_STORES"
                    <? if($_REQUEST["ALL_STORES"])
                        echo "checked";
                    ?>
                    > 
                    К заказам на всех складах
                </label>
                <br/>
                <label>
                    <input type="checkbox" name="ALL_MANS"
                    <? if($_REQUEST["ALL_MANS"])
                        echo "checked";
                    ?>
                    > 
                    К заказам всех производителей
                </label>
            </td>
        </tr>
        <tr>
            <th>
                Доступы к заказам следующих складов
            </th>
            <td>
                <select name="STORES[]" class="form-control" multiple="multiple"
                size="12" tabindex="0">
                <option value="0">-нет-</option>
                <? foreach($arResult["STORES"] as $arStore):?>
                    <option value="<?= $arStore["ID"]?>" <? 
                    if(in_array($arStore["ID"],$_REQUEST["STORES"]))
                        echo "selected";?>>
                        <?= $arStore["TITLE"]?>
                    </option>
                <? endforeach ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                Доступы к заказам следующих производителей
            </th>
            <td>
                <select name="MANS[]" class="form-control" multiple="multiple"
                size="12">
                <option value="0">-нет-</option>
                <? foreach($arResult["MANS"] as $arMan):?>
                    <option value="<?= $arMan["ID"]?>" <? 
                    if(in_array($arMan["ID"],$_REQUEST["MANS"]))
                        echo "selected";?>>
                        <?= $arMan["NAME"]?>
                    </option>
                <? endforeach ?>
                </select>
            </td>
        </tr>
       <tr>
            <td colspan="2" style="text-align:right">
                <input type="submit" name="ADD" class="btn btn-primary"
                value="Добавить">
                <input type="button" class="btn" value="Отмена"
                onclick="document.location.href='<?= $arParams["BACK_URL"]?>'">
            </td>
        </tr>
    </table>
</form>
