<div class="ag-right-block">
<h4>Новинки</h4>
<?
global $newProductFilter;
$newProductFilter = array("PROPERTY_6"=>1);
$APPLICATION->IncludeComponent(
"bitrix:catalog.top",
    "",
    Array(
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => "2",
        "ELEMENT_COUNT"=>3,
        "FILTER_NAME"=>"newProductFilter",
        "ELEMENT_SORT_FIELD"=>"TIMESTAMP_X",
        "ELEMENT_SORT_ORDER"=>"desc"
    )
);
?>


<h4>Товары по акции</h4>
<?
global $actionProductFilter;
$actionProductFilter = array("PROPERTY_8"=>3);
$APPLICATION->IncludeComponent(
"bitrix:catalog.top",
    "",
    Array(
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => "2",
        "ELEMENT_COUNT"=>3,
        "FILTER_NAME"=>"actionProductFilter",
        "ELEMENT_SORT_FIELD"=>"TIMESTAMP_X",
        "ELEMENT_SORT_ORDER"=>"desc"
    )
);
?>


<h4>Лидеры продаж</h4>
<?
global $leadersProductFilter;
$leadersProductFilter = array("PROPERTY_7"=>2);
$APPLICATION->IncludeComponent(
"bitrix:catalog.top",
    "",
    Array(
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => "2",
        "ELEMENT_COUNT"=>3,
        "FILTER_NAME"=>"leadersProductFilter",
        "ELEMENT_SORT_FIELD"=>"TIMESTAMP_X",
        "ELEMENT_SORT_ORDER"=>"desc"
    )
);
?>


</div>
