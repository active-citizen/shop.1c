<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    CModule::IncludeModule("iblock");
    $APPLICATION->SetTitle(
        "Мои желания"
    );
    include("../menu.php");

?>


<? if( !CUser::IsAuthorized()):?>
  <div class="ag-shop-content">
    <div class="ag-shop-content__limited-container">
      <div class="ag-shop-card__container">
        <div class="ag-shop-card__requirements">
            Для просмотра данной страницы необходимо 
            <a class="ag-shop-menu__link--active"
            href="http://ag.mos.ru/">авторизоваться</a>
        </div>
      </div>
    </div>
  </div>
<? else: ?>
    <? if(IS_MOBILE || IS_PHONE):?>
        <? require($_SERVER["DOCUMENT_ROOT"]."/catalog/mobile.filter.params.php");?>
    <? else:?>
        <? require($_SERVER["DOCUMENT_ROOT"]."/catalog/desktop.filter.params.php");?>
        <? include_once($_SERVER["DOCUMENT_ROOT"]."/catalog/filter.inc.php");?>
    <? endif?>
    <? include_once($_SERVER["DOCUMENT_ROOT"]."/catalog/container.inc.php");?>
<? endif?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
