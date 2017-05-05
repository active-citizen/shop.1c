<?
require(
    $_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php"
);

header("Content-type: text/plain");

$resOrder = CSaleOrder::GetList(
    array("ID"=>"DESC"),
    $arFilter = array(
        "ID"=>$_REQUEST["id"]
    ),
    false,
    array(
        "nTopCount"=>1
    ),
    array() 
);

print_r($arFilter);
while($arOrder = $resOrder->Fetch()){
//  print_r($arOrder);
    orderPropertiesUpdate($arOrder["ID"],true);
}



