<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>

<!-- Menu {{{-->
<div class="ag-shop-menu">
  <div class="ag-shop-menu__container">
    <div class="ag-shop-menu__header">
      <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
        <div class="grid__col grid__col-shrink">
          <h2 class="ag-shop-menu__current"><? 

            $sCAtalogName = 'Главная';

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
    <div class="ag-shop-menu__items js-menu__list">
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

