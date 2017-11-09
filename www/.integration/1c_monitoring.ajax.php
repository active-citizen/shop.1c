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


    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");

    // Добустимое время даунтайма 1С (сек)
    define("MONITORING_1C_DOWNTIME", 300);
    // Окно в которое фиксируется ошибка обратного толчка (сек
    define("MONITORING_BACKKICK_TIME", 120);
    // Окно в которое фиксируется ошибка окончания денег на тройке
    define("MONITORING_IGOGO_TIME", 120);

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
    ];
    $arErrors = [];


    // Чистим события, которым более суток
    $DB->Query(
        "DELETE FROM `int_1c_monitoring` WHERE `CTIME`<'".
        date("Y-m-d H:i:s", time()-24*60*60)
        ."'");

    // Проверяем коды оплаты
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


 
