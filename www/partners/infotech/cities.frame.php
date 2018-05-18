<?

require_once($_SERVER["DOCUMENT_ROOT"].
            "/bitrix/modules/main/include/prolog_before.php");

require("../group_access.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegrationInfotech.class.php");

use AGShop\Integration as Integration;

$objInfotech = new \Integration\CIntegrationInfotech;
$arCities = $objInfotech->getCities();

?>
<div class="partners-main">
    <h1>Города</h1>
    
</div>

