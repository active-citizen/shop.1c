<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>
        <?if(count($arResult["BANNERS"])):?>
          <!-- Slider {{{-->
        <!--  <div class="ag-shop-slider" style="height:500px;margin-bottom: 5px;">
            <div class="js-content-slider">


            </div>
            <div class="ag-shop-slider__buttons">
              <div class="ag-shop-slider__prev"></div>
              <div class="ag-shop-slider__next"></div>
            </div>
            <div class="ag-shop-slider__dots"></div>
          </div>-->
          <!--}}} Slider-->

          <section class="single-item">
            <div>
              <img src="/local/templates/desktop2018/img/ag-demo-banner.jpg">
            </div>
            <div>
              <img src="/local/templates/desktop2018/img/ag-demo-banner.jpg">
            </div>
            <div>
              <img src="/local/templates/desktop2018/img/ag-demo-banner.jpg">
            </div>
            <div>
              <img src="/local/templates/desktop2018/img/ag-demo-banner.jpg">
            </div>
            <div>
              <img src="/local/templates/desktop2018/img/ag-demo-banner.jpg">
            </div>
            <div>
              <img src="/local/templates/desktop2018/img/ag-demo-banner.jpg">
            </div>
          </section>


        <script>
        $('.single-item').slick({
          dots: true,
          dotsClass: 'ag-shop-slider__dots',
          infinite: true,
          autoplay: true,
          prevArrow: '<i class="fas fa-arrow-left"></i>',
          nextArrow: '<i class="fas fa-arrow-right"></i>',
        });
        </script>

        <? endif ?>
