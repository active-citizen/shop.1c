<? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]):?>
<div class="ag-shop-card__warning">
    <i class="ag-shop-icon ag-shop-icon--attention"></i><span>Срок действия вашего заказа <?= 
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]
        ?> <?= 
        get_days($arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]);
        ?> с момента оформления.</span>
</div>
<a name="setbet"></a>
<? endif ?>

