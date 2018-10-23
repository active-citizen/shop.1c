<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>

<table class="catalog-menu-table scroll-fixed<?
if($_SERVER["REQUEST_URI"]=='/catalog/'):?> on-top<? endif ?>"><tr><td style="width: <?
if($arResult["INTERESTS"] && IS_MOBILE):?>55%;<? else:?>100%<? endif ?>">
<!-- Menu {{{-->
<tr><td>
<div class="ag-shop-menu<? if($arResult["IN_CATALOG"]):?>  catalog-menu<? endif ?>">
  <div class="ag-shop-menu__container">
    <div class="ag-shop-menu__header">
      <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
        <div class="grid__col grid__col-shrink" style="width: 80% !important">
          <h2 class="ag-shop-menu__current"><?

            $sCAtalogName = 'Все категории';

            foreach($arResult["SECTIONS"] as $arSection){
                if(strpos($_SERVER["REQUEST_URI"],
                $arSection["SECTION_PAGE_URL"])!==false)
                    $sCAtalogName = $arSection["NAME"];
            }
                $arPath = explode("/",$_SERVER["REQUEST_URI"]);
                if($arPath[1]=='profile' && $arPath[2]=='wishes'){
                    echo "Мои желания";
                }
                elseif($arPath[1]=='profile' && $arPath[2]=='order'){
                    echo "Мои заказы";
                }
                elseif($arPath[1]=='rules' && $arPath[2]=='hiw'){
                    echo "Правила";
                }
                elseif($arPath[1]=='rules' && $arPath[2]=='stores'){
                    echo "Адреса";
                }
                elseif($arPath[1]=='rules' && $arPath[2]=='faq'){
                    echo "FAQ";
                }
                else{
                    echo $sCAtalogName;
                }
           ?></h2>
        </div>
        <div class="grid__col grid__col-shrink">
          <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button"
          type="button"><span></span></button>
        </div>
      </div>
    </div>
    <? /*if(preg_match("#^/catalog/.*#",$_SERVER["REQUEST_URI"])):*/?>
    <div class="ag-shop-menu__items js-menu__list" style="background-color: #f1f0f0;">
        <? if($_SERVER["REQUEST_URI"]!='/catalog/' && IS_MOBILE):?>
        <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link" href="/catalog/" >
                <?
                    echo "Все категории"
                ?>
            </a>
        </div>
        <? endif ?>
        <?/*
        <?php foreach($arResult["SECTIONS"] as $section):?>
        <? if(!$section["products"])continue;?>
        <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link<? if(preg_match("#^".$section["SECTION_PAGE_URL"]."#",
                $_SERVER["REQUEST_URI"])):?> ag-shop-menu__link--active<? endif?>"
                href="<?= $section["SECTION_PAGE_URL"];?>"
            >
                <?= $section["NAME"];?>
            </a>
        </div>
        <?endforeach?>
        */?>

        <!--!!! Here begins menu.catalog !!!-->

        <?php foreach($arResult["SECTIONS"] as $section):?>
        <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link<? if($section["CURRENT"]):
            ?> ag-shop-menu__link--active<? endif?>"
                href="/catalog/<?= $section["CODE"];?>/"
            >
                <?= $section["NAME"];?>
            </a>
        </div>
        <?endforeach?>
        <!--<div class="ag-shop-menu__item ag-menu-breaker"></div>--->
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link<?
            if(preg_match("#^/profile/order/.*#",$_SERVER["REQUEST_URI"])):?> ag-shop-menu__link--active<? endif?>"
                href="/profile/order/" >
            Мои заказы
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link<?
            if($_SERVER["REQUEST_URI"]=="/profile/wishes/"):?> ag-shop-menu__link--active<? endif?>"
                href="/profile/wishes/" >
            Мои желания
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link<?
            if($_SERVER["REQUEST_URI"]=="/rules/hiw/"):?> ag-shop-menu__link--active<? endif?>"
                href="/rules/hiw/" >
            Правила
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link<?
            if($_SERVER["REQUEST_URI"]=="/rules/stores/"):?> ag-shop-menu__link--active<? endif?>"
                href="/rules/stores/" >
            Адреса
            </a>
        </div>
        <div class="ag-shop-menu__item item-articles">
            <a class="ag-shop-menu__link<?
            if($_SERVER["REQUEST_URI"]=="/rules/faq/"):?> ag-shop-menu__link--active<? endif?>"
                href="/rules/faq/" >
            FAQ
            </a>
        </div>
         
        <!--Старый Блок с поиском -->
          
          <!-- <button class="ag-shop-nav__link" type="button" style="padding:0;/*safari/firefox*/">
            <div class="ag-shop-nav__link">
                <form action="/search/" class="searchform">
                <input id="ag-input-search" name="q" type="text" value="<?= htmlspecialchars(isset($_GET["q"])?$_GET["q"]:"") ?>" disabled>
              </form>
            </div>
          </button>
 -->

          <div class="ag-catalog-icon row">
             <div class="mobile-header-search">
                    <form id="mobileHeaderSearchForm" class="searchform">
                        <div id="multiple-datasets" class="mobile-header-search__input">
                            <input  class="typeahead" type="text" id="mobileHeaderSearchInput" name="mobileHeaderSearchInput" placeholder="Что вы ищете?" autocorrect="off" autocomplete="off">
                            <button class="mobile-header-search__clear" type="button" name="clearTypeahead"></button>
                        </div>
                    </form>
                </div>

            <div class="ag-portmone-balls">
            <div class="ag-wrap-balls">
            <a class="ag-shop-menu__link" href="/profile/points">
                <img src="/local/templates/desktop2018/img/portmone-balls.png" alt="portmone-balls">
                <?=number_format(intval($arResult["BALANCE"]),0,""," ")?>
            </a>
            </div>
          </div>
          <div class="ag-heart">
            <div class="ag-wrap-balls">
            <a class="ag-shop-menu__link" href="/profile/wishes/">
                <img src="/local/templates/desktop2018/img/heart.png" alt="portmone-balls">
                <?=$arResult["WISHES_COUNT"];?>
            </a>
          </div>
          </div>
          <div class="ag-cart">
            <div class="ag-wrap-balls">
            <a class="ag-shop-menu__link" href="/profile/order">
                <img src="/local/templates/desktop2018/img/cart.png" alt="portmone-balls">
                <?= $arResult["ORDERS_COUNT"]?>
            </a>
          </div>    
          </div>
        </div>
    </div>
    <?/* endif */?>
  </div>
</div>
<!-- }}} Menu-->
</td><td>

