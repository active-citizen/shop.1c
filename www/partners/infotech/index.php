<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Инфотех::справочники");
require("../group_access.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CCache/CCache.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php");

use AGShop\Integration as Integration;
use AGShop\Cache as Cache;


?>
<div class="partners-main">
    <h1>Справочники Инфотех</h1>
    <? include("../menu.php"); ?>
    <table class="cities">
        <tr>
            <td>
                <iframe src="/partners/infotech/cities.frame.php"></iframe>
            </td>
            <td>
                <iframe name="actions" src="/partners/infotech/actions.frame.php"></iframe>
            </td>
            <td>
                <iframe name="seats" src="/partners/infotech/seats.frame.php"></iframe>
            </td>
        </tr>
    </table>
</div>

<style>
    table.cities td{
        height: 450px;
    }

    table.cities{
        width: 100%;
    }


    table.cities iframe{
        border: 1px transparent solid;
        width: 100%;
        height: 100%;
    }


</style>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
