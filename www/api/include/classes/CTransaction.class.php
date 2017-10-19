<?php
    require_once(realpath(dirname(__FILE__)."/../common.php"));
    require_once(realpath(dirname(__FILE__)."/wirix/db.class.php"));
    require_once(realpath(dirname(__FILE__)."/CSession.class.php"));
    require_once(realpath(dirname(__FILE__)."/CUser.class.php"));

    /**
     * Класс работы с транзакциями
     */
    class CTransaction extends CAll{

        var $verbose = true;
        var $error = '';

        function __construct(){
        }
    
        
        /**
         * Add tansaction into internal tables
        */
        function add(
            $nUserId,
            $nAppId,
            $fPoints,
            $nDebet,
            $sComment,
            $nTimeStamp = false,
            $nAccepted = 1
        ){
            // CAlculate CRC32 of transaction. For excluding duplicates
            $nCRC32 = $this->getCRC32($nUserId,$nTimeStamp,$nDebet,$fPoints,$sComment);
            // Define user table suffix
            $sUserSuffix = sprintf("%02d",$nUserId % 100);
            // Define time as NOW if time is undefined
            if($nTimeStamp===false)$nTimeStamp = time();
            // Insert transaction main part(fixed length data)
            $nTransactionId = $GLOBALS["DB"]->insert("transacts_brief_$sUserSuffix",array(
                "ctime"         =>  date("Y-m-d H:i:s", $nTimeStamp),
                "application_id"=>  $nAppId,
                "user_id"       =>  $nUserId,
                "debit"         =>  $nDebet,
                "quantity"      =>  $fPoints,
                "accepted"      =>  $nAccepted,
                "crc32"         =>  $nCRC32
            ));
            // Insert transaction second part (not fixed lenght data)
            if($nTransactionId){
                $GLOBALS["DB"]->insert("transacts_detail_$sUserSuffix",array(
                    "ctime"         =>  date("Y-m-d H:i:s", $nTimeStamp),
                    "transaction_id"=>  $nTransactionId,
                    "comment"       =>  $sComment
                ));
            }
            // Return transaction ID 
            return $nTransactionId;
        }


        /**
        
            @param $nUserId - ID пользователя для которого выводить
            @param $nAppId - ID приложения дл/
            @param $sDebet - что выводить (1 - начисления, -1 - списания, 0 - все)
            @param $nFromTimestamp - время с которого выводить (false - не учитывать)
            @param $nToTimestamp - время по которое выводить (false - не учитывать)
            @param $nAccepted - 1 - только принятые транзакции, 0- только непринятые, false - все
        */
        function getPoints(
            $sSessionId,
            $nAppId=false, 
            $sDebet=0, 
            $nFromTimestamp = false,
            $nToTimestamp = false,
            $nAccepted = 1,
            $bSync = false
        ){
            // Получаем ID пользователя по сесссии
            $CSession = new CSession;
            $arSession = $CSession->get($sSessionId);
            
            // Обновляем транзакции из ЕМП
            $arEMPAnswer = $this->updatePointsFromEMP($sSessionId,$arSession["user_id"],$bSync);
            
            $sUserSuffix = CUser::getSuffix($arSession["user_id"]);
           
            $arHistory = [];
            if($bSync){
                // Условия для выборки
                $sCond = "`a`.`user_id`=".intval($arSession["user_id"]);
                if($nAppId!==false)
                    $sCond .= " AND  `a`.`application_id`='".$nAppId."'";
                    
                if(intval($sDebet)>0)
                    $sCond .= " AND  `a`.`debet`>0";
                elseif(intval($sDebet)<0)
                    $sCond .= " AND  `a`.`debet`<0";
                    
                if($nFromTimestamp!==false)
                    $sCond .= " AND  `a`.`ctime`>='".date("Y-m-d H:i:s",intval($nFromTimestamp))."'";
                if($nToTimestamp!==false)
                    $sCond .= " AND  `a`.`ctime`<='".date("Y-m-d H:i:s",intval($nToTimestamp))."'";
                if($nAccepted!==false)
                    $sCond .= " AND  `a`.`accepted`='".$nAccepted."'";
                    
                $arTables = array();
                $arTables["a"] = "transacts_brief_".$sUserSuffix;
                $arTables["b"] = "transacts_detail_".$sUserSuffix;
                $arJoin["`a`.`id`=`b`.`transaction_id`"] = "LEFT";
                
                $arFields = array(
                    "UNIX_TIMESTAMP(`a`.`ctime`)"                   =>  "date",
                    "`a`.`quantity`*`a`.`debit`*`a`.`accepted`"     =>  "points",
                    "`b`.`comment`"                                 =>  "title",
                    "IF(`a`.`debit`>0,'debit','credit')"            =>  "action",

                    "`a`.`ctime`"                                   =>  "ctime",
                    "DATE_FORMAT(`a`.`ctime`,'%d.%m.%Y %H:%i:%s')"  =>  "ctimef",
                    "`a`.`quantity`"                                =>  "quantity",
                    "`a`.`debit`"                                   =>  "debit",
                    "`a`.`accepted`"                                =>  "accepted",
                    "`a`.`crc32`"                                   =>  "crc32",
                );
                
                $GLOBALS["DB"]->rows =array();
                $GLOBALS["DB"]->search($arTables,$arJoin,array(),$sCond, "`a`.`ctime` DESC", 0,0,$arFields);
                $arHistory = $GLOBALS["DB"]->rows;
            }
//          $arUserPoints = CUser::getUserPoints($arSession["user_id"]);

            return array(
                "history"   =>  $arHistory,
                "status"    =>  get_object_vars($arEMPAnswer->result->status)
            );
        }
        
        /**
         *  Обновляем баллы из ЕМП в таблицах
        */
        function updatePointsFromEMP($sSessionId, $nUserId,$bSync = false){
            $CUser = new CUser;

            $data = array(
                "token"=>$GLOBALS["CONF"]["mvag_token"],
                "auth"=>array(
                    "session_id"     =>  $sSessionId,
                )
            );
            
            require_once(realpath(dirname(__FILE__)."/curl.class.php"));
            
            $data = json_encode($data);
            $curl = new curlTool;
            $data = $curl->post(
                "https://emp.mos.ru/v2.0.0/poll/getHistory", 
                $data, 
                array("Content-Type: application/json")
            );
           
            $data = json_decode($data);       
            // Обновляем EMP поебень
            if(
                is_object($data) 
                && property_exists($data,"result")
                && is_object($data->result) 
                && property_exists($data->result,"status")
                && is_object($data->result->status) 
                && property_exists($data->result->status,"current_points")
            )
                $CUser->updateBalance($nUserId,"current_points",$data->result->status->current_points);
            if(
                is_object($data) 
                && property_exists($data,"result")
                && is_object($data->result) 
                && property_exists($data->result,"status")
                && is_object($data->result->status) 
                && property_exists($data->result->status,"all_points")
            )
                $CUser->updateBalance($nUserId,"all_points",$data->result->status->all_points);
            if(
                is_object($data) 
                && property_exists($data,"result")
                && is_object($data->result) 
                && property_exists($data->result,"status")
                && is_object($data->result->status) 
                && property_exists($data->result->status,"spent_points")
            )
                $CUser->updateBalance($nUserId,"spent_points",$data->result->status->spent_points);
            
            if(
                is_object($data) 
                && property_exists($data,"result")
                && is_object($data->result) 
                && property_exists($data->result,"status")
                && is_object($data->result->status) 
                && property_exists($data->result->status,"freezed_points")
            )
                $CUser->updateBalance($nUserId,"freezed_points",$data->result->status->freezed_points);
            
            if(
                is_object($data) 
                && property_exists($data,"result")
                && is_object($data->result) 
                && property_exists($data->result,"status")
                && is_object($data->result->status) 
                && property_exists($data->result->status,"state")
            )
                $CUser->updateBalance($nUserId,"ag_status",$data->result->status->state);
 
            $arPoints = '';
            if(
                is_object($data) 
                && property_exists($data,"result")
                && is_object($data->result) 
                && property_exists($data->result,"history")
            )
                $arPoints = $data->result->history;
                
            if($bSync && is_array($arPoints))foreach($arPoints as $arPoint){
                $arExistsTransaction = $this->get(
                    $nUserId,
                    $arPoint->date,
                    $arPoint->action=='debit'?"1":"-1",
                    $arPoint->points,
                    $arPoint->title
                );
                if(!$arExistsTransaction){
                    $this->add(
                        $nUserId,
                        2,
                        $arPoint->points,
                        $arPoint->action=='debit'?"1":"-1",
                        $arPoint->title,
                        $arPoint->date
                    );
                }
            }
            // пересчитываем баланс пользователя
            $CUser->updateBalance($nUserId);

            return $data;
        }
        
        
        function addEMPPoints(
            $sSessionId, 
            $nDebit, 
            $nPoints, 
            $sComment
        ){
            $sSSOId = $this->getSSOIdBySession($sSessionId);
            if(!$sSSOId)return false;
            $url = "https://emp.mos.ru/v0.3/support/addPoints";
            $data = '{
                "token": "'.$GLOBALS["CONF"]["mvag_token"].'",
                "title": "'.$sComment.'",
                "points": '.$nPoints.',
                "action": "'.($nDebit>0?'debit':'credit').'",
                "sso_id": "'.$sSSOId.'"
            }';
            require_once(realpath(dirname(__FILE__)."/curl.class.php"));
            $curl = new curlTool;

            $data = $curl->post($url, $data, array("Content-Type: application/json"));

            $data =  json_decode($data);
            $data = json_decode(json_encode((array)$data), TRUE);
            if(!isset($data["errorCode"]) || $data["errorCode"])return false;
            if(
                isset($data["result"]["statuses"][$sSSOId]["error"])
                && 
                $data["result"]["statuses"][$sSSOId]["error"]
            ){
                $this->error = $data["result"]["statuses"][$sSSOId]["error"];
                return false;
            }
            return true;
        }
        
        function sendTrans(
            $sSessionId, 
            $nAppId, 
            $nDebit, 
            $nPoints, 
            $sComment
        ){
        }
        
        function getSSOIdBySession($sSessionId){
            $CSession = new CSession;
            $CUser = new CUser;


            $arSession = $CSession->get($sSessionId);

            if(!$arSession)return false;
            $nUserId = $arSession["user_id"];
            $arUser = $CUser->getById($nUserId);

            if(!$arUser)return false;

            return $arUser["sso_id"];
        }
        
        
        /**
            Функция подсчёта CRC32 для транзакции
            
            @param $nTimestamp
            @param $nDebit
            @param $sPoints 
            @param $sComment
        */
        function getCRC32(
            $nUserId,
            $timestamp,
            $nDebit,
            $sPoints,
            $sComment
        ){
            $sCRC = 
                intval($nUserId).
                intval($timestamp)
                .$nDebit
                .sprintf("%.3f",$sPoints)
                .trim($sComment)
                ;
            return crc32($sCRC);
        }
        
        /**
        
            Поиск транзакции по 5 параметрам
             
            @param $nUserId,
            @param $timestamp,
            @param $nDebit,
            @param $sPoints,
            @param s$sComment
        
        */
        function get(
            $nUserId,
            $timestamp,
            $nDebit,
            $sPoints,
            $sComment
        ){
            $nCRC32 = $this->getCRC32($nUserId,$timestamp,$nDebit,$sPoints,$sComment);
            return $this->getBriefByCRC32($nUserId,$nCRC32);
        }
        
        /*
         * Получение транзакции по CRC32
         * 
            @param $nUserId
            @param $sCRC32
        
        
        */
        function getBriefByCRC32($nUserId,$sCRC32){
            $sUserSuffix = CUser::getSuffix($nUserId);
            $GLOBALS["DB"]->record =array();
            $GLOBALS["DB"]->search_one("transacts_brief_$sUserSuffix",array("crc32"=>$sCRC32));
            return $GLOBALS["DB"]->record;
        }
        

    }
