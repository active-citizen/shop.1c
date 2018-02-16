<?php
    namespace SSAG;
    require_once(realpath(__DIR__)."/CSSAG.class.php");
    require_once(realpath(__DIR__."/..")."/CCurl/CCurlSimple.class.php");
    require_once(realpath(__DIR__."/..")."/CUtil/CLang.class.php");
    require_once(realpath(__DIR__."/..")."/CLog/CSSAGLog.class.php");
    
    use AGShop as AGShop;
    use AGShop\Util as Util;
    use AGShop\SSAG as SSAG;
    use AGShop\Curl as Curl;
    use AGShop\Log as Log; 


    /**
        Класс для работой с историей транзакций СС АГ
    */
    class CSSAGAccount extends CSSAG{
        
        private $sSummaryMethod = '/mvag/billing/getSummary';
        private $sPointsMethod = '/mvag/billing/add';
        
        function __construct($sSessionId = '',$nUserId = 0){
            global $USER;
            if($nUserId)$nUSerId = $USER->GetID();
            parent::__construct($sSessionId,$nUserId);
        }

        /**
            Обновление информации о счёте пользователя из CC АГ
        */
        function update(){
            $arSign = $this->getSignature($this->nAGID);
            $sUrl = $this->sDomain.":".$this->sPort.$this->sSummaryMethod;
            $arRequest = [
                "ag_id"     =>  $this->nAGID,
                "nonce"     =>  $arSign["nonce"],
                "signature" =>  $arSign["signature"]
            ];
            $sRequest = json_encode($arRequest);
            
            $objCurl = new \Curl\CCurlSimple;
            $sResult = $objCurl->post($sUrl, $sRequest);
            \Log\CSSAGLog::addLog($sUrl, $sRequest, $sResult);            
            if(!$arAnswer = $this->checkAnswer($sResult))
                return false;
            
            if(!isset($arAnswer["result"]["current_points"]))return 
                $this->addError("Не удалось получить баланс пользователя");

            $nBalance = intval($arAnswer["result"]["current_points"]);
            $arAnswer["result"]["ag_status"] = $sStatus = 
                isset($arAnswer["result"]["is_active"]) 
                && $arAnswer["result"]["is_active"]
                ?
                'Активный гражданин'
                :
                ''
                ;

            if(!$nBalance)return $this->addError("Нулевой баланс");

            $objUser = new \CUser;
            $objUser->Update(
                $this->nBitrixUserId, 
                ["UF_USER_ALL_POINTS" => $nBalance]
            );
            $objUser->Update(
                $this->nBitrixUserId, 
                ["UF_USER_AG_STATUS" => $sStatus]
            );

            $arAnswer["result"]["title"] = number_format(
                $nBalance,'0',',',' ')." ".\Util\CLang::getPoints($nBalance);

            
            return $arAnswer["result"];
        }

        /**
            Получение баланса пользователя

            @return количество баллов на счету пользователя
        */
        function balance(){
            $objUser = new \CUser;
            $arUser = $objUser->GetList(
                ($by="personal_country"), ($order="desc"),
                ["ID"=>$this->nBitrixUserId],[
                    "SELECT"=>["UF_USER_ALL_POINTS"],
                    "NAV_PARAMS"=>["nTopCount"=>1]

                ]
            )->Fetch();
            return $arUser["UF_USER_ALL_POINTS"];
        }

        /**
            Транзакция на счёт пользователя

            @param $nSum - сумма(- снятие, + начисление)
            @param $sComment - кооментарий к транзакции

            @return итоговое количество баллов
        */
        function transaction($nSum, $sComment){

            $nSum = intval($nSum);
            if(!$nSum)return $this->addError(
                'Нельзя начислять/списывать 0 баллов'
            );
            if(!$sComment)return $this->addError(
                'Нельзя начислять/списывать баллы без комментария'
            );
            $sAction = $nSum>0?'debit':'credit';
            $nSum = abs($nSum);

            $sSignString = 
                $sAction
                ."&".$this->nAGID
                ."&".$nSum
                ."&".$sComment;

            $arSign = $this->getSignature($sSignString);

            $sUrl = $this->sDomain.":".$this->sPort.$this->sPointsMethod;
            $arRequest = [
                "ag_id"     =>  $this->nAGID,
                "title"     =>  $sComment,
                "points"    =>  $nSum,
                "action"    =>  $sAction,
                "nonce"     =>  $arSign["nonce"],
                "signature" =>  $arSign["signature"]
            ];
            $sRequest = json_encode($arRequest);
            
            $objCurl = new \Curl\CCurlSimple;
            $sResult = $objCurl->post($sUrl, $sRequest);

            \Log\CSSAGLog::addLog($sUrl, $sRequest, $sResult);            
            if(!$arAnswer = $this->checkAnswer($sResult))
                return false;
            return $arAnswer["result"]["current_points"]; 
        }

        
    }
   
