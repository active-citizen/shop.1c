<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>  
        <?if(count($arResult["BANNERS"])):?>
          <!-- Slider {{{-->
          <div class="ag-shop-slider" style="height:500px;margin-bottom: 5px;">
            <div class="js-content-slider">
                
            <?foreach($arResult["BANNERS"] as $BANER):?>
                <?if($BANER["CATALOG_LINK_DATA"]):?>
                <div class="ag-shop-slider__item">
                    <button class="ag-shop-slider-card__likes" type="button">
                      <div class="ag-shop-slider-card__likes-icon<? if($BANER["CATALOG_LINK_DATA"]["MY_WISH"]){?> wish-on<? }else{?> wish-off<? }?>"
                        productid="<?= $BANER["CATALOG_LINK_DATA"]["ID"]?>" 
                        onclick="return mywish(this);"></div>
                      <div class="ag-shop-slider-card__likes-count"
                      id="wishid<?= $BANER["CATALOG_LINK_DATA"]["ID"]?>"><?= 
                        $BANER["CATALOG_LINK_DATA"]["WISHES"]
                      ?></div>
                    </button>
                  <a class="ag-shop-slider-card" href="<?=
                  $BANER["CATALOG_LINK_DATA"]["URL"]?>"
                  style="background-image:url(<?= $BANER["CATALOG_LINK_DATA"]["PREVIEW_PICTURE"]?>)">
                        <!-- 
                            <img class="ag-shop-slider-card__image" src="<?=
                             $BANER["CATALOG_LINK_DATA"]["PREVIEW_PICTURE"]?>">
                         -->
                  <div class="ag-shop-slider-card__badges">
                      <? if($BANER["CATALOG_LINK_DATA"]["NEWPRODUCT"]):?>
                      <img class="ag-shop-slider-card__badge" src="/local/assets/images/badge__new.png">
                      <? endif ?>
                      <? if($BANER["CATALOG_LINK_DATA"]["SALELEADER"]):?>
                      <img class="ag-shop-slider-card__badge" src="/local/assets/images/badge__hit.png">
                      <? endif ?>
                      <? if($BANER["CATALOG_LINK_DATA"]["SPECIALOFFER"]):?>
                      <img class="ag-shop-slider-card__badge" src="/local/assets/images/badge__sale.png">
                      <? endif ?>
                  </div>
                  <div class="ag-shop-slider-card__info-layer">
                    <div class="ag-shop-slider-card__points">
                      <div class="ag-shop-slider-card__points-count">
                          <?= $BANER["CATALOG_LINK_DATA"]["PRICE"] ?>
                      </div>
                      <div class="ag-shop-slider-card__points-text"><? 
                        echo get_points($BANER["CATALOG_LINK_DATA"]["PRICE"]);
                      ?></div>
                    </div>
                    <div class="ag-shop-slider-card__info">
                      <p class="ag-shop-slider-card__category"><?= $BANER["CATALOG_LINK_DATA"]["SECTION_NAME"]?></p>
                      <p class="ag-shop-slider-card__description"><?= 
                        mb_strlen($BANER["CATALOG_LINK_DATA"]["PREVIEW_TEXT"])<64?
                        strip_tags($BANER["CATALOG_LINK_DATA"]["PREVIEW_TEXT"]):
                        mb_substr(strip_tags($BANER["CATALOG_LINK_DATA"]["PREVIEW_TEXT"]),0,64)."..."
                      ?></p>
                      <div class="ag-shop-slider-card__rating" title="Средняя оценка  <?= $BANER["CATALOG_LINK_DATA"]["RATING"]?>">
                        <? for($i=0;$i<round($BANER["CATALOG_LINK_DATA"]["RATING"]);$i++):?>
                        <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
                        <? endfor ?>
                        <? for($j=0;$j<5-round($BANER["CATALOG_LINK_DATA"]["RATING"]);$j++):?>
                        <div class="ag-shop-slider-card__rating-item"></div>
                        <? endfor ?>
                      </div>
                      <h3 class="ag-shop-slider-card__name"><?= $BANER["CATALOG_LINK_DATA"]["NAME"] ?></h3>
                    </div>
                  </div>
                  </a>
                </div>
                <? endif ?>  
                  
                <?if(!$BANER["CATALOG_LINK_DATA"]):?>
                <div class="ag-shop-slider__item">
                  <a class="ag-shop-slider-card-dark" href="<?= $BANER["PROPERTIES"]["BANER_URL"]["VALUE"]?>" style="background-image:url(<?= $BANER["CATALOG_LINK_DATA"]["PREVIEW_PICTURE"]?>)">
                    <!-- <img class="ag-shop-slider-card-dark__image" src=""
                         style="display:none;"> -->
                  <div class="ag-shop-slider-card-dark__info-layer" style="background-image: url('<?= $BANER["PROPERTIES"]["BANER_PICTURE"]["URL"] ?>');">
                    <div class="ag-shop-slider-card-dark__info">

                    <h3 class="ag-shop-slider-card-dark__name"
                    style="text-shadow: 0 0 5px rgba(0,0,0,1);"><?= $BANER["NAME"]?></h3>
                    </div>
                  </div></a>

                    <div style="position: absolute; bottom: 10px; color:  white;
                    z-index: 3; left: 20px; right: 20px; text-shadow: 0 0 3px
                    rgba(0,0,0,1); color: white !important;">
                      <?= 
                        $BANER["PROPERTIES"]["BANER_DESC"]["VALUE"]
                      ?>
                    </div>

                </div>
                <? endif ?>  
            <? endforeach?>
            </div>
            <div class="ag-shop-slider__buttons">
              <div class="ag-shop-slider__prev"></div>
              <div class="ag-shop-slider__next"></div>
            </div>
            <div class="ag-shop-slider__dots"></div>
          </div>
          <!-- }}} Slider-->

        <script>
          (function () {
            $('.js-content-slider').slick({
              mobileFirst: true,
              centerPadding: 0,
              arrows: false,
              dots: true,
              dotsClass: 'ag-shop-slider__dots',
              infinite: true,
              speed: 300,
              slidesToShow: 1,
              centerMode: false,
              variableWidth: true,
<? if($arResult["AUTOPLAY_DELAY"]):?>
              autoplay: true,
              autoplaySpeed: <?= $arResult["AUTOPLAY_DELAY"]?>,
<? else:?>
              autoplay: false,
<? endif ?>
              responsive: [{
                  breakpoint: 992,
                  settings: {
                      arrows: true
                  }
              }]
            });
          })();
        </script>

        <? endif ?>

