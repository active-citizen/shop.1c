<?/*
<? foreach($arResult["PROP1C"] as $code1c=>$props): ?>
  <? if(!$props["VALUES"])continue;?>
  <div class="ag-shop-card__field">
    <div class="ag-shop-card__fieldname"><?= $props["NAME"]?>:</div>
    <div class="ag-shop-card__sizes">
      <? foreach($props["VALUES"] as $id=>$value):?>
      <label>
        <input type="radio" name="<?= $code1c?>" <?
        if($id==$arResult["OFFERS"][0]["PROPERTIES"][$code1c][0]["VALUE"])echo "checked";
        ?> value="<?= $id?>">
        <div class="ag-shop-card__sizes-item"><?= $value?></div>
      </label>
      <? endforeach ?>
    </div>
  </div>
<? endforeach ?>
*/?>

<?
//    new XPrint($arResult["OFFERS_JSON"]);
?>
<? foreach($arResult["OFFER_PARAMETERS"] as $sPropCode0=>$arProp0): ?>
  <div class="ag-shop-card__field">
    <div class="ag-shop-card__fieldname"><?= $arProp0['info']["name"]?>:</div>
    <div class="ag-shop-card__sizes">
      <? foreach($arProp0['items'] as $nValId=>$arValue):?>
      <? $arPics = $arResult["OFFERS_JSON"][$arValue["offerId"]]["PICS"]; ?>
      <label>
        <input type="radio" name="<?= $sPropCode0 ?>" value="<?=
        !$arValue['childs']?$nValueId:'' ?>"
            pics="<?= implode("|",$arPics)?>"
            rel="<?= $nValId?>"
        >
        <? if(mb_strtolower(trim($arProp0['info']["name"]))=='цвет'):?>
            <div class="ag-shop-card__colors-item"
            style="background-image:url(<?= 
                $arPics[0]
            ?>)"
            title="<?= $arValue['value']?>"
            ><?= $arValue['value']?></div>
        <?else:?>
            <div class="ag-shop-card__sizes-item"><?= $arValue['value']?></div>
        <? endif ?>
      </label>
      <? endforeach ?>
    </div>
  </div>
<? endforeach ?>


<? foreach($arResult["OFFER_PARAMETERS"] as $sPropCode0=>$arProp0): ?>
<? foreach($arProp0['items'] as $nItemId => $arItem):?>
<? foreach($arItem["childs"] as $arPropCode1=>$arProp1):?>
  <div class="ag-shop-card__field ag-param-secondfield" style="display:none;" parent="<?= $nItemId ?>">
    <div class="ag-shop-card__fieldname"><?= $arProp1['info']["name"]?>:</div>
    <div class="ag-shop-card__sizes">
      <? foreach($arProp1['items'] as $nValId=>$arValue):?>
      <label>
        <input type="radio" name="<?= $sPropCode1 ?>" value="<?=
        !$arValue['childs']?$nValueId:'' ?>">
        <? if(mb_strtolower(trim($arProp1['info']["name"]))=='цвет'):?>
            <?
                $arPics = $arResult["OFFERS_JSON"][$arValue["offerId"]]["PICS"];
            ?>
            <div class="ag-shop-card__colors-item"
            style="background-image:url(<?= 
                $arPics[0]
            ?>)"
            title="<?= $arValue['value']?>"
            pics="<?= implode("|",$arPics)?>"
            ><?= $arValue['value']?></div>
        <?else:?>
            <div class="ag-shop-card__sizes-item"><?= $arValue['value']?></div>
        <? endif ?>
      </label>
      <? endforeach ?>
    </div>
  </div>
<? endforeach ?>
<? endforeach ?>
<? endforeach ?>

