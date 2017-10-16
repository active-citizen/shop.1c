<?
    /**
        Отчет для удаления дублей заказов
    */

if(
    isset($_POST["fordelete"]) 
    && $_POST["fordelete"]
    && is_array($_POST["fordelete"])


){
    foreach($_POST["fordelete"] as $sOrderNum=>$sOn){
        $sQuery = "
            SELECT
                `a`.`ID`,
                `a`.`ADDITIONAL_INFO`,
                `b`.`VALUE` 
            FROM
                `b_sale_order` as `a`
                    LEFT JOIN
                `b_sale_order_props_value` as `b`
                    ON
                    `a`.`ID`=`b`.`ORDER_ID` AND `b`.`NAME`='Дата истечения бронирования'
            WHERE
                `a`.`ADDITIONAL_INFO`='".$DB->ForSql($sOrderNum)."'
            ORDER BY
                `b`.`VALUE` ASC,`a`.`ID` ASC
        ";
        $arRes = $DB->Query($sQuery);
        $arOrders = [];
        while($arOrder = $arRes->Fetch())
            $arOrders[] = $arOrder;
        
        array_pop($arOrders);
        foreach($arOrders as $arOrder){
            $sQuery = "
                DELETE FROM `b_sale_order_props_value` WHERE `ORDER_ID`="
                .$arOrder["ID"];
            $DB->Query($sQuery);
            $sQuery = "
                DELETE FROM `b_sale_order` WHERE `ID`=".$arOrder["ID"]."
            ";
            $DB->Query($sQuery);
        }
    }
    header("Location: /partners/reports/?type=order-doubles");

}

$arStatuses = [
    "F"=>"Выполнен",
    "N"=>"В работе"
];

$arResult = [
    "COLS"=>[
        [
            "VALUE"=>"Экземляров"
        ],
        [
            "VALUE"=>"Статус"
        ],
        [
            "VALUE"=>"Добавлено"
        ],
        [
            "VALUE"=>"Удалить"
        ]
    ],
    "ROWS"=>[],
    "CELLS"=>[]
];

$sQuery = "
    SELECT 
        ID,
        ADDITIONAL_INFO,
        STATUS_ID as `STATUS_ID`,
        COUNT(ID) as `COUNT`,
        DATE_FORMAT(DATE_INSERT,'%d.%m.%Y') as `DATE_INSERT`
    FROM 
        `b_sale_order` 
    GROUP BY 
        `ADDITIONAL_INFO`,`STATUS_ID`
    ORDER BY
        `COUNT` DESC,
        `ID` ASC
    LIMIT 100
";

//echo $sQuery;
$res = $DB->Query($sQuery);

$nRowId = 0;
while($arRow = $res->Fetch()){
    if($arRow["COUNT"]<2)continue;
    $arResult["ROWS"][] = [
        "VALUE" =>  $arRow["ADDITIONAL_INFO"]?$arRow["ADDITIONAL_INFO"]:"UNKNOWN",
        "URL"   =>  "/partners/orders/".$arRow["ID"]."/"
    ];
    $arResult["CELLS"][$nRowId][0] = $arRow["COUNT"];
    $arResult["CELLS"][$nRowId][1] = $arStatuses[$arRow["STATUS_ID"]];
    $arResult["CELLS"][$nRowId][2] = $arRow["DATE_INSERT"];
    $arResult["CELLS"][$nRowId][3] = '<input type="checkbox" name="fordelete['
        .$arRow["ADDITIONAL_INFO"].']"'. $arRow["ADDITIONAL_INFO"].">";
    
    $nRowId++;
}

//echo "<pre>";
//print_r($res);
