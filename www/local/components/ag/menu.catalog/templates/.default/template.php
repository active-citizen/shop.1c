<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>

<!-- Menu {{{-->
<div class="ag-shop-menu">
  <div class="ag-shop-menu__container">
    <? /*if(preg_match("#^/catalog/.*#",$_SERVER["REQUEST_URI"])):*/?>
    <div class="ag-shop-menu__items js-menu__list">
        <div class="ag-shop-menu__item">
            <a class="ag-shop-menu__link<? if(preg_match("#^".$section["SECTION_PAGE_URL"]."#",
                $_SERVER["REQUEST_URI"])):?> ag-shop-menu__link--active<? endif?>" 
                href="<?= $section["SECTION_PAGE_URL"];?>"
                style="visibility: collapse;"
            >
            &#160;
            </a>
        </div>
    </div>
  </div>
</div>
<!-- }}} Menu-->


