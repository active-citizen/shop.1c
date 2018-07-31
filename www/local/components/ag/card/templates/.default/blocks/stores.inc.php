  <div class="ag-shop-card__field js-choose__place" 
    <? if($arResult["OFFER_PARAMETERS"]):?>style="display:none;"<? endif ?>
  >
    <div class="ag-shop-card__fieldname">Где получить?</div>

    <? foreach($arResult["OFFERS_JSON"] as $nOfferId=>$arOffer):?>
    <div class="ag-shop-card__places" offer-id="<?= $nOfferId?>"
        style="display:none;"
    >
      <? $count=0;
      $storageSelected = false;
      if(!$stopDailyLimit)
      foreach($arOffer["STORAGES"] as $id=>$ammount): $count++;?>
      <label>
        <input  onclick="return selectStorage('<?= $id;?>');"type="radio" name="place" value="<?= $id ?>" <?
        if (count($arResult["OFFERS"][0]["STORAGES"]) == 1) {
            $storageSelected = true;
            echo " checked ";
        }
            /*
            if($count==count($arResult["OFFERS"][0]["STORAGES"]))echo
                " checked ";
            */
        ?>>
        <div class="ag-shop-card__places-item"><?= $arResult["STORAGES"][$id]["TITLE"] ?></div>
      </label>
      <? endforeach ?>
    </div>
    <? endforeach ?>

    <div class="ag-shop-card__selected-place<?= $storageSelected ? '' : ' hidden'; ?>">
      <div class="ag-shop-card__selected-place-header">
        <div class="grid grid--bleed grid--justify-space-between">
          <div class="grid__col-xs-12 grid__col-sm-shrink mobile_none">
            <div class="ag-shop-card__selected-place-station">
                <i class="ag-shop-icon ag-shop-icon--metro"></i>
                <span>
                <? if(count($arResult["OFFERS"][0]["STORAGES"])==1):?>
                <?= $arResult["STORAGES"][$id]["TITLE"] ?>
                <? endif?>
                </span>
            </div>
          </div>
          <div class="grid__col-xs-12 grid__col-sm-shrink">
              <? foreach(array(
                array(0,0,"отсутствует"),
                array(1,10,"мало"),
                array(11,100,"достаточно"),
                array(101,1000000000,"много")
              ) as $arAmmount):?>
                <div class="ag-shop-card__remaining-count" 
                fromAmmount="<?= $arAmmount[0]?>"
                toAmmount="<?= $arAmmount[1]?>"
                style="display: <?
                  if(
                    count($arResult["OFFERS"][0]["STORAGES"])==1
                    &&
                    (
                        $ammount>=$arAmmount[0] 
                        &&
                        $ammount<=$arAmmount[1]
                    )
                  ): ?>inline-block;<? else:?>none;<? endif ?>"
                >
                  <span class="ag-shop-card__remaining-count-title">
                    осталось:
                  </span>
                  <span class="ag-shop-card__remaining-count-text">
                    <?= $arAmmount[2]?>
                  </span>
                </div>
              <? endforeach ?>
          </div>
        </div>
      </div>
      <table class="ag-shop-card__selected-place-table">
      <? if(count($arResult["OFFERS"][0]["STORAGES"])==1):?>
            <? if(trim($arResult["STORAGES"][$id]["ADDRESS"])):?>
            <tr>
              <td>Адрес:</td>
              <td><?= $arResult["STORAGES"][$id]["ADDRESS"] ?></td>
            </tr>
            <? endif ?>
            <? if(trim($arResult["STORAGES"][$id]["PHONE"])):?>
            <tr>
              <td>Телефон:</td>
              <td><?= $arResult["STORAGES"][$id]["PHONE"] ?></td>
            </tr>
            <? endif ?>
            <? if(trim($arResult["STORAGES"][$id]["SCHEDULE"])):?>
            <tr>
              <td>Режим:</td>
              <td><?= $arResult["STORAGES"][$id]["SCHEDULE"] ?></td>
            </tr>
            <? endif ?>
            <? if($arResult["STORAGES"][$id]["EMAIL"]):?>
            <tr>
              <td>Сайт:</td>
              <td><a href="<?=
              $arResult["STORAGES"][$id]["EMAIL"]
              ?>" target="_blank"><?=
              linkTruncate($arResult["STORAGES"][$id]["EMAIL"]) 
              ?></a></td>
            </tr>
            <? endif ?>
      <? endif ?>
      </table>
      <? if(0 && trim($arResult["STORAGES"][$id]["DESCRIPTION"])):?>
      <p class="ag-shop-card__selected-place-description"><?= $arResult["STORAGES"][$id]["DESCRIPTION"] ?></p>
      <? endif ?>
    </div>
  </div>

