<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои желания");
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
    <div class="ag-shop-content">
        <!-- Catalog {{{-->
        <div class="ag-shop-catalog">
            <div class="ag-shop-catalog__items-container">
                <div class="grid grid--bleed grid--justify-center my-wishes-ajax">
                </div>
            <a class="ag-shop-catalog__more-button" href="#" onclick="return wishes_load();">Ещё</a>
            </div>
        </div>
        <!-- }}} Catalog-->
    </div>

    <script>
    $(document).ready(function(){wishes_load();});
    </script>
<? endif;?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
