<?
    /**
        Отчет для удаления дублей заказов
    */

$arResult = [
    "COLS"=>[
        [
            "VALUE"=>"Пользовательь"
        ],
        [
            "VALUE"=>"Дата блокировки"
        ]
    ],
    "ROWS"=>[],
    "CELLS"=>[]
];


$sQuery = "
    SELECT
        `ID`
    FROM
        `b_sale_order_props`
    WHERE 
        `CODE`='TROIKA_TRANSACT_ID'
";
$arProp = $DB->Query($sQuery)->Fetch($sQuery);
$nPropId = $arProp["ID"];

$sDate = date("Y-m-d H:i:s",time()-1*24*60*60);

$sQuery = "
    SELECT
        `order`.`ID` as `ID`,
        `order`.`ADDITIONAL_INFO`,
        `prop`.`VALUE` as `TRANSACT`,
        `order`.`DATE_INSERT`,
        `lock`.`USER_ID`,
        `user`.`LOGIN` as `USER`
    FROM
        `b_sale_order` as `order`
            LEFT JOIN
        `b_sale_order_props_value` as `prop`
            ON
            `order`.`ID`=`prop`.`ORDER_ID`
            LEFT JOIN
        `index_lock` as `lock`
            ON
            `lock`.`ORDER_ID`=`order`.`ID`
            LEFT JOIN
        `b_user` as `user`
            ON
            `order`.`USER_ID`=`user`.`ID`
    WHERE
        `lock`.`ID` IS NULL
        AND
        `order`.`DATE_INSERT`>'$sDate'
        AND 
       `prop`.`ORDER_PROPS_ID` = $nPropId
    ORDER BY
        `order`.`DATE_INSERT` DESC
    LIMIT
        1000
";

$resOrders = $DB->Query($sQuery);

$nRowCount = 1;
while($arOrder = $resOrders->Fetch()){
    $arResult["ROWS"][$nRowCount] = [
        "VALUE" =>  $arOrder["ADDITIONAL_INFO"],
        "URL"   =>  "/partners/orders/".$arOrder["ID"]."/"
    ];
    $arResult["CELLS"][$nRowCount][0]=$arOrder["USER"];
    $arResult["CELLS"][$nRowCount][1]=$arOrder["DATE_INSERT"];
    $arResult["CELLS"][$nRowCount][2]=$arOrder["USER"];
        
    $nRowCount++;
//    echo "<pre>";
//    print_r($arOrder);
//    echo "</pre>";
}


