<?
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CCache/CCache.class.php");
use Cache;

define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$objCache = new \Cache\CCache("wishes",md5($_SERVER["REQUEST_URI"]).$USER->GetID(),300);
if($sData = $objCache->get()){
echo json_encode($sData);
die;
}


    $arProducts = [];
    if(isset($_POST["products"])){
        $arProducts = explode(',', $_POST["products"]);
        foreach($arProducts as $nKey=>$sVal)
            $arProducts[$nKey] = intval($sVal);
    }

    $nUserId = $USER->IsAuthorized()?$USER->GetId():0;

    // Составляем справочник свойств
    $sQuery = "
        SELECT 
            `ID`,`CODE`
        FROM
            `b_iblock_property`
        WHERE  
            `CODE` IN ('WISH_USER','WISH_PRODUCT')
        LIMIT 2
    ";
    $res = $DB->Query($sQuery);
    $arPropList = [];
    while($arProp = $res->Fetch())
        $arPropList[$arProp["CODE"]] = $arProp["ID"];

$sQuery = "
    SELECT
        FLOOR(`product`.`VALUE_NUM`) as `ID`
    FROM
        `b_iblock_element_property` as `product`
            LEFT JOIN
        `b_iblock_element_property` as `user`
            ON
            `user`.`IBLOCK_PROPERTY_ID`=".$arPropList["WISH_USER"]."
            AND
            `user`.`VALUE_NUM`= ".$nUserId."
            AND
            `product`.`IBLOCK_ELEMENT_ID`=`user`.`IBLOCK_ELEMENT_ID`
    WHERE
        `product`.`IBLOCK_PROPERTY_ID`=".$arPropList["WISH_PRODUCT"]."
        AND
        `user`.`ID` IS NOT NULL
        AND
        `product`.`VALUE_NUM` IN (".(
            $arProducts
            ?
            implode(",",$arProducts)
            :
            0
        ).")
    LIMIT   
        ".count($arProducts)."
";
$arProducts = [];
$res = $DB->Query($sQuery);
while($arProduct = $res->Fetch())$arProducts[] = $arProduct["ID"];


$objCache->set($arProducts);
echo json_encode($arProducts);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
