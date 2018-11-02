<!--@Боковой слайдер@-->
<div class="product-slider">
  <i class="fas fa-angle-up"></i>
  <? foreach($arPics as $key=>$morePhoto):?>
  <div class="product-slider-img"
      style="background-image: url(<?=$morePhoto?>);" rel="<?= $morePhoto;?>"
  ></div>
  <? endforeach ?>
  <i class="fas fa-angle-down"></i>
</div>

