<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои желания");
include("../menu.php");
?>
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
