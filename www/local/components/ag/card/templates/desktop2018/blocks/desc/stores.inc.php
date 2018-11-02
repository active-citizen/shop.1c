<div class="col-auto">
    <div class="row">
        <div class="col-4">
            <p class="product-attr">Где получить:</p>
        </div>
        <div class="col-6">
            <div class="btn-group">
                <button type="button" id="btn-map" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Выбрать район
                </button>
                <div class="dropdown-menu">
                  <? $count=0;
                  $storageSelected = false;
                  if(!$stopDailyLimit)
                  foreach($arResult["OFFERS_STORAGES"] as $id=>$arStore): 
                  $count++;?>
                    <a class="dropdown-item" href="#"
                        offers="<?= implode(",", $arStore["offers"])?>"
                        propsvals="<?= implode(",", $arStore["propsVals"])?>"
                        switched="off"
                    ><?= $arResult["STORAGES"][$id]["TITLE"] ?></a>
                  <? endforeach ?>
                </div>
            </div>
            <div class="col-auto">
                <p id="product-attr-map" data-toggle="modal" data-target="#modal-map">
                    <i class="fas fa-map-marker-alt"></i>
                    <abbr title="attribute">Показать на карте</abbr>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="col-auto">
    <div class="row">
        <div class="col-3">
            <p class="product-attr">На складе:</p>
        </div>
        <div class="col-2">
            <span>Много</span>
        </div>
    </div>
</div>

