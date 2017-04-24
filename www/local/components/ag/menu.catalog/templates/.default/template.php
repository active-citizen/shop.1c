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
          <h2 class="ag-shop-menu__current">Все&nbsp;категории</h2>
        </div>
        <div class="grid__col grid__col-shrink">
          <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button" 
          type="button"><span></span></button>
        </div>
      </div>
    </div>
    <? if(preg_match("#^/catalog/.*#",$_SERVER["REQUEST_URI"])):?>
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
    </div>
    <? endif?>
  </div>
</div>
<!-- }}} Menu-->


