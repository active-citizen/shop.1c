<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>

<table class="catalog-menu-table scroll-absolute"><tr><td style="width: <?
if($arResult["INTERESTS"]):?>55%;<? else:?>100%<? endif ?>">
<!-- Menu {{{-->
<div class="ag-shop-menu<? if($arResult["IN_CATALOG"]):?>  catalog-menu<? endif ?>">
  <div class="ag-shop-menu__container">
    <div class="ag-shop-menu__header">
      <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
        <div class="grid__col grid__col-shrink" style="width: 80% !important">
          <h2 class="ag-shop-menu__current"><? 

            $sCAtalogName = 'Все категории';

            foreach($arResult["SECTIONS"] as $arSection){
                if($arSection["SECTION_PAGE_URL"]==$_SERVER["REQUEST_URI"])
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
    <div class="ag-shop-menu__items js-menu__list" style="">
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
        <div class="ag-shop-menu__item ag-menu-breaker"></div>
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
