<?php
    /**
        Выдыча в виде XML последних невыгруженных заказов
    */
    require("includes/datafilter.lib.php");
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");
    header("Content-type: text/plain; charset=windows-1251;");
    if(!$USER->IsAdmin()){
        echo "Failed\nAccess denied";
        die;
    }

    // Получаем DI сессии обмена
    $session_id = 
        isset($_COOKIE['PHPSESSID'])
        ?
        $_COOKIE['PHPSESSID']
        :
        "";
    $session_id = 
        !$session_id && isset($_POST['PHPSESSID'])
        ?
        $_POST['PHPSESSID']
        :
        $session_id;
    $session_id = 
        !$session_id && isset($_GET['PHPSESSID'])
        ?
        $_GET['PHPSESSID']
        :
        $session_id;
    if(!preg_match("/^[\d\w]+$/",$session_id)){
        echo "Failed\nPHPSESSID incorrect";
        die;
    }
   

    echo '<?xml version="1.0" encoding="windows-1251"?>';
    
    CModule::IncludeModule('sale');
    CModule::IncludeModule('iblock');
   
    $arOrderses = array();
    $res = CSaleOrder::GetList(
        array("ID"=>"ASC"),
        array(
            //">ID"=>783
            //"DATE_UPDATE"=>""
            "PROPERTY_VAL_BY_CODE_CHANGE_REQUEST"=>"AA" 
        ), // Выводить только не отданные заказы
        false
//        ,array("nTopCount"=>ORDER_EXPORT_QUANT)
    );
    while(
        count($arOrderses)<ORDER_EXPORT_QUANT 
        && $arOrder = $res->GetNext()
    )$arOrderses[] = $arOrder;
    $res = CSaleOrder::GetList(
        array("ID"=>"ASC"),
        array(
            //">ID"=>783
            //"DATE_UPDATE"=>""
            "PROPERTY_VAL_BY_CODE_CHANGE_REQUEST"=>"AG" 
        ), // Выводить только не отданные заказы
        false
//        ,array("nTopCount"=>ORDER_EXPORT_QUANT)
    );
    while(
        count($arOrderses)<ORDER_EXPORT_QUANT 
        && $arOrder = $res->GetNext()
    )$arOrderses[] = $arOrder;
    $res = CSaleOrder::GetList(
        array("ID"=>"ASC"),
        array(
            //">ID"=>783
            //"DATE_UPDATE"=>""
            "PROPERTY_VAL_BY_CODE_CHANGE_REQUEST"=>"F" 
        ), // Выводить только не отданные заказы
        false
//        ,array("nTopCount"=>ORDER_EXPORT_QUANT)
    );
    while(
        count($arOrderses)<ORDER_EXPORT_QUANT 
        && $arOrder = $res->GetNext()
    )$arOrderses[] = $arOrder;
    $res = CSaleOrder::GetList(
        array("ID"=>"ASC"),
        array(
            //">ID"=>783
            //"DATE_UPDATE"=>""
            "PROPERTY_VAL_BY_CODE_CHANGE_REQUEST"=>"N" 
        ), // Выводить только не отданные заказы
        false
//        ,array("nTopCount"=>ORDER_EXPORT_QUANT)
    );
    while(
        count($arOrderses)<ORDER_EXPORT_QUANT 
        && $arOrder = $res->GetNext()
    )$arOrderses[] = $arOrder;
    $res = CSaleOrder::GetList(
        array("ID"=>"ASC"),
        array(
            //">ID"=>783
            //"DATE_UPDATE"=>""
            "PROPERTY_VAL_BY_CODE_CHANGE_REQUEST"=>"AI" 
        ), // Выводить только не отданные заказы
        false
//        ,array("nTopCount"=>ORDER_EXPORT_QUANT)
    );
    while(
        count($arOrderses)<ORDER_EXPORT_QUANT 
        && $arOrder = $res->GetNext()
    )$arOrderses[] = $arOrder;
     
  

    /*
    $arPropGroup = CSaleOrderPropsGroup::GetList(
        array(),
        $arPropGroupFilter = array(),
        false,
        array("nTopCount"=>1)
    )->GetNext();
    */
    $nPropGroup = 5;//$arPropGroup["ID"];

    $objOrder = new CSaleOrder;
    $arOrders = array();
    foreach($arOrderses as $arrOrder){
        $resPropValues = CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            $arF = array(
                    "ORDER_ID"       => $arrOrder["ID"],
                    "PERSON_TYPE_ID" => 1,
                    "PROPS_GROUP_ID" => $nPropGroup,
                    "CODE"=>"CHANGE_REQUEST"
                ),
            false,
            false,
            array("ID","CODE","NAME")
        );
        $arrOrder["PROPERTIES"] = array();
        while($arProp = $resPropValues->GetNext()){
            $arrOrder["PROPERTIES"][$arProp["CODE"]] = 
                CSaleOrderPropsValue::GetList(
                    array(),
                    $arFilterProp = array(
                        "ORDER_ID"=>$arrOrder["ID"],
                        "ORDER_PROPS_ID"=>$arProp["ID"]
                    )
                )->GetNext();
        }

        // Не выводим заказы импортированные из других систем
        // if(!preg_match("#^.*\-\d+$#i",$arrOrder["ADDITIONAL_INFO"]))continue;
        // Отмечаем заказ как "отданный в рамках сессии обмена 
        orderSetSessionId($arrOrder["ID"],$session_id);
         /*
        CSaleOrder::Update($arrOrder["ID"], array(
            "COMMENTS"=>$session_id,
            "DATE_UPDATE"=>Date(
                CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))
             )
        ));
        */
        
        $order = array("Ид"=>$arrOrder["ID"]);
        $order["Номер"] = mb_convert_encoding(
            $arrOrder["ADDITIONAL_INFO"],"cp1251","UTF-8"
        );
        
        $order["Дата"] = date_parse($arrOrder["DATE_INSERT"]);
        $order["Дата"] = date("Y-m-d",mktime(
            $order["Дата"]["hour"],$order["Дата"]["minute"],
            $order["Дата"]["second"],
            $order["Дата"]["month"],$order["Дата"]["day"],
            $order["Дата"]["year"]
        ));

        $order["ДатаИстеченияБронирования"] = date_parse(
            $arrOrder["DATE_INSERT"]
        );
        $order["ДатаИстеченияБронирования"] = date("Y-m-d",24*60*60+mktime(
            $order["ДатаИстеченияБронирования"]["hour"],
            $order["ДатаИстеченияБронирования"]["minute"],
            $order["ДатаИстеченияБронирования"]["second"],
            $order["ДатаИстеченияБронирования"]["month"],
            $order["ДатаИстеченияБронирования"]["day"],
            $order["ДатаИстеченияБронирования"]["year"]
        ));

        
        $order["ДатаИзменения"] = date_parse($arrOrder["DATE_UPDATE"]);
        $order["ДатаИзменения"] = date("Y-m-d H:i:s",mktime(
            $order["ДатаИзменения"]["hour"],
            $order["ДатаИзменения"]["minute"],
            $order["ДатаИзменения"]["second"],
            $order["ДатаИзменения"]["month"],
            $order["ДатаИзменения"]["day"],
            $order["ДатаИзменения"]["year"]
        ));
        
        $order["Время"] = date_parse($arrOrder["DATE_INSERT"]);
        $order["Время"] = date("H:i:s",mktime(
            $order["Время"]["hour"],$order["Время"]["minute"],
            $order["Время"]["second"],
            $order["Время"]["month"],$order["Время"]["day"],
            $order["Время"]["year"]
        ));

        $resProducts = CSaleBasket::GetList(
            array(),array("ORDER_ID"=>$arrOrder["ID"])
        );
        $products = array();
        while($arProduct = $resProducts->GetNext()){
            $product = array();
            $product["Количество"] = $arProduct["QUANTITY"];
            
            $resOffer = CIBlockElement::GetList(array(), 
                array(
                    "IBLOCK_ID"=>3,
                    "ID"=>$arProduct["PRODUCT_ID"]
                ),false,array("nTopCount"=>1),
                array("PROPERTY_CML2_LINK","XML_ID","NAME","ID")
            );
            $arOffer = $resOffer->GetNext();
            
            $resProps = CIBlockElement::GetProperty(3, $arOffer["ID"]);
            $product["ХарактеристикиТовара"] = array();
            while($arrProp = $resProps->GetNext()){
                if(
                    !preg_match("#^PROP1C_.*#",$arrProp["CODE"]) 
                    || 
                    !$arrProp["VALUE"]
                )continue;
                $product["ХарактеристикиТовара"][] = array(
                    "Наименование"  =>  
                        mb_convert_encoding(
                            dataNormalize($arrProp["NAME"]),"cp1251","utf-8"
                        ),
                    "Значение"      =>  
                        mb_convert_encoding(
                            dataNormalize($arrProp["VALUE_ENUM"]),"cp1251","utf-8"
                        )
                );
            }
            
            $resPrice = CPrice::GetList(array(),
                array("PRODUCT_ID"=>$arOffer["ID"]),false,array("nTopCount"=>1),
                array("PRICE")
            );
            $arPrice = $resPrice->GetNext();
            
            $resCatalog = CIBlockElement::GetList(array(), 
                array(
                    "IBLOCK_ID"=>CATALOG_IB_ID,
                    "ID"=>$arrOffer["PROPERTY_CML2_LINK_VALUE"]),
                    false,
                    array("nTopCount"=>1),
                    array("PROPERTY_QUANT","PROPERTY_ARTNUMBER")
            );
            $arrCatalog = $resCatalog->GetNext();
            
            $product["Ид"] = $arOffer["XML_ID"];
            $product["Наименование"] = 
                mb_convert_encoding(dataNormalize($arOffer["NAME"]),"cp1251","utf-8");
            $product["Единица"] = 
                mb_convert_encoding(
                    dataNormalize($arrCatalog["PROPERTY_QUANT_VALUE"]),"cp1251","utf-8"
                );
            $product["Артикул"] = $arrCatalog["PROPERTY_ARTNUMBER_VALUE"];
            $product["ЦенаЗаЕдиницу"] = $arPrice["PRICE"];
            $product["Продукт"] = $arOffer;
            $products[] = $product;
        }

        //$order["Сумма"] = $arrOrder["SUM_PAID"];
        $order["Сумма"] = 0;
        foreach($products as $product)
            $order["Сумма"] += $product["Количество"]*$product["ЦенаЗаЕдиницу"];
        
        $resUser = CUser::GetByID($arrOrder["USER_ID"]);
        $arUser = $resUser->GetNext();
        
        $resStore = CCatalogStore::GetList(
            array(),
            array("ID"=>$arrOrder["STORE_ID"]),
            false,
            array("nTopCount"=>1)
        );
        $arStore = $resStore->GetNext();
        
        
        $order["Телефон"] = preg_replace("#^u(\d+)$#","$1",$arUser["LOGIN"]);
        $order["ЭлектроннаяПочта"] = $arUser["EMAIL"];
        $order["Клиент"] = 
            mb_convert_encoding(
                dataNormalize($arUser["LAST_NAME"]." ".$arUser["NAME"]),"cp1251","utf-8"
            );
        $order["Имя"] = mb_convert_encoding(
            dataNormalize($arUser["NAME"]),"cp1251","utf-8"
        );
        $order["Фамилия"] = mb_convert_encoding(
            dataNormalize($arUser["LAST_NAME"]),"cp1251","utf-8"
        );
        $order["Город"] = mb_convert_encoding(
            dataNormalize($arUser["PERSONAL_CITY"]),"cp1251","utf-8"
        );
        $order["Склад"] = $arStore["XML_ID"];
        
        $arSatatus =
        CSaleStatus::GetByID($arrOrder["STATUS_ID"]);
        $order["КодСостоянияЗаказа"] = $arrOrder["STATUS_ID"];
        $order["СостояниеЗаказа"] = mb_convert_encoding(
            $arSatatus["NAME"],"cp1251","utf-8"
        );
        $arSatatus =
        CSaleStatus::GetByID($arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]);
        $order["ЗНИ"] = mb_convert_encoding(
            $arSatatus["NAME"],"cp1251","utf-8"
        );

        $order["СостояниеЗаказа"] = $order["ЗНИ"];

        $order["Товары"] = $products;
        $arOrders[] = $order;
    }
