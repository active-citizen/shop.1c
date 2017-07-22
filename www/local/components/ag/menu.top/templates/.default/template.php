<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>
<!-- Top Nav {{{-->
<nav class="ag-shop-nav">
  <div class="ag-shop-nav__container">
    <div class="grid grid--bleed">
      <div class="grid__col-auto grid__col-md-shrink">
        <a class="ag-shop-nav__link" href="/catalog/" style="padding-left: 0px;">
        <div class="ag-shop-nav__link <? if(preg_match("#^/catalog/.*$#", $_SERVER["REQUEST_URI"])):?>ag-shop-nav__link--active<? endif ?>"><i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--basket"></i>
          <div class="ag-shop-nav__link-caption">Магазин<span class="show-on-desktop">&nbsp;поощрений</span></div>
        </div>
        </a>
      </div>
      <? if($USER->isAuthorized()):?>
      <div class="grid__col-auto grid__col-md-shrink">
          <a class="ag-shop-nav__link<? if(preg_match("#^/profile/.*$#", $_SERVER["REQUEST_URI"])):?> ag-shop-nav__link--active<? endif ?>" href="/profile/">
              <div class="ag-shop-nav__profile-container">
                <i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--profile"></i>
                <div class="ag-shop-nav__link-caption">
                  <span class="hide-on-desktop">Профиль</span>
                  <span class="show-on-desktop"><?= $arResult['FIO'];?></span>
                </div>
                <div class="ag-shop-nav__profile-points"><?= $arResult['myBalls'];?></div>
              </div>
          </a>
      </div>
      <? endif;?>
      <div class="grid__col-auto grid__col-md-shrink">
        <a class="ag-shop-nav__link <? if(preg_match("#^/rules/.*$#", $_SERVER["REQUEST_URI"])):?>ag-shop-nav__link--active<? endif ?>" href="/rules/">
            <i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--rules"></i>
            <div class="ag-shop-nav__link-caption">
                Правила
            </div>
          </a>
      </div>
      <!-- Можно выпилить целиком, если не нужно-->
      <!--
      <div class="grid__col-auto grid__col-md-shrink ag-shop-nav__last-item">
        <button class="ag-shop-nav__link" type="button" style="padding:0;/*safari/firefox*/">
          <div class="ag-shop-nav__link">
            <form action="/search/" class="searchform">
              <input name="q" type="text" placeholder="Поиск" value="<?= htmlspecialchars(isset($_GET["q"])?$_GET["q"]:"") ?>" disabled>
            </form>
          </div>
        </button>
      </div>
    </div>
    -->
  </div>
</nav>
<!-- }}} Top Nav-->

