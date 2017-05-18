<?php

    require(
        $_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php"
    );
    if(!$USER->IsAdmin()){
        echo "failure\nAccess Denied\n";
        die;
    }
    require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

    header("Content-type: text/plain; charset=utf-8;");
//    header("Content-type: text/plain; charset=windows-1251;");
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
    
    CModule::IncludeModule('sale');
  
    // Получаем заказы отданный е рамках этой сессии
    $res = CSaleOrder::GetList(
        array(),
        array(
            "PROPERTY_VAL_BY_CODE_SESSION_ID"=>$session_id
        ), // Выводить только не отданные заказы
        false
    );
  
    $objOrder = new CSaleOrder;
    $arOrders = array();
    while($arrOrder = $res->GetNext()){
        // Не выводим заказы импортированные из других систем
        // if(!preg_match("#^.*\-\d+$#i",$arrOrder["ADDITIONAL_INFO"]))continue;

        $arrOrder["PROPERTIES"] = orderGetProperties($arrOrder["ID"]);

        // Если ЗНИ меняет статус заказа
        if(
            $arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]
            !=
            $arrOrder["STATUS_ID"]
        ){
            // Если отменяем заказ - ещё и бабло возвращаем
            // НО ТОЛЬКО ДЛЯ ЗАКАЗОВ БИТРИКСА
            if(
                $arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]=='AG'
                && 
                preg_match("#^.*\-\d+$#i",$arrOrder["ADDITIONAL_INFO"])
            ){
                require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php");
                require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/order.class.php");
                require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/point.class.php");
                
                $obOrder = new bxOrder();
                $resOrder = $obOrder->addEMPPoints(
                    $arrOrder["SUM_PAID"],
                    "Отмена заказа ".$arrOrder["ADDITIONAL_INFO"]." в магазине поощрений АГ",
                    $arrOrder["USER_LOGIN"]
                );
                $moneyBack = true;
                CSaleOrder::PayOrder($arrOrder["ID"],"N",true,false);
            }
            CSaleOrder::StatusOrder(
                $arrOrder["ID"],
                $arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]
            );
            /*
            eventOrderStatusSendEmail(
                $arrOrder["ID"], 
                ($ename=$arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]), 
                ($arFields = array()), 
                ($stat= $arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"])
            );
            */
        }

        // Отмечаем заказ как "отданный в рамках транзакции $session_id"
        orderSetZNI($arrOrder["ID"],'',$arrOrder["STATUS_ID"]);
        // Убираем сеансовую сессию
        orderSetSessionId($arrOrder["ID"],$session_id);
    }
    echo "success";
        
        
 
