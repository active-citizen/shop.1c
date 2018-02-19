<?
/*
 * point.class.php
 * 
 * Copyright 2016 Андрей Инюцин <inutcin@yandex.ru>
 * 
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
 * 
 */


    class bxPoint{

        var $error = '';
        
        /**
         * Обновление в битриксе транзакций личного счёта из занных EМП
         *  DEPRICATED
         */

        /*
        function updatePoints($history, $userId){
          
            $arPointsStatus = $history["status"];
            $history = $history["history"];
            
            CModule::IncludeModule("sale");
            
            // Создаём индекс транзакций счёта, чтобы определять
            // есть ли уже начисление/списание из $history в транзакциях или нет
            // Ключ индекса - DEBIT+TRANSACT_DATE+DESCRIPTION
            $transactionIndex = array();
            // Создаём другой индекс транзакций счёта
            // Ключ индекса - номер заказа
            $transactionDescIndex = array();
            $res = CSaleUserTransact::GetList(
                array("ID"=>"DESC"),
                array("USER_ID"=>$userId,"CURRENCY"=>"BAL")
            );
            $objTransact = new CSaleUserTransact;
            
            while($arTransaction = $res->GetNext()){
                $transactionsIndex[
                    $arTransaction["DEBIT"]." ".
                    preg_replace(
                        "#^(.*)\s+.*$#",
                        "$1",
                        $arTransaction["TRANSACT_DATE"]
                    )." ".
                    $arTransaction["~DESCRIPTION"]
                ] = $arTransaction["ID"];
                $transactionDescIndex[$arTransaction["ORDER_ID"]] = 1;
            }
                
            foreach($history as $nT=>$empTransact){
                // Не загружаем транзакции за заказы
                
                // Если начисление-списание связано с заказом в битриксе - пропускаем
                $flag = 0;
                foreach($transactionDescIndex as $key=>$value)
                    if(preg_match("# ".$key." #",$empTransact["title"])){
                        $flag = 1;
                        break;
                    }
                if($flag){
                    continue;
                }
                
                // Формируем ключ для поиска по индексу транзакций
                $transactionKey = 
                    ($empTransact["action"]=='debit'?"Y":"N")." ".
                    date("d.m.Y",$empTransact["date"])." ".
                    $empTransact["title"];

                // Если это начисление/списание уже есть в индексе транзакций - пропускаем её
                if(isset($transactionsIndex[$transactionKey])){
                    $objTransact->Update(
                        $transactionsIndex[$transactionKey], 
                        array("TRANSACT_DATE"=>date("d.m.Y H:i:s", $empTransact["date"]))
                    );
                    continue;
                }

                $arFields = array(
                    "USER_ID"       =>  $userId,
                    "AMOUNT"        =>  abs(intval($empTransact['points'])),
                    "CURRENCY"      =>  "BAL",
                    "DEBIT"         =>  ($empTransact['action']=='debit'?'Y':'N'),
                    "DESCRIPTION"   =>  $empTransact["title"],
                    "ORDER_ID"      =>  "",
                    "EMPLOYEE_ID"   =>  1,
                    "TRANSACT_DATE" =>  date("d.m.Y H:i:s", $empTransact["date"])
                );      
                
                // ДОбавляем транзакцию
                if(!$transactId = $objTransact->Add($arFields)){
                    $error = $this->error = "Ошибка добавления транзакции: ". print_r($objTransact, 1);
                    return false;
                }
                else{
                    
                }
                   
            }
            
            $this->updateAccount($arPointsStatus, $userId);
       }
       */

        /*
            Обновление состяния счёта
            DEPRICATED

            @return число баллов на балансе пользователя
        */
        /*
        function updateAccount($arPointsStatus, $userId){
            // Получаем номер счёта данного пользователя
            $objSaleUserAccount = new CSaleUserAccount;
            $res = $objSaleUserAccount->GetList(
                [], ["USER_ID"=>$userId,"CURRENCY"=>"BAL"],
                false, ["nTopCount"=>1],["ID"]
             );
            $accountId = 0;
            // Создаём личный счет если ещё нет
            if($arrAccount = $res->getNext()){
                $accountId = $arrAccount["ID"];
            }
            else{
                $accountId = $objSaleUserAccount->Add(array(
                    "USER_ID"=>$userId,
                    "CURRENCY"=>"BAL",
                    "CURRENT_BUDGET"=>0
                ));
            }

            // Изменяем размер счёта
            CSaleUserAccount::Update(
                $accountId,
                $arFields = [
                    "USER_ID"       =>  $userId,
                    "CURRENT_BUDGET"=>  $arPointsStatus["current_points"],
                    "CURRENCY"      =>  "BAL",
                    "NOTES"         =>  "" 
                ]
            );

            // Обновляем число заработанных баллов
            $user = new CUser;
            // Костыль для Радика
            if($userId == 2145){
                $arPointsStatus["all_points"] = 1200;
                $arPointsStatus["ag_status"] = 'Активный гражданин';
            }
            

            if(isset($arPointsStatus["all_points"]) && $arPointsStatus["all_points"])
                $user->Update($userId, array("UF_USER_ALL_POINTS" => 
                    $arPointsStatus["all_points"]
                ));
            if(isset($arPointsStatus["ag_status"]))
                $user->Update($userId, array("UF_USER_AG_STATUS" =>
                    $arPointsStatus["ag_status"]
                ));

            return $arPointsStatus["current_points"];
         }
         */

        
        /**
            Обновление баланса текущего пользователя
            DEPRICATED

            @return $answer = [
                
            ]
        */
        /*
        function fetchAccountFromAPI(
            $sync = false //!< Вызвать синхронизацию таблиц ККБ
        ){
            global $USER;
            require(
                realpath(dirname(__FILE__)."/..")
                ."/secret.inc.php"
            );
            require_once(
                realpath(dirname(__FILE__))
                ."/active-citizen-bridge.class.php"
            );
            require_once(
                realpath(dirname(__FILE__)).
                "/user.class.php"
            );
            
            $agBrige = new ActiveCitizenBridge;
            
            $answer = array(
                "errors"=>""
            );
            
            $bxUser = new bxUser;
            $session_id = $bxUser->getEMPSessionId();
            
            $args = array(
                "session_id"=>  $session_id,
                "token"     =>  $EMP_TOKENS[CONTOUR],
                "sync"      =>  $sync
            );
            $agBrige->setMethod('pointsHistory');
            $agBrige->setMode('emp');
            $agBrige->setArguments($args);
            $answer["errors"] = $agBrige->getErrors();
            $profile = array();
            if(!$answer["errors"] && !$history = $agBrige->exec()){
                $answer["errors"] = array_merge(
                    $answer["errors"],$agBrige->getErrors()
                );
            }

            if(
                (
                !isset($history["result"]["status"])
                ||
                !isset($history["result"]["status"])
                )
                &&
                (
                    // Ошибка авторизации
                    $history["errorCode"] =='401'
                    ||
                    // Поддержка авторизации в проекте «Активный гражданин» по
                    // адресу электронной почты остановлена. Пожалуйста,
                    // авторизуйтесь с использованием номера мобильного телефона.
                    $history["errorCode"] =='5950'
                )
            ){
                // Операторов, партнёров, администраторов и администраторов
                // магазина не разлогиниваем
                if(
                !in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
                &&
                !in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())
                &&
                !in_array(SHOP_ADMIN, $USER->GetUserGroupArray())
                &&
                !$USER->IsAdmin()
                ) $USER->Logout();
                return [
                    "errors"=>["Не получено состояние счёта"]
                ];
            }

            if(isset($history["errorMessage"]) && $history["errorMessage"])
                $answer["errors"][] = $history["errorMessage"];
                
            $bxPoint = new bxPoint;
            if(!$bxPoint->updateAccount(
                $history["result"]["status"], CUser::GetID())
            )$answer["errors"][] = $bxPoint->error;

            $answer["status"] = $history["result"]["status"];

            require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/rus.lib.php");

            $answer["title"] = 
                number_format(
                    $history["result"]["status"]["current_points"]
                    ,0
                    ,","
                    ," "
                )
                ." "
                .get_points(
                    intval($history["result"]["status"]["current_points"])
                );

            return $answer;
        }
        */


    }