?><КоммерческаяИнформация xmlns="urn:1C.ru:commerceml_205" <? 
?>xmlns:xs="http://www.w3.org/2001/XMLSchema" <?
?>xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" <?
?>ВерсияСхемы="2.05" ДатаФормирования="<? echo date("c");?>">
<? foreach($arOrders as $arOrder):?>
<Документ>
    <Ид><? echo $arOrder["Ид"];?></Ид>
    <Номер><? echo $arOrder["Номер"];?></Номер>
    <Дата><? echo $arOrder["Дата"];?></Дата>
    <Время><? echo $arOrder["Время"];?></Время>
    <Валюта>руб.</Валюта>
    <Курс>1</Курс>
    <ХозОперация>Заказ товара</ХозОперация>
    <Роль>Продавец</Роль>
    <Сумма><? echo $arOrder["Сумма"];?></Сумма>
    <Комментарий/>
    <ДатаИстеченияБронирования><? 
        echo $arOrder["ДатаИстеченияБронирования"];
    ?></ДатаИстеченияБронирования>
    <ДатаИзменения><? echo $arOrder["ДатаИзменения"];?></ДатаИзменения>
    <СтатусЗаказа><? echo $arOrder["СостояниеЗаказа"];?></СтатусЗаказа>
    <ЗапросНаИзменение><? 
        echo $arOrder["ЗНИ"];
    ?></ЗапросНаИзменение>
    <Контрагенты>
        <Контрагент>
            <Ид>0#<? echo $arOrder["ЭлектроннаяПочта"];?></Ид>
            <Наименование><? echo $arOrder["Клиент"];?></Наименование>
            <Роль>Покупатель</Роль>
            <ПолноеНаименование><? 
                echo $arOrder["Клиент"];
            ?></ПолноеНаименование>
            <Фамилия><? echo $arOrder["Фамилия"];?></Фамилия>
            <Имя><? echo $arOrder["Имя"];?></Имя>
            <Адрес>
                <Представление><? echo $arOrder["Город"];?></Представление>
            </Адрес>
            <Контакты>
                <Контакт>
                    <Тип>ТелефонРабочий</Тип>
                    <Значение><? echo $arOrder["Телефон"];?></Значение>
                </Контакт>
                <Контакт>
                    <Тип>Почта</Тип>
                    <Значение><? echo $arOrder["ЭлектроннаяПочта"];?></Значение>
                </Контакт>
            </Контакты>
        </Контрагент>
    </Контрагенты>
    <История>
        <Состояние>
            <ДатаИзменения><? echo $arOrder["ДатаИзменения"];?></ДатаИзменения>
            <СостояниеЗаказа><? 
                echo $arOrder["СостояниеЗаказа"];
            ?></СостояниеЗаказа>
            <ЗапросНаИзменение><? 
                echo $arOrder["СостояниеЗаказа"];
            ?></ЗапросНаИзменение>
            <Комментарий/>
            <Уведомление>Нет</Уведомление>
        </Состояние>
    </История>
    <Товары>    
        <? foreach($arOrder["Товары"] as $product):?>
        <Товар>
            <Ид><? echo $product["Ид"];?></Ид>
            <Наименование><? echo $product["Наименование"];?></Наименование>
            <ЦенаЗаЕдиницу><? echo $product["ЦенаЗаЕдиницу"];?></ЦенаЗаЕдиницу>
            <ЦенаЗаЕдиницуРублей><? 
                echo $product["ЦенаЗаЕдиницу"];
            ?></ЦенаЗаЕдиницуРублей>
            <Количество><? echo $product["Количество"];?></Количество>
            <Сумма><? 
                echo 
                    number_format($product["Количество"]
                    *$product["ЦенаЗаЕдиницу"],2,'.',' ');
            ?></Сумма>
            <Склад><? echo $arOrder["Склад"];?></Склад>
            <ХарактеристикиТовара>
                <? foreach($product["ХарактеристикиТовара"] as $arProps):?>
                <ХарактеристикаТовара>
                    <Наименование><? 
                        echo $arProps["Наименование"]
                    ?></Наименование>
                    <Значение><? echo $arProps["Значение"]?></Значение>
                </ХарактеристикаТовара>
                <? endforeach?>
            </ХарактеристикиТовара>
        </Товар>
        <? endforeach?>
    </Товары>
</Документ>
<? endforeach?>
</КоммерческаяИнформация>
<?require(
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"
);?>


