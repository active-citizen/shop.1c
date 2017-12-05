<?php
    require_once("curl.class.php");

    class CAGIDMethod{

        var $AGID = '';         //!< ag_id пользователя
        var $session_id = '';
        var $secret = '';
        var $error = '';
        var $url = '';
        var $port = 80;
        var $nonce = ''; 
        var $arProfile = [];
        var $method = '';

        function __construct($session_id = ''){

            if(!trim($session_id))
                return $this->riseError("Не указан ID сессии");
            else
                $this->session_id = $session_id;

            require(realpath(dirname(__FILE__)."/../../..")
                ."/.integration/secret.inc.php"
            );
            require(realpath(dirname(__FILE__)."/../../..")
                ."/local/php_interface/settings.inc.php"
            );

            if(
                !isset($AG_SECRETS[CONTOUR]["secret"])
                ||
                !trim($AG_SECRETS[CONTOUR]["secret"])
            ){
                //return $this->riseError("Не определён ключ взаимодействия");
            }else{
                $this->secret = $AG_SECRETS[CONTOUR]["secret"];
            }

            /*
                На локальных копиях используем внешний URL
            */
            if(
// Раскомментить когда будет доступ по локальноЦОДовским адресам
//                preg_match("#.*\.local$#", $_SERVER["HTTP_HOST"])
//                &&
                isset($AG_SECRETS[CONTOUR]["ext_url"])
                &&
                trim($AG_SECRETS[CONTOUR]["ext_url"])
            )
                $this->url = $AG_SECRETS[CONTOUR]["ext_url"];
            
            /*
                На серверных копиях используем внутренний URL
            */
// Раскомментить когда будет доступ по локальноЦОДовским адресам
            /*
            if(
                !preg_match("#.*\.local$#", $_SERVER["HTTP_HOST"])
                &&
                isset($AG_SECRETS[CONTOUR]["local_url"])
                &&
                trim($AG_SECRETS[CONTOUR]["local_url"])
            )
                $this->url = $AG_SECRETS[CONTOUR]["local_url"];

            if(!$this->url)            
                return $this->riseError("Нет url API на контуре ".CONTOUR);
            */

            /*
                На серверных копиях используем внутренний порт
            */
// Раскомментить когда будет доступ по локальноЦОДовским адресам
            /*
            if(
                !preg_match("#.*\.local$#", $_SERVER["HTTP_HOST"])
                &&
                isset($AG_SECRETS[CONTOUR]["local_port"])
                &&
                trim($AG_SECRETS[CONTOUR]["local_port"])
            )
                $this->port = $AG_SECRETS[CONTOUR]["local_port"];
            */


            if(!$this->arProfile = $this->getProfile())
                return false;

            $this->AGID = $this->arProfile["ag_id"];

            return true;
        }

        function getProfile(){
            // случайная строка длиной 20 символов
            $sNonce = substr(md5(time().rand()),0,20); 
            // Вычисляем строку подписи
            $sSignString = 
                $this->secret ."&".$this->session_id ."&".$sNonce;
            // Вычисляем подпись
            $sSignature = base64_encode(hash_hmac(
                'sha256',
                $sSignString,
                $this->secret,
                true
            ));
            // Формируем запрос
            $sRequest = json_encode([
                "session_id"=>  $this->session_id,
                "nonce"     =>  $sNonce,
                "signature" =>  $sSignature
            ]);

            // Получаем ответ от API
            $this->setMethod("/mvag/user/getProfile");
            $sAnswer = $this->request(
                $this->url.":".$this->port.$this->getMethod(),
                $sRequest
            );
            // Возвращает результат запроса после всех проверок
            return $this->checkErrors($sAnswer);
        }

        function getSummary(){
            // случайная строка длиной 20 символов
            $sNonce = substr(md5(time().rand()),0,20); 
            // Вычисляем строку подписи
            $sSignString = 
                $this->secret."&".$this->AGID."&".$sNonce;
            // Вычисляем подпись
            $sSignature = base64_encode(hash_hmac(
                'sha256',
                $sSignString,
                $this->secret,
                true
            ));
            // Формируем запрос
            $sRequest = json_encode([
                "ag_id"=>  $this->AGID,
                "nonce"     =>  $sNonce,
                "signature" =>  $sSignature
            ]);

            // Получаем ответ от API
            $this->setMethod("/mvag/billing/getSummary");
            $sAnswer = $this->request(
                $this->url.":".$this->port.$this->getMethod(),
                $sRequest
            );
            // Возвращает результат запроса после всех проверок
            return $this->checkErrors($sAnswer);
        }

        function getHistory(){
            // случайная строка длиной 20 символов
            $sNonce = substr(md5(time().rand()),0,20); 
            // Вычисляем строку подписи
            $sSignString = 
                $this->secret."&".$this->AGID."&".$sNonce;
            // Вычисляем подпись
            $sSignature = base64_encode(hash_hmac(
                'sha256',
                $sSignString,
                $this->secret,
                true
            ));
            // Формируем запрос
            $sRequest = json_encode([
                "ag_id"=>  $this->AGID,
                "nonce"     =>  $sNonce,
                "signature" =>  $sSignature
            ]);

            // Получаем ответ от API
            $this->setMethod("/mvag/billing/getHistory");
            $sAnswer = $this->request(
                $this->url.":".$this->port.$this->getMethod(),
                $sRequest
            );
            // Возвращает результат запроса после всех проверок
            return $this->checkErrors($sAnswer);
        }

        function addPoints($nPoints, $sComment){

            if(!$nPoints = intval($nPoints))
                return riseError('Некорректная сумма транзакции');

            if($nPoints>0){
                $sAction = 'debit';
            }
            else{
                $nPoints = -1*$nPoints;
                $sAction = 'credit';
            }

            // случайная строка длиной 20 символов
            $sNonce = substr(md5(time().rand()),0,20); 
            // Вычисляем строку подписи
            $sSignString = 
                $this->secret."&".$sAction."&".$this->AGID
                ."&".$nPoints."&".$sComment."&".$sNonce
            ;
            // Вычисляем подпись
            $sSignature = base64_encode(hash_hmac(
                'sha256',
                $sSignString,
                $this->secret,
                true
            ));
            // Формируем запрос
            $sRequest = json_encode([
                "ag_id"     =>  $this->AGID,
                "title"     =>  $sComment,
                "points"    =>  $nPoints,
                "action"    =>  $sAction,
                "nonce"     =>  $sNonce,
                "signature" =>  $sSignature
            ]);

            // Получаем ответ от API
            $this->setMethod("/mvag/billing/add");
            $sAnswer = $this->request(
                $this->url.":".$this->port.$this->getMethod(),
                $sRequest
            );
            // Возвращает результат запроса после всех проверок
            return $this->checkErrors($sAnswer);
            
        }

        function checkErrors($sAnswer){
            $objAnswer = json_decode($sAnswer);
            $arAnswer = json_decode(json_encode((array)$objAnswer), TRUE);

            if(!$arAnswer)
                return $this->riseError('Ошибка парсинга JSON-ответа');

            if(
                isset($arAnswer['errorCode'])
                &&
                $arAnswer['errorCode']!=0
                &&
                isset($arAnswer['errorMessage'])
                &&
                trim($arAnswer['errorMessage'])
            )
                return $this->riseError( "Ошибка АГ API: "
                    .$arAnswer['errorMessage']);

            if( !isset($arAnswer['errorCode']))
                return $this->riseError( "Не получен корректный код ошибки");
            
            if(
                !isset($arAnswer["result"])
                ||
                !$arAnswer["result"]
            )return $this->riseError(
                "Не получен результат запроса метода "
                .$this->getMethod()
            );

            return $arAnswer["result"];            
        }

        function request($sUrl, $sRequest){
            
            $objCurl = new curlTool;
            $sData = $objCurl->post($sUrl, $sRequest);         

            // Пишем в лог
            $sFilename = realpath(
                dirname(__FILE__)."/../../../.."
            )."/logs/agapi/".date("Y-m-d").".log";
            $fd = fopen($sFilename,"a");
            fwrite(
                $fd,
                date("Y-m-d H:i:s")
                ."\t".$sUrl
                ."\t".$sRequest
                ."\t".$sData
                ."\n"
            );
            fclose($fd);
            return $sData;
        }

        private function riseError($sError){
            $this->error = date("Y-m-d H:i:s")." ".$sError;
            return false;
        }


        private function setMethod($sMethod){
            $this->method = $sMethod;
        }

        private function getMethod(){
            return $this->method;
        }


    }
