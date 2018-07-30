<? if(trim($arResult["CATALOG_ITEM"]["DETAIL_TEXT"])):?>
  <h4>Описание:</h4>
  <p class=""><?= 
    $arResult["CATALOG_ITEM"]["DETAIL_TEXT"]
  ?></p>
<? endif ?>
  <? if(
      $arResult["CATALOG_ITEM"]["PROPERTIES"]
        ["RECEIVE_RULES"][0]["~VALUE"]["TEXT"]
  ):?>
  <h4>Правила получения:</h4>
  <p>
  <?=
  $arResult["CATALOG_ITEM"]["PROPERTIES"]
    ["RECEIVE_RULES"][0]["~VALUE"]["TEXT"]
  ?>
  </p>
  <? endif ?>

  <? if(
      $arResult["CATALOG_ITEM"]["PROPERTIES"]
        ["CANCEL_RULES"][0]["~VALUE"]["TEXT"]
  ):?>
  <h4>Правила отмены:</h4>
  <p>
  <?=
  $arResult["CATALOG_ITEM"]["PROPERTIES"]
    ["CANCEL_RULES"][0]["~VALUE"]["TEXT"]
  ?>
  </p>
  <? endif ?>

