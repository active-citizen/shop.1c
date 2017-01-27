<?
    // Библиотаке для склонения баллов, и дней
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/rus.lib.php");
    // Библиотека для отправки почты через SMTP
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/mail/common.php");

    if(!preg_match("#^/admin#",$_SERVER["REQUEST_URI"])){
        define("ORDERS_EXCHANGE_ADMIN_MODE", true);
    }
    else{
        define("ORDERS_EXCHANGE_ADMIN_MODE", false);
    }

    // Если режим обмена заказами - глушим отправку письма при создании заказа
    if(ORDERS_EXCHANGE_ADMIN_MODE){
        AddEventHandler("sale", "OnOrderNewSendEmail", "eventOrderNewSendEmail_dummy");
    }
    else{
        AddEventHandler("sale", "OnOrderNewSendEmail", "eventOrderNewSendEmail_normal");
    }
    
    // Глушим отправку письма при оплате
    AddEventHandler("sale", "OnOrderPaySendEmail", "eventOrderPaySendEmail");
    // Глушим отправку письма при отмене
    AddEventHandler("sale", "OnOrderCancelSendEmail", "eventOnOrderCancelSendEmail");
    // Глушим отправку письма при доставке
    AddEventHandler("sale", "OnOrderDeliverSendEmail", "eventOnOrderDeliverSendEmail");
    // Глушим отправку письма напоминалку
    AddEventHandler("sale", "OnOrderRemindSendEmail", "eventOrderRemindSendEmail");
    // Эти две фишни тоже на всякий случай баним
    AddEventHandler("sale", "OnOrderRecurringSendEmail", "eventOrderRecurringSendEmail");
    AddEventHandler("sale", "OnOrderRecurringCancelSendEmail", "eventOrderRecurringCancelSendEmail");
    
    
    
    // Назначаем обработчик формирования письма о смене статуса заказа
    AddEventHandler("sale", "OnSaleStatusEMail", "eventSaleStatusEMail");
    // Назначаем обработчик отправки письма о смене статуса заказа
    AddEventHandler("sale", "OnOrderStatusSendEmail", "eventOrderStatusSendEmail");
    
    
    function eventOrderStatusSendEmail($orderId, &$eventName, &$arFields, $orderStatus){
        $fd = fopen($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/1.txt","w");
        fwrite($fd, print_r($orderStatus,1)."\n");
        fwrite($fd, print_r($orderId,1)."\n");
        fwrite($fd, print_r($arFields,1)."\n");
        fclose($fd);
    }
    

    function eventSaleStatusEMail($orderId, $orderStatus){
    }

    function eventOrderNewSendEmail_normal($orderID, &$eventName, &$arFields){
        echo "\Отправка письма при изменении статуса \n";
        print_r($orderStatus);
        print_r($orderId);
        die;
    }

    // Функции пустышки для предотвращения отправки писем при собятиях
    function eventOrderNewSendEmail_dummy($orderID, &$eventName, &$arFields){return false;}
    function eventOrderPaySendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOnOrderCancelSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOnOrderDeliverSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOrderRemindSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOrderRecurringCancelSendEmail($orderID, &$eventName, &$arFields){return false;}
    function eventOrderRecurringSendEmail($orderID, &$eventName, &$arFields){return false;}

?>
