<? if($arResult["ERROR"]):?>
<div class="alert alert-danger">
    <?= $arResult["ERROR"]?>
</div>
<? else:?>
<div class="alert alert-info">При создании заказа баллы у пользователя не снимаются,
никакие интеграции с внешними сервисами не выполняются(тройка и парковка не
пополняются)</div>
<? endif ?>
<form class="form-horizontal" role="form" method="POST">
    <div class="form-group">
        <label class="col-sm-2 control-label">Телефон</label>
        <div class="col-sm-10">
        <input class="form-control" 
        placeholder="Введите телефон пользователя, например 79171189696" name="PHONE"
        value="<?= $arResult["ORDER"]["PHONE"]?>"
        >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Поощрение</label>
        <div class="col-sm-10">
        <input class="form-control"
        placeholder="Введите название поощрения(или часть названия)"
        name="OFFER_NAME"
        value="<?= $arResult["ORDER"]["OFFER_NAME"]?>"
        >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Склад</label>
        <div class="col-sm-10">
        <select class="form-control" name="STORE_ID">
            <option value="0">-Выберите склад--</option>
            <? foreach($arResult["FORM"]["STORES"] as $arStore):?>
            <option value="<?= $arStore["ID"]?>"<? 
                if($arStore["ID"]==$arResult["ORDER"]["STORE_ID"])echo " selected";
            ?>>
                <?= $arStore["TITLE"]?>
            </option>
            <? endforeach ?>
        </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">ЗНИ</label>
        <div class="col-sm-10">
        <div class="alert alert-warning">При создании заказа устанавливается
        статус <b>"В обработке"</b>, а параметр <b>ЗНИ</b> отправляется в 1С для
        установления реального статуса заказа</div>
        <select class="form-control" name="STATUS_ID">
            <option value="0">-Выберите ЗНИ--</option>
            <? foreach($arResult["FORM"]["STATUSES"] as $arStatus):?>
            <option value="<?= $arStatus["ID"]?>"<? 
                if($arStatus["ID"]==$arResult["ORDER"]["STATUS_ID"])echo " selected";
            ?>>
                <?= $arStatus["NAME"]?>
            </option>
            <? endforeach ?>
        </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Количество</label>
        <div class="col-sm-10">
        <input class="form-control" 
        placeholder="Введите количество заказываемых единиц поощрения" name="AMOUNT"
        value="<?= $arResult["ORDER"]["AMOUNT"]?>"
        >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Цена за единицу</label>
        <div class="col-sm-10">
        <input class="form-control" 
        placeholder="Если цена берётся из каталога - оставить пустым" 
        name="PRICE" 
        value="<?= $arResult["ORDER"]["PRICE"]?>"
        >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Дата добавления</label>
        <div class="col-sm-10">
        <input class="form-control" 
        placeholder="Если цена берётся из каталога - оставить пустым"
        name="DATE_ADD" 
        value="<?= $arResult["ORDER"]["DATE_ADD"]?>"
        >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Номер карты Тройка</label>
        <div class="col-sm-10">
        <input class="form-control" 
        placeholder="Введите номер карты Тройка" 
        name="TROYKA" 
        value="<?= $arResult["ORDER"]["TROYKA"]?>"
        >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Номер транзакции тройки</label>
        <div class="col-sm-10">
        <input class="form-control" 
        placeholder="Введите номер транзакции" 
        name="TROYKA_TRANSACT" 
        value="<?= $arResult["ORDER"]["TROYKA_TRANSACT"]?>"
        >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Номер транзакции парковки</label>
        <div class="col-sm-10">
        <input class="form-control" 
        placeholder="Введите номер транзакции" 
        name="PARKING_TRANSACT" 
        value="<?= $arResult["ORDER"]["PARKING_TRANSACT"]?>"
        >
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>

<script>
//$('input[name="DATE_ADD"]').datepicker({dateFormat:"dd.mm.yy"});
var availableTags = <?=
json_encode($arResult["FORM"]["OFFERS"],JSON_UNESCAPED_UNICODE)?>;
$('input[name="OFFER_NAME"]').autocomplete({
    source: availableTags
});
</script>
