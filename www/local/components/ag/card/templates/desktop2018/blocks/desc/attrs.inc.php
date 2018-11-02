 <? foreach($arResult["OFFERS_PROPS"] as $sPropCode=>$arProp): ?>
  <div class="row products-attrs">
    <div class="col-2">
        <p class="product-attr"><?= $arProp["name"]?>:</p>
    </div>
    <div class="col-8">
      <div class="product-img-wrap">
      <? foreach($arProp['values'] as $nValId=>$arValue):?>
          <? $arPics = $arValue["pics"]; ?>
          <? 
            $arCross = [];$arOfferIds=[];$arStores = [];
            foreach($arValue["crossed"] as $nValIdCross=>$arCrossValue){
                $arCross[]= $nValIdCross;
                $arOfferIds[] = $arCrossValue["offerId"];
            }
            $arStores = [];
            foreach($arValue["stores"] as $nStoreId=>$nAmount)
                $arStores[] = $nStoreId;
            $arCross = array_unique($arCross);
            $arOfferIds = array_unique($arOfferIds);
          ?>

        <?  $isColor = mb_strtolower(trim($arProp["name"]))=='цвет'?true:false; ?>
        <label>
            <input type="radio" name="<?= $sPropCode ?>" value="<?= $nValId ?>"
                pics="<?= implode("|",$arPics)?>"
                rel="<?= $nValId?>"
                cross-values="<?= implode(",",$arCross)?>"
                offers="<?= implode(",",$arOfferIds)?>"
                stores="<?= implode(",",$arStores)?>"
                switched="<?= count($arResult["OFFERS"])==1?"on":"off" ?>"
                <? if(count($arResult["OFFERS"])==1):?>checked<? endif?>
            >
            <div class="product-img-attr"
                <? if($isColor):?>
                style="background-image:url(<?= $arPics[0]?>)"
                <? endif ?>
            >
               <? if(!$isColor):?>
                   <?= $arValue['value']?>
               <? endif ?>
            </div>
        </label>
      <? endforeach ?>
      </div>
    </div>
  </div>
 <? endforeach ?>


 

