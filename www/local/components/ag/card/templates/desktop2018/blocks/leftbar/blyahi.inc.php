<!--@Бляхи@-->
<div class="product-label">
    <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["NEWPRODUCT"][0]["VALUE_ENUM"]=='да'):?>
    <div class="label-new">
      Новинка
    </div>
    <? endif ?>

    <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["SALELEADER"][0]["VALUE_ENUM"]=='да'):?>
    <div class="label-hot">
      Хит
    </div>
    <? endif ?>

    <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["SPECIALOFFER"][0]["VALUE_ENUM"]=='да'):?>
    <div class="label-stock">
      Акция
    </div>
    <? endif ?>
</div>

