<?
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

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


    /*
    $sQuery = "
        SELECT  
            `user`.`IBLOCK_ELEMENT_ID` as `ELEMENT_ID`
        FROM    
            `b_iblock_element_property` as `user`
        WHERE   
            `user`.`IBLOCK_PROPERTY_ID`=".$arPropList["WISH_USER"]."
            AND
            `user`.`VALUE_NUM`=".$nUserId."

    ";
    $res = $DB->Query($sQuery);
    $arItems = [];
    while($arItem = $res->Fetch())
        $arItems[] = $arItem["ELEMENT_ID"];

    $sQuery = "
        SELECT  
            `product`.`VALUE_NUM` as `ID`
        FROM    
            `b_iblock_element_property` as `product`
        WHERE   
            `product`.`IBLOCK_ELEMENT_ID` IN (".(
                $arItems
                ?
                implode(",",$arItems)
                :
                0
            ).")
            AND
            `product`.`VALUE_NUM` IN (".(
                $arProducts
                ?
                implode(",",$arProducts)
                :
                0
            ).")
    ";
    $res = $DB->Query($sQuery);
    $arProducts = [];
    while($arProduct = $res->Fetch())
        $arProducts[] = intval($arProduct["ID"]);
    */



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



echo json_encode($arProducts);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
