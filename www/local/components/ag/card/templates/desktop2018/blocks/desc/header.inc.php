<?
$useBeforeDate = $arResult["CATALOG_ITEM"]["PROPERTIES"]["USE_BEFORE_DATE"][0]["VALUE"];
$daysToExpire = $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"];

$useBefore = null;
$finished = false;
if (!$useBeforeDate && $daysToExpire) {
  $useBefore = date("d.m.Y", time() + ($daysToExpire - 1) * 24 * 60 * 60);
}

if ($useBeforeDate && !$daysToExpire ) {
  $useBefore = $useBeforeDate;
}

if ($useBeforeDate && $daysToExpire) {
  $tmp = date_parse($useBeforeDate);
  $ts1 = mktime(0, 0, 0, $tmp["month"], $tmp["day"], $tmp["year"]);
  $ts2 = time() + $daysToExpire * 24 * 60 * 60;
  $useBefore = date("d.m.Y", $ts1 < $ts2 ? $ts1 : $ts2);

  if ($ts1 + 24 * 60 * 60 < time()) {
      $finished = true;
  }
}

$useBeforeHtml = '';

?>
<div class="product-description-header">
    <h2><?= $arResult["CATALOG_ITEM"]["NAME"]?></h2>
    <div class="row">
      <div class="col-auto">
          <p id="product-date">Использовать до: <?= $useBefore?></p>
      </div>
      <?/*
      <div class="col-auto mg-left">
          <p id="attribute"><abbr title="attribute">12 отзывов</abbr></p>
      </div>
      <div class="col-auto mg-left">
        <p><i class="fas fa-star"></i></p>
        <p><i class="fas fa-star"></i></p>
        <p><i class="fas fa-star"></i></p>
        <p><i class="fas fa-star"></i></p>
        <p><i class="far fa-star"></i></p>
      </div>
      */?>
    </div>
    <? if($finished):?>
    <div class="row is-finished">
        Мероприятие завершено. Поощрение недоступно для заказа.
    </div>
    <? endif ?>
</div>

