<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Страница не найдена");?>

    <div class="ag-shop-content">
        <div class="ag-shop-content__limited-container" style="text-align:center;">

            <h2>Страница не найдена</h2>

            <div class="bx-404-container">
                <div class="bx-404-block"><img src="<?=SITE_DIR?>images/404.png" alt=""></div>
                <div class="bx-404-text-block">Неправильно набран адрес, <br>или такой страницы на сайте больше не существует.</div>
                <div class="">Вернитесь на <a href="<?=SITE_DIR?>">главную</a> или воспользуйтесь картой сайта.</div>
            </div>
    
            <div class="map-columns row">
                <div class="col-sm-10 col-sm-offset-1">
                    <div class="bx-maps-title">Карта сайта:</div>
                </div>
            </div>

            
        </div>

    </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
