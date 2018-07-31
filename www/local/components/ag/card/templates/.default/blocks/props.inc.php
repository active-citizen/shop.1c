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
//new XPrint($arResult["OFFERS_PROPS"]);
?>



<? foreach($arResult["OFFERS_PROPS"] as $sPropCode=>$arProp): ?>
  <div class="ag-shop-card__field">
    <div class="ag-shop-card__fieldname"><?= $arProp["name"]?>:</div>
    <div class="ag-shop-card__sizes">
      <? foreach($arProp['values'] as $nValId=>$arValue):?>
      <? $arPics = $arValue["pics"]; ?>
      <? 
        $arCross = [];$arOfferIds=[];$arStores = [];
        foreach($arValue["crossed"] as $nValIdCross=>$arCrossValue){
            $arCross[]= $nValIdCross;
            $arOfferIds[] = $arCrossValue["offerId"];
        }
        foreach($arValue["stores"] as $nStoreId=>$nAmount)
            $arStores[] = $nStoreId;
        $arCross = array_unique($arCross);
        $arOfferIds = array_unique($arOfferIds);
      ?>
      <label>
        <input type="radio" name="<?= $sPropCode ?>" value="<?= $nValId ?>"
            pics="<?= implode("|",$arPics)?>"
            rel="<?= $nValId?>"
            cross-values="<?= implode(",",$arCross)?>"
            offers="<?= implode(",",$arOfferIds)?>"
            stores="<?= implode(",",$arStores)?>"
            switched="off"
        >
        <? if(mb_strtolower(trim($arProp["name"]))=='цвет'):?>
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



