<?php
    /**
        Выдыча в виде XML последних невыгруженных заказов
    */
    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/COrder/COrderExportCML.class.php");
    use AGShop\Order as Order;

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
    

    $objExport = new \Order\COrderExportCML;
    $arOrders = $objExport->getLastZNI($session_id);
    echo '<?xml version="1.0" encoding="windows-1251"?>'."\n";

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
    <Курс>1</Курс><? 
        if($arOrder["НомерТройки"]):
    ?>

    <НомерТройки><? echo $arOrder["НомерТройки"];?></НомерТройки><?
        endif
    ?>
    <? 
        if($arOrder["НомерОперации"]):
    ?>

    <НомерОперации><? echo $arOrder["НомерОперации"];
    ?></НомерОперации><?
        endif
    ?>

    <ХозОперация>Заказ товара</ХозОперация>
    <Роль>Продавец</Роль>
    <Сумма><? echo $arOrder["Сумма"];?></Сумма>
    <Комментарий/>
    <? if($order["ДатаИстеченияБронирования"]):?>
    <ДатаИстеченияБронирования><? 
        echo $arOrder["ДатаИстеченияБронирования"];
    ?></ДатаИстеченияБронирования>
    <? endif ?>
    <ДатаИзменения><? echo $arOrder["ДатаИзменения"];?></ДатаИзменения>
    <? if($arOrder["ДатаВыполнения"]):?>
    <ДатаВыполнения><? echo $arOrder["ДатаВыполнения"];?></ДатаВыполнения>
    <? endif ?>
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
                echo $arOrder["ЗНИ"];
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
	    <ЦенаЗаЕдиницуРублей><? echo $product["ЦенаЗаЕдиницу"];?></ЦенаЗаЕдиницуРублей>
            <Количество><? echo intval($product["Количество"]);?></Количество>
            <Сумма><? 
                echo 
                    number_format($product["Количество"]
                    *$product["ЦенаЗаЕдиницу"],2,'.','');
            ?></Сумма>
            <Склад><? echo $arOrder["Склад"];?></Склад>
            <ХарактеристикиТовара>
                <? foreach($product["ХарактеристикиТовара"] as $arProps):?>
                <ХарактеристикаТовара>
                    <!--
                    <Наименование><? 
                        echo $arProps["Наименование"]
                    ?></Наименование>
                    <Значение><? echo trim($arProps["Значение"])?></Значение>
                    -->
                    <<? 
                        echo trim($arProps["Наименование"])
                    ?>><? echo $arProps["Значение"]?>
                    </<? 
                        echo trim($arProps["Наименование"])
                    ?>>
                </ХарактеристикаТовара>
                <? endforeach?>
            </ХарактеристикиТовара>
        </Товар>
        <? endforeach?>
    </Товары>
</Документ>
<? endforeach?>
</КоммерческаяИнформация>
<?
    // Снимаем блокировку
    $objExport->orderQueryResetLock();
    require(
    $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"
);?>
