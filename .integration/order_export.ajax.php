<?php
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    header("Content-type: text/xml; charset=utf-8;");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    
    CModule::IncludeModule('sale');
    CModule::IncludeModule('iblock');
    
    $res = CSaleOrder::GetList(
        array(),
        array()
    );
    
    
    $arOrders = array();
    while($arrOrder = $res->GetNext()){
        $order = array("Ид"=>$arrOrder["ID"]);
        $order["Номер"] = "БИТРИКС-".$arrOrder["ID"];
        $order["Дата"] = date_parse($arrOrder["DATE_INSERT"]);//date("Y-m-d");
        $order["Дата"] = date("Y-m-d",mktime(
            $order["Дата"]["hour"],$order["Дата"]["minute"],$order["Дата"]["second"],
            $order["Дата"]["month"],$order["Дата"]["day"],$order["Дата"]["year"]
        ));
        
        $resProducts = CSaleBasket::GetList(array(),array("ORDER_ID"=>$arrOrder["ID"]));
        $products = array();
        while($arProduct = $resProducts->GetNext()){
            $product = array();
            $product["Количество"] = $arProduct["QUANTITY"];
            
            $resOffer = CIBlockElement::GetList(array(), 
                array("IBLOCK_ID"=>3,"ID"=>$arProduct["PRODUCT_ID"]),false,array(),
                array("PROPERTY_CML2_LINK","XML_ID","NAME","ID")
            );
            $arOffer = $resOffer->GetNext();
            
            
            $resPrice = CPrice::GetList(array(),
                array("PRODUCT_ID"=>$arOffer["ID"]),false,array(),
                array("PRICE")
            );
            $arPrice = $resPrice->GetNext();
            
            $resCatalog = CIBlockElement::GetList(array(), 
                array("IBLOCK_ID"=>2,"ID"=>$arrOffer["PROPERTY_CML2_LINK_VALUE"]),false,array(),
                array("PROPERTY_QUANT","PROPERTY_ARTNUMBER")
            );
            $arrCatalog = $resCatalog->GetNext();
            
            $product["Ид"] = $arOffer["XML_ID"];
            $product["Наименование"] = $arOffer["NAME"];
            $product["Единица"] = $arrCatalog["PROPERTY_QUANT_VALUE"];
            $product["Артикул"] = $arrCatalog["PROPERTY_ARTNUMBER_VALUE"];
            $product["ЦенаЗаЕдиницу"] = $arPrice["PRICE"];
            $product["Продукт"] = $arOffer;
            $products[] = $product;
        }
        $order["Товары"] = $products;
        
        
        $resUser = CUser::GetByID($arrOrder["USER_ID"]);
        $arUser = $resUser->GetNext();
        $order["Телефон"] = preg_replace("#^u(\d+)$#","$1",$arUser["LOGIN"]);
        $order["ЭлектроннаяПочта"] = $arUser["EMAIL"];
        $order["Клиент"] = $arUser["LAST_NAME"]." ".$arUser["NAME"];
        
        $arSatatus = CSaleStatus::GetByID($arrOrder["STATUS_ID"]);
        $order["СостояниеЗаказа"] = $arSatatus["NAME"];
        $order["ДатаИзменения"] = date_parse($arrOrder["DATE_STATUS"]);//date("Y-m-d");
        $order["ДатаИзменения"] = date("c",mktime(
            $order["ДатаИзменения"]["hour"],$order["ДатаИзменения"]["minute"],$order["ДатаИзменения"]["second"],
            $order["ДатаИзменения"]["month"],$order["ДатаИзменения"]["day"],$order["ДатаИзменения"]["year"]
        ));
        
        $arOrders[] = $order;
    }
  
?>
<КоммерческаяИнформация xmlns="urn:1C.ru:commerceml_205" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ВерсияСхемы="2.05" ДатаФормирования="<? echo date("c");?>">
<? foreach($arOrders as $arOrder):?>
<Документ>
    <Ид><? echo $arOrder["Ид"];?></Ид>
    <Номер><? echo $arOrder["Номер"];?></Номер>
    <Дата><? echo $arOrder["Дата"];?></Дата>
    <Товары>
        <? foreach($arOrder["Товары"] as $product):?>
        <Товар>
            <Ид><? echo $product["Ид"];?></Ид>
            <Артикул><? echo $product["Артикул"];?></Артикул>
            <Наименование><? echo $product["Наименование"];?></Наименование>
            <БазоваяЕдиница Код="796" НаименованиеПолное="<? echo $product["Единица"];?>" МеждународноеСокращение="PCE"/>
            <ЗначенияРеквизитов>
            </ЗначенияРеквизитов>
            <ЦенаЗаЕдиницу><? echo $product["ЦенаЗаЕдиницу"];?></ЦенаЗаЕдиницу>
            <Количество><? echo $product["Количество"];?></Количество>
            <Единица><? echo $product["Единица"];?></Единица>
        </Товар>
        <? endforeach?>
    </Товары>
    <История>
        <Состояние>
            <ДатаИзменения><? echo $arOrder["ДатаИзменения"];?></ДатаИзменения>
            <СостояниеЗаказа><? echo $arOrder["СостояниеЗаказа"];?></СостояниеЗаказа>
            <Комментарий>Из <?
                echo $_SERVER['HTTP_HOST']
            ?></Комментарий>
            <Уведомление>Нет</Уведомление>
        </Состояние>
    </История>
    <Клиент><? echo $arOrder["Клиент"];?></Клиент>
    <Телефон><? echo $arOrder["Телефон"];?></Телефон>
    <ЭлектроннаяПочта><? echo $arOrder["ЭлектроннаяПочта"];?></ЭлектроннаяПочта>
</Документ>
<? endforeach?>
</КоммерческаяИнформация>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>


