<?php
/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 */

//if(
//$_SERVER["REMOTE_ADDR"]!='46.20.69.113' ||
//!preg_match("#zabbix#i",$_SERVER["HTTP_USER_AGENT"])){
//    echo "Access denied";
//    die;
//}

    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

    // Добустимое время даунтайма 1С (сек)
    define("MONITORING_1C_DOWNTIME", 300);
    // Окно в которое фиксируется ошибка обратного толчка (сек
    define("MONITORING_BACKKICK_TIME", 120);
    // Окно в которое фиксируется ошибка окончания денег на тройке
    define("MONITORING_IGOGO_TIME", 120);
    // Окно времени, за которое проверяются ошибки отклонения
    define("REJECT_WINDOW",3600);
    // Количество подряд идущих отклонёных заказов
    define("REJECT_COUNT",5);

    // Коды ошибок
    define("ERROR_NONE",0);
    define("ERROR_1C_IS_DOWN",1);
    define("ERROR_QUERY_SUCCESS",2);
    define("ERROR_BACKKICK",3);
    define("ERROR_IGOGO_MONEY",4);

    $arErrorsMessages = [
        ERROR_NONE =>[
            "MESSAGE"=>"Ошибок нет"
        ],
        ERROR_1C_IS_DOWN => [
            "MESSAGE"=>"1C перестала отправлять запросы более чем ".
            MONITORING_1C_DOWNTIME
            ." сек. назад"
        ],
        ERROR_QUERY_SUCCESS => [
            "MESSAGE"=>"Нарушен порядок query/success запросов"
        ],
        ERROR_BACKKICK => [
            "MESSAGE"=>"Ошибка битрикс при приёме обратного толчка"
        ],
        ERROR_IGOGO_MONEY => [
            "MESSAGE"=>"На счету пополнения Троек закончились средства"
        ],
        ERROR_TRANSPORT_REJECT => [
            "MESSAGE"=>"Перманентные отклонения троек или парковок"
        ],
        ERROR_REJECT => [
            "MESSAGE"=>"Отклонение заказа"
        ],
    ];
    $arErrors = [];


    // Чистим события, которым более суток
    $DB->Query(
        "DELETE FROM `int_1c_monitoring` WHERE `CTIME`<'".
        date("Y-m-d H:i:s", time()-24*60*60)
        ."'");

    // Проверяем коды оплаты
    /*
    Тройка с контролем отклонений не нужна
    $sQuery = "
        SELECT
            `ctime` as `ALARM_TIME`,
            `data` as `DATA`
        FROM
            `int_curl_logger`
        WHERE
            `ctime`>".(time()-MONITORING_IGOGO_TIME)."
        ORDER BY
            `id` DESC
        LIMIT 
            256
    ";
    $resQuery = $DB->Query($sQuery);
    while($arEvent = $resQuery->Fetch()){
        $arData = json_decode($arEvent["DATA"]);
        $arData = json_decode(json_encode((array)$arData), TRUE);
        if(
            isset($arData["errorCode"])
            && isset($arData["errorDesc"])
            && $arData["errorCode"]==24
            && $arData["errorDesc"]==116
        ){
            $arErrors[] = [
                "code"      =>  ERROR_IGOGO_MONEY,
                "message"   =>  $arErrorsMessages[ERROR_IGOGO_MONEY]["MESSAGE"],
                "timeStamp" =>  $arEvent["ALARM_TIME"],
                "dateTime"  =>  date("Y-m-d H:i:s", $arEvent["ALARM_TIME"]) 
            ];
        }
    }
    */

    // Получаем отклонённые заказы на прошедший час кроме тройки и парковки
    $sQuery = "
        SELECT
            `order`.`ADDITIONAL_INFO` as `order_num`,
            UNIX_TIMESTAMP(`order`.`DATE_INSERT`) as `ALARM_TIME`
        FROM 
            `index_order` as `order`
                LEFT JOIN
            `b_iblock_element_property` as `product`
                ON 
                    `product`.`IBLOCK_ELEMENT_ID`=`order`.`PRODUCT_ID`
                    AND `product`.`IBLOCK_PROPERTY_ID`=".ARTNUMBER_PROPERTY_ID."
        WHERE
            `order`.`STATUS_ID`='AF'
            AND `order`.`DATE_INSERT`>='".
                date("Y-m-d H:i:s",time()-REJECT_WINDOW)
                ."'
            AND (
                `product`.`VALUE` NOT IN ('troyka','parking')
                OR `product`.`VALUE` IS NULL
            )
        LIMIT
            1
    ";
    $resAF = $DB->Query($sQuery);
    if($arEvent = $resAF->Fetch()){
        $arErrors[] = [
            "code"      =>  ERROR_REJECT,
            "message"   =>  $arErrorsMessages[ERROR_REJECT]["MESSAGE"]
                ." ".$arEvent["order_num"],
            "timeStamp" =>  $arEvent["ALARM_TIME"],
            "dateTime"  =>  date("Y-m-d H:i:s", $arEvent["ALARM_TIME"]) 
        ];
    }

    // Получаем последние заказов тройки за последний час
    $sQuery = "
        SELECT
            `order`.`ADDITIONAL_INFO` as `order_num`,
            `order`.`STATUS_ID` as `status`,
            UNIX_TIMESTAMP(`order`.`DATE_INSERT`) as `ALARM_TIME`,
            `product`.`VALUE` as `art`
        FROM 
            `index_order` as `order`
                LEFT JOIN
            `b_iblock_element_property` as `product`
                ON 
                    `product`.`IBLOCK_ELEMENT_ID`=`order`.`PRODUCT_ID`
                    AND `product`.`IBLOCK_PROPERTY_ID`=".ARTNUMBER_PROPERTY_ID."
        WHERE
            1
            AND `order`.`DATE_INSERT`>='"
                .date("Y-m-d H:i:s",time()-REJECT_WINDOW)
            ."'
            AND `product`.`VALUE` IN ('troyka')
        ORDER BY 
            `order`.`ID` DESC
        LIMIT
            1000
    ";
    $resAF = $DB->Query($sQuery);
    $arTroykas = [];
    while($arTroyka = $resAF->Fetch())$arTroykas[] = $arTroyka;

    // Ищем 5 подряд идущих отклонённых заказов
    $nRejected = 0;
    foreach($arTroykas as $arEvent){
        if($arEvent["status"]=='AF')$nRejected++;
        if($nRejected>=REJECT_COUNT){
            $arErrors[] = [
                "code"      =>  ERROR_TRANSPORT_REJECT,
                "message"   =>  $arErrorsMessages[ERROR_TRANSPORT_REJECT]["MESSAGE"]
                    ." (".$arEvent["art"].")".$arEvent["order_num"],
                "timeStamp" =>  $arEvent["ALARM_TIME"],
                "dateTime"  =>  date("Y-m-d H:i:s", $arEvent["ALARM_TIME"]) 
            ];
            break;
        }
    }


    // Получаем последние заказов парковки за последний час
    $sQuery = "
        SELECT
            `order`.`ADDITIONAL_INFO` as `order_num`,
            `order`.`STATUS_ID` as `status`,
            UNIX_TIMESTAMP(`order`.`DATE_INSERT`) as `ALARM_TIME`,
            `product`.`VALUE` as `art`
        FROM 
            `index_order` as `order`
                LEFT JOIN
            `b_iblock_element_property` as `product`
                ON 
                    `product`.`IBLOCK_ELEMENT_ID`=`order`.`PRODUCT_ID`
                    AND `product`.`IBLOCK_PROPERTY_ID`=".ARTNUMBER_PROPERTY_ID."
        WHERE
            1
            AND `order`.`DATE_INSERT`>='"
                .date("Y-m-d H:i:s",time()-REJECT_WINDOW)
            ."'
            AND `product`.`VALUE` IN ('parking')
        ORDER BY 
            `order`.`ID` DESC
        LIMIT
            1000
    ";
    $resAF = $DB->Query($sQuery);
    $arParkings = [];
    while($arParking = $resAF->Fetch())$arParkings[] = $arParking;

    // Ищем 5 подряд идущих отклонённых заказов
    $nRejected = 0;
    foreach($arParkings as $arEvent){
        if($arEvent["status"]=='AF')$nRejected++;
        if($nRejected>=REJECT_COUNT){
            $arErrors[] = [
                "code"      =>  ERROR_TRANSPORT_REJECT,
                "message"   =>  $arErrorsMessages[ERROR_TRANSPORT_REJECT]["MESSAGE"]
                    ." (".$arEvent["art"].")".$arEvent["order_num"],
                "timeStamp" =>  $arEvent["ALARM_TIME"],
                "dateTime"  =>  date("Y-m-d H:i:s", $arEvent["ALARM_TIME"]) 
            ];
            break;
        }
    }



    // Проверяем ошибку обратного толчка
    $sQuery = "
        SELECT 
            UNIX_TIMESTAMP(`CTIME`) as `ALARM_TIME` 
        FROM 
            `int_1c_monitoring` 
        WHERE 
            `MODE` = 'file' 
            AND `STATUS` != 'success' 
            AND `CTIME`>'".
            date("Y-m-d H:i:s",time()-MONITORING_BACKKICK_TIME)."'
        ORDER BY 
            `ID` DESC 
        LIMIT 1";
    if($arEvent = $DB->Query($sQuery)->Fetch()){
        $arErrors[] = [
            "code"      =>  ERROR_BACKKICK,
            "message"   =>  $arErrorsMessages[ERROR_BACKKICK]["MESSAGE"],
            "timeStamp" =>  $arEvent["ALARM_TIME"],
            "dateTime"  =>  date("Y-m-d H:i:s", $arEvent["ALARM_TIME"]) 
        ];
    }


    // Проверяем даунтайм 1С
    $sQuery = "SELECT UNIX_TIMESTAMP(MAX(`CTIME`))+".
        MONITORING_1C_DOWNTIME." as `ALART_TIME` FROM `int_1c_monitoring` LIMIT 1";
    $arEvent = $DB->Query($sQuery)->Fetch();
    if($arEvent["ALART_TIME"]<time()){
        $arErrors[] = [
            "code"      => ERROR_1C_IS_DOWN,
            "message"   =>  $arErrorsMessages[ERROR_1C_IS_DOWN]["MESSAGE"],
            "timeStamp" => $arEvent["ALARM_TIME"],
            "dateTime"  => date("Y-m-d H:i:s", $arEvent["ALARM_TIME"]) 
        ];
    }

    // Проверяем очерёдность следовыания query-success
    $sQuery = "
        SELECT 
            `MODE`,
            UNIX_TIMESTAMP(`CTIME`) as `ALARM_TIME`
        FROM 
            `int_1c_monitoring` 
        WHERE 
            `MODE` IN ('success','query')
        ORDER BY 
            `ID` DESC
        LIMIT 5";
    $resQuery = $DB->Query($sQuery);
   
    $sPrev = '';
    while($arEvent = $resQuery->Fetch()){
        if($arEvent["MODE"]==$sPrev){
            $arErrors[] = [
                "code"      => ERROR_QUERY_SUCCESS,
                "message"   =>  $arErrorsMessages[ERROR_QUERY_SUCCESS]["MESSAGE"],
                "timeStamp" => $arEvent["ALARM_TIME"],
                "dateTime"  => date("Y-m-d H:i:s", $arEvent["ALARM_TIME"]) 
            ];
            break;           
        }
        $sPrev = $arEvent["MODE"];
    }


    $arAnswer = ["errorsCount"   =>  count($arErrors)];
    $arAnswer["errors"] = $arErrors;
    if($nTimeStamp){
        $arAnswer["timeStamp"] = $nTimeStamp;
        $arAnswer["dateTime"] = date("Y-m-d H:i:s", $nTimeStamp);
    }

    if(isset($_REQUEST["json"])){
        header("Content-type: application/json");
        echo json_encode($arAnswer);
    }
    else{
        header("Content-type: text/plain; charset=utf-8");
        echo "Число ошибок: ".$arAnswer["errorsCount"]."\n";
        foreach($arAnswer['errors'] as $arError){
            echo "--------------------\n";
            echo "Код ошибки: ".$arError["code"]."\n";
            echo "Сообщение об ошибке: ".$arError["message"]."\n";
            echo "timestamp возникновения ошибки: ".$arError["timeStamp"]."\n";
            echo "Время возникновения ошибки: ".$arError["dateTime"]."\n";
        }
    }

    require(
        $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"
    );


 
