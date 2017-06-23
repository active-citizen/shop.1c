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
    CModule::IncludeModule('catalog');
  
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

                // Высчисляем по картине суммарную стоимость заказа
                $sql = "select SUM(`PRICE`*`QUANTITY`) as `TOTAL_SUM` from b_sale_basket WHERE
                ORDER_ID='".$arrOrder["ID"]."' GROUP BY ORDER_ID";
                $arSum = $DB->Query($sql)->Fetch();
                $arrOrder["SUM_PAID"] =
                    isset($arSum["TOTAL_SUM"])?floatval($arSum["TOTAL_SUM"]):0;
                

                $obOrder = new bxOrder();
                print_r($arrOrder);
                $resOrder = $obOrder->addEMPPoints(
                    $arrOrder["SUM_PAID"],
                    "Отмена заказа ".$arrOrder["ADDITIONAL_INFO"]." в магазине поощрений АГ",
                    $arrOrder["USER_LOGIN"]
                );
                $moneyBack = true;
                CSaleOrder::PayOrder($arrOrder["ID"],"N",true,false);
            }

            // Если отменяем заказ - возвращаем товар на склад
            // НО ТОЛЬКО ДЛЯ ЗАКАЗОВ БИТРИКСА
            if(
                $arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]=='AG'
                && 
                preg_match("#^.*\-\d+$#i",$arrOrder["ADDITIONAL_INFO"])
            ){
                // Получаем список товаров к заказу
                $sql = "SELECT PRODUCT_ID,QUANTITY FROM `b_sale_basket` WHERE
                `ORDER_ID`=".intval($arrOrder["ID"]);
                $res = $DB->Query($sql);
                while($arProduct = $res->Fetch()){
                    $nQuantity = $arProduct["QUANTITY"];
                    $nProductId = $arProduct["PRODUCT_ID"];
                    $nStoreId = $arrOrder["STORE_ID"];
                    // Смотрим сколько этого товара на складе
                    $nCQuantity = 0;
                    // Если записей с остатком нет - пропустить его уменьшение
                    if($arStoreProduct = CCatalogStoreProduct::GetList(
                        array(),
                        $arStoreProductFilter = array(
                            "PRODUCT_ID"=>  $nProductId,
                            "STORE_ID"  =>  $nStoreId
                        ),
                        false,
                        array("nTopCount"=>1),
                        array("ID","PRODUCT_ID","AMOUNT")
                    )->GetNext()){
                        $nCQuantity = $arStoreProduct["AMOUNT"];
                    }
                    else{
                        continue;
                    }
                
                    // Устанавливаем новое значение остатка
                    if(!CCatalogStoreProduct::Update(
                        $arStoreProduct["ID"],
                        $arF = array(
                            "AMOUNT"              =>  $nCQuantity+$nQuantity
                    ))){
                        print_r($objCCatalogStoreProduct);
                        die;
                    }
                    
                }

            }

            // Если подтверждаем принятие заказа в работу - сниманием единицу со
            //склада
            if(
                $arrOrder["PROPERTIES"]["CHANGE_REQUEST"]["VALUE"]=='N'
                && 
                preg_match("#^.*\-\d+$#i",$arrOrder["ADDITIONAL_INFO"])
            ){
                // Получаем список товаров к заказу
                $sql = "SELECT PRODUCT_ID,QUANTITY FROM `b_sale_basket` WHERE
                `ORDER_ID`=".intval($arrOrder["ID"]);
                $res = $DB->Query($sql);
                while($arProduct = $res->Fetch()){
                    $nQuantity = $arProduct["QUANTITY"];
                    $nProductId = $arProduct["PRODUCT_ID"];
                    $nStoreId = $arrOrder["STORE_ID"];
                    // Смотрим сколько этого товара на складе
                    $nCQuantity = 0;
                    // Если записей с остатком нет - пропустить его
                    if($arStoreProduct = CCatalogStoreProduct::GetList(
                        array(),
                        $arStoreProductFilter = array(
                            "PRODUCT_ID"=>  $nProductId,
                            "STORE_ID"  =>  $nStoreId
                        ),
                        false,
                        array("nTopCount"=>1),
                        array("ID","PRODUCT_ID","AMOUNT")
                    )->GetNext()){
                        $nCQuantity = $arStoreProduct["AMOUNT"];
                    }
                    else{
                        continue;
                    }
               
                    // Устанавливаем новое значение остатка
                    if(!CCatalogStoreProduct::Update(
                        $arStoreProduct["ID"],
                        $arF = array(
                            "AMOUNT"              =>  
                                $nCQuantity-$nQuantity>=0
                                ?
                                $nCQuantity-$nQuantity
                                :
                                0
                    ))){
                        print_r($objCCatalogStoreProduct);
                        die;
                    }
                    
                }

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
        
        
 
