<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>

<table class="catalog-menu-table scroll-fixed">
  <tbody>
<div class="ag-shop-menu">
  <div class="ag-shop-menu__container">
    <div class="ag-shop-menu__header">
      <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
        <div class="grid__col grid__col-shrink" style="width: 80%;">
          <h2 class="ag-shop-menu__current">Все категории</h2>
        </div>
        <div class="grid__col grid__col-shrink">
          <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button" type="button"><span></span></button>
        </div>
      </div>
    </div>
        <div class="ag-shop-menu__items js-menu__list">
              <div class="ag-shop-menu__item" id="logo-top-menu">
                <img src="/local/templates/desktop2018/img/logo-menu.png" alt="logo-menu">
              </div>
              <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link" href="/catalog/eksklyuziv/">
                Голосования            </a>
              </div>
                <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link" href="/catalog/suveniry/">
                Городские новинки            </a>
        </div>
                <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link" href="/catalog/muzei/">
               Электронный дом            </a>
        </div>
                <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link" href="/catalog/meropriyatiya/">
                Результаты работы            </a>
        </div>
                <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link ag-shop-menu__link--active" href="/catalog/elektronnyy-kontent/">
                Магазин поощрений            </a>
        </div>
                <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="/catalog/transport/">
                Проект в лицах            </a>
              </div>

                <!--<div class="ag-shop-menu__item ag-menu-breaker"></div>-->
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="/profile/order/">
            Афиша
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="/profile/wishes/">
            Blockchain
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="/rules/hiw/">
            Правила
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="/rules/stores/">
            Адреса
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="/rules/faq/">
            FAQ
            </a>
        </div>

        <!--Карточка профиля-->
      <div id="profie-win-top">
        <? //if($USER->isAuthorized()):?>
        <div id="profie-win">
            <a class="ag-shop-nav__link<? if(preg_match("#^/profile/.*$#", $_SERVER["REQUEST_URI"])):?> ag-shop-nav__link--active<? endif ?>" href="/profile/">

                <img id="logo-profile" src="/local/templates/desktop2018/img/logo-profile.png" alt="logo-profile">
                  <div id="logo-balls" class="ag-shop-nav__link-caption">
                    <span id="fio-logo" class="show-on-desktop"><?= $arResult['FIO'];?>Александр Алексеевский</span>
                    <div id="fio-balls" class="ag-shop-nav__profile-points"><?= $arResult['myBalls'];?></div>
                  </div>
            </a>
            <i id="fa-angle" class="fas fa-angle-down"></i>
        </div>
        <? //endif;?>
      </div>
      <!--End profile block-->
      </div>


    </div>
</div>
<!-- }}} Menu - Top-Header-->
</tbody>
</table>
<!-- Top Nav {{{-->
<nav class="ag-shop-nav">
  <div class="ag-shop-nav__container">
    <div class="grid grid--bleed">
      <div class="grid__col-auto grid__col-md-shrink">
        <a class="ag-shop-nav__link" href="/catalog/" style="padding-left: 0px;">
        <div id="ag-shop-nav__link--active" class="ag-shop-nav__link
        <? if(preg_match("#^/catalog/.*$#", $_SERVER["REQUEST_URI"])):?>
      <? endif ?>">
          <div class="ag-shop-nav__link-caption" id="shop-title">Магазин<span class="show-on-desktop">&nbsp;поощрений</span></div>
        </div>
        </a>
      </div>
      <!-- Можно выпилить (search) целиком, если не нужно-->
      <!--There was a search-->
    </div>
  </div>
</nav>
<!-- }}} Top Nav-->
