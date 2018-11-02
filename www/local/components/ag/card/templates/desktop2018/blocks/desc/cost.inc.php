<div class="row">
  <div class="col-2">
  <p class="product-attr">Стоимость:</p>
  </div>
  <div class="col-4">
  <span><?=
    number_format(
        $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
        ,0,",",""
    )?> <?= 
        \Utils\CLang::getPoints(
            $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]                                        
        )
    ?></span>
  </div>
</div>

