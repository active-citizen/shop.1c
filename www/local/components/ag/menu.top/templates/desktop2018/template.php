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
              <div class="ag-shop-menu__item"><a class="ag-shop-menu__link" href="https://ag.mos.ru/poll/index">Голосования</a></div>
                <div class="ag-shop-menu__item"><a class="ag-shop-menu__link" href="https://ag.mos.ru/experiments">Городские новинки</a></div>
                <div class="ag-shop-menu__item"><a class="ag-shop-menu__link" href="https://ag.mos.ru/dom">Электронный дом</a></div>
                <div class="ag-shop-menu__item"><a class="ag-shop-menu__link" href="https://ag.mos.ru/results">Результаты работы</a></div>
                <div class="ag-shop-menu__item"><a class="ag-shop-menu__link ag-shop-menu__link--active" href="/catalog/">Магазин поощрений</a></div>
                <div class="ag-shop-menu__item item-articles"><a class="ag-shop-menu__link" href="https://ag.mos.ru/photos">Проект в лицах</a></div>
                <!--<div class="ag-shop-menu__item ag-menu-breaker"></div>-->
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="https://ag.mos.ru/schedule">
            Афиша
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link" href="https://ag.mos.ru/blockchain">
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

        <!--Profile block-->
      <div id="profie-win-top">
        <? if(!$USER->isAuthorized()):?>
        <div id="profie-win">
            <a class="ag-shop-profile ag-shop-nav__link<? if(preg_match("#^/profile/.*$#", $_SERVER["REQUEST_URI"])):?> ag-shop-nav__link--active<? endif ?>" href="https://ag.mos.ru/profile">

                <img id="logo-profile" src="/local/templates/desktop2018/img/logo-profile.png" alt="logo-profile">
                  <div id="logo-balls" class="ag-shop-nav__link-caption">
                    <span id="fio-logo" class="show-on-desktop"><?=$arResult['FIO']?$arResult['FIO']:"";?></span>
                    <div id="fio-balls" class="ag-shop-nav__profile-points"><?= $arResult['myBalls'];?></div>
                  </div>   
                </a>
            <button onclick="diplay_hide('.ag-shop-dropdown-profile');return false;"><i id="fa-angle" class="fas fa-angle-down"></i></button>    
        </div>
        <div style="display: none;" class="ag-shop-dropdown-profile">
          <ul>
            <li><a href="/profile">Мой профиль</a></li>
            <li><a href="/profile/points/">Мои баллы</a></li>
            <li><a href="/profile/order/">Мои заказы</a></li>
            <li><a href="/profile/wishes/">Мои желания</a></li>
          </ul>
        </div>
      <? else : ?>
      <div class="profie-win">
        <div class="no-autorized" style="padding: auto;">
          <div class="ag-shop-intro">
            <img src="/local/templates/desktop2018/img/img-user.png" alt="key">
              <p>Вход</p>
          </div>
         <div class="ag-shop-user">
           <img src="/local/templates/desktop2018/img/img-key.png" alt="user">
             <p>Регистрация</p>
         </div>

        </div>
      </div>

        <? endif;?>
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
      <div class="ag-line-none grid__col-auto grid__col-md-shrink">
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

<script type="text/javascript"> 

function diplay_hide (profileBlock)
{ 
  if ($(profileBlock).css('display') == 'none') { 
      $(profileBlock).animate({height: 'show'}, 500); 
    } else {     
        $(profileBlock).animate({height: 'hide'}, 500); 
      }
} 
</script>

<!-- }}} Top Nav-->