<!-- Menu {{{-->
<? if($arResult["INTERESTS"]):?>
<div class="ag-shop-menu<? if($arResult["IN_CATALOG"]):?> tags-menu<? endif ?>">
  <div class="ag-shop-menu__container">
    <div class="ag-shop-menu__header">
      <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
        <div class="grid__col grid__col-shrink">
          <h2 class="ag-shop-menu__current">Фильтры<span class="filter-counts"></span></h2>
        </div>
        <div class="grid__col grid__col-shrink">
          <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button"
          type="button"><span></span></button>
        </div>
      </div>
    </div>
    <? /*if(preg_match("#^/catalog/.*#",$_SERVER["REQUEST_URI"])):*/?>
    <div class="ag-shop-menu__items js-menu__list"
    style="margin-left:-132%;width:225%;">

    <? if($arResult["INTERESTS"]):?>
    <div class="ag-shop-filter__variants mobile-filter" id="interests-filter" style="display:
    block; text-align: center;">
      <? foreach($arResult["INTERESTS"] as $INTEREST_ID=>$INTEREST):?>
      <label>
        <input type="checkbox" class="ag-interest" value="<?= $INTEREST_ID ?>" title="<?=
        $INTEREST["NAME"]?>">
        <div class="ag-shop-filter__variants-item">
            <?= $INTEREST["NAME"]?>
            <? if($INTEREST["COUNT"]):?>
            (<?=
            $INTEREST["COUNT"]
            ?>)
            <? endif ?>
        </div>
      </label>
      <? endforeach ?>
    </div>
    <? endif ?>

    </div>
    <?/* endif */?>
  </div>
</div>
<? endif ?>
<!-- }}} Menu-->

</td></tr></table>


