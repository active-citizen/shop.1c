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
         */
        function updatePoints($history, $userId){
           
            $arPointsStatus = $history["status"];
            $history = $history["history"];
            
            CModule::IncludeModule("sale");
            
            // Получаем номер счёта данного пользователя
            $objSaleUserAccount = new CSaleUserAccount;
            $res = $objSaleUserAccount->GetList(array(),array("USER_ID"=>$userId,"CURRENCY"=>"BAL"));
            $accountId = 0;
            $accountAmount = 0;
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
            
            
            // Создаём индекс транзакций счёта, чтобы определять
            // есть ли уже начисление/списание из $history в транзакциях или нет
            // Ключ индекса - DEBIT+TRANSACT_DATE+DESCRIPTION
            $transactionIndex = array();
            // Создаём другой индекс транзакций счёта
            // Ключ индекса - номер заказа
            $transactionDescIndex = array();
            $res = CSaleUserTransact::GetList(array(),array("USER_ID"=>$userId,"CURRENCY"=>"BAL"));
            $objTransact = new CSaleUserTransact;
            
            while($arTransaction = $res->GetNext()){
                $transactionsIndex[
                    $arTransaction["DEBIT"]." ".
                    preg_replace("#^(.*)\s+.*$#","$1",$arTransaction["TRANSACT_DATE"])." ".
                    $arTransaction["~DESCRIPTION"]
                ] = $arTransaction["ID"];
                $transactionDescIndex["Б-".$arTransaction["ORDER_ID"]] = 1;
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

            // Изменяем размер счёта
            CSaleUserAccount::Update(
                $accountId,
                array(
                    "USER_ID"       =>  $userId,
                    "CURRENT_BUDGET"=>  $arPointsStatus["current_points"],
                    "CURRENCY"      =>  "BAL",
                    "NOTES"         =>  "" 
                )
            );

            // Обновляем число заработанных баллов
            $user = new CUser;
            $user->Update($userId, array("UF_USER_ALL_POINTS" => $arPointsStatus["all_points"]));

    // Чистим кэш компонента фильтра для пользователя 
    $objComponent = new CBitrixComponent();
    $objComponent->initComponent("ag:filter");
    $objComponent->clearResultCache(CUser::GetID());
    
    // Чистим кэш компонента главного меню 
    $objComponent = new CBitrixComponent();
    $objComponent->initComponent("ag:menu.top");
    $objComponent->clearResultCache(CUser::GetID());
 
        }
    }
