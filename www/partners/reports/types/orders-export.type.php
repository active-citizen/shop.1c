<?
    /**
        Отчет для удаления дублей заказов
    */

if(
    isset($_POST["from"])
    &&
    isset($_POST["to"])
){
    $tmp = date_parse($_POST["from"]);
    if($tmp['error_count']){
        ShowMessage([
            "TYPE"=>"ERROR",
            "MESSAGE"=> "Ошибка парсинга даты начала"
        ]);
        die;
    }
    $sStartDate = date("Y-m-d 00:00:00",mktime(
        0,0,0,$tmp["month"],$tmp["day"],$tmp["year"]
    ));

    $tmp = date_parse($_POST["to"]);
    if($tmp['error_count']){
        ShowMessage([
            "TYPE"=>"ERROR",
            "MESSAGE"=> "Ошибка парсинга даты конца"
        ]);
        die;
    }
    $sEndDate = date("Y-m-d 23:59:59",mktime(
        23,59,59,$tmp["month"],$tmp["day"],$tmp["year"]
    ));

    $sXML = '<КоммерческаяИнформация xmlns="urn:1C.ru:commerceml_205" '
        .'xmlns:xs="http://www.w3.org/2001/XMLSchema" '
        .'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
        .'ВерсияСхемы="2.05" ДатаФормирования="2017-10-17T15:13:42">
';

    $nPricePropId = 20;
    $sQuery = "
        SELECT
            `order`.`XML_ID` as `XML_ID`,
            `order`.`ADDITIONAL_INFO` as `ORDER_NUM`,
            DATE(`order`.`DATE_INSERT`) as `DATE`,
            TIME(`order`.`DATE_INSERT`) as `TIME`,
            CONCAT(DATE(`order`.`DATE_UPDATE`),'T',TIME(`order`.DATE_UPDATE)) as `DATE_UPDATE`,
            `close_date`.`VALUE` as `CLOSE_DATE`,
            `product`.`XML_ID` as `PRODUCT_XML_ID`,
            `product`.`NAME` as `PRODUCT_NAME`,
            `basket`.`QUANTITY` as `QUANTITY`,
            `price`.`VALUE_NUM` as `PRICE`,
            `status`.`NAME` as `STATUS`,
            `store`.`XML_ID` as `STORE_XML_ID`,
            CONCAT(`user`.`LAST_NAME`,' ',`user`.`NAME`) as `FIO`,
            LEFT(`user`.`LOGIN`,11) as `PHONE`,
            `user`.`EMAIL` as `EMAIL`
        FROM
            `b_sale_order` as `order`
                LEFT JOIN
            `b_sale_order_props_value` as `close_date`
                ON `close_date`.`ORDER_ID`=`order`.`ID`
                    AND `close_date`.`NAME`='Дата истечения бронирования'
                LEFT JOIN
            `b_sale_basket` as `basket`
                ON `order`.`ID`=`basket`.`ORDER_ID`
                LEFT JOIN
            `b_iblock_element_property` as `offer`
                ON 
                    `offer`.`IBLOCK_PROPERTY_ID`=".CML2_LINK_PROPERTY_ID."
                    AND `offer`.`IBLOCK_ELEMENT_ID`=`basket`.`PRODUCT_ID` 
                LEFT JOIN
            `b_iblock_element` as `product`
                ON
                    `offer`.`VALUE_NUM`=`product`.`ID`
                LEFT JOIN
            `b_iblock_element_property` as `price`
                ON
                    `price`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                    AND `price`.`IBLOCK_PROPERTY_ID` = $nPricePropId
                LEFT JOIN
            `b_sale_status_lang` as `status`
                ON
                    `order`.`STATUS_ID`=`status`.`STATUS_ID` 
                    AND `status`.`LID`='ru'
                LEFT JOIN
            `b_catalog_store` as `store`
                ON
                    `order`.`STORE_ID`=`store`.`ID`
                LEFT JOIN
            `b_user` as `user`
                ON
                    `user`.`ID`=`order`.`USER_ID`
                
        WHERE
            `order`.`DATE_INSERT` BETWEEN '$sStartDate' AND '$sEndDate'
    ";


    $resOrder = $DB->Query($sQuery);
    while($arOrder=$resOrder->Fetch()){
        $sXML .= '
	<Документ>
		<Ид>'.$arOrder["XML_ID"].'</Ид>
		<Номер>'.$arOrder["ORDER_NUM"].'</Номер>
		<Дата>'.$arOrder["DATE"].'</Дата>
		<ДатаИстеченияБронирования>'.$arOrder["CLOSE_DATE"].'T23:59:59</ДатаИстеченияБронирования>
		<Время>'.$arOrder["TIME"].'</Время>
		<Товары>
			<Товар>
				<Ид>'.$arOrder["PRODUCT_XML_ID"].'</Ид>'
/*		<Артикул>parking</Артикул>*/
				.'
                <Наименование>'.convertToXMLString($arOrder["PRODUCT_NAME"]).'</Наименование>
				<ЦенаЗаЕдиницу>'.intval($arOrder["PRICE"]).'</ЦенаЗаЕдиницу>
				<Количество>'.intval($arOrder["QUANTITY"]).'</Количество>
				<Единица>100 парковочных баллов</Единица>
			</Товар>
		</Товары>
		<История>
			<Состояние>
				<ДатаИзменения>'.$arOrder["DATE_UPDATE"].'</ДатаИзменения>
				<СостояниеЗаказа>'.$arOrder["STATUS"].'</СостояниеЗаказа>
				<Комментарий/>
				<Уведомление>Нет</Уведомление>
				<Склад>'.$arOrder["STORE_XML_ID"].'</Склад>
			</Состояние>
		</История>
		<Клиент>'.convertToXMLString($arOrder["FIO"]).'</Клиент>
		<Телефон>'.$arOrder["PHONE"].'</Телефон>
		<ЭлектроннаяПочта>'.$arOrder["EMAIL"].'</ЭлектроннаяПочта>
	</Документ>
';
    }

    $sXML = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'
        ."\n".$sXML;
    $sXML .= "\n</КоммерческаяИнформация>";

    $sUrl = "/upload/xml/orders_".date("Y-m-d-H-i-s").".xml";
    $fd = fopen($_SERVER["DOCUMENT_ROOT"].$sUrl,"w");
    fwrite($fd, $sXML);
    fclose($fd);
    ?><a href="<?= $sUrl?>" target="_blank">Скачать выгрузку</a><?
}


$arResult = [
    "COLS"=>[
        [
            "VALUE"=>"Дата"
        ],
    ],
    "ROWS"=>[
        [
            "VALUE"=> 'Дата добавления от:'
        ],
        [
            "VALUE"=> 'Дата добавления до:'
        ]
    ],
    "CELLS"=>[
        [
            '<input type="text" name="from" class="form-control form-date" value="'.
                date("d.m.Y",mktime(0,0,0,date('m')-1,date("d"),date("Y")))
            .'">'
        ],
        [
            '<input type="text" name="to" class="form-control form-date" value="'.
                date("d.m.Y")
            .'">'
        ],
    ]
];

function convertToXMLString($sText){
    $sText = htmlspecialchars($sText);
    return $sText;
}

