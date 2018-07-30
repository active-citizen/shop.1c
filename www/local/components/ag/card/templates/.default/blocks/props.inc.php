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

