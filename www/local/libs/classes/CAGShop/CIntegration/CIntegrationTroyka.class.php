<?
    namespace Integration;
    require_once(realpath(dirname(__FILE__))."/CIntegration.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
    use AGShop\Integration as Integration;
    use AGShop\DB as DB;

    class CIntegrationTroyka extends \Integration\CIntegration{
        var $number = false;    //!< Номер карты тройка
        var $simpleMode= false;  //!< Упрощенный режим запросов (2 из 5)
        var $cvc = '';          //!< CVC банковской карты
        var $errorDesc=0;       //!< Из протокола тройки
        var $errorMessage=0;    //!< Из протокола тройки
        var $url    = '';       //!< Ссылка на wsdl-шлюз
        var $pemPath = '';      //!< Полный путь до сертификата
        var $currentVersion     //!< Номер версии базы поставщиков
            ='';
        var $bindingId = '';    //!< ID привязанной карты.
        var $partnerMdOrder = '';//!< номер операции на стороне Партнера 
        var $amount = '';       //!< Сумма заказа
        var $currency = '';     //!< Код валюты
        var $serviceId = '';    //!< Уникальный идентификатор поставщика
        var $ip = '';           //!< IP-адрес инициатора запроса 
        var $connection_timeout = 1;

        function __construct($sNum='',$sPhone=''){
            parent::__construct();
            if(function_exists('xdebug_disable')){ xdebug_disable(); };

            if(!$sNum)$sNum = $this->settings["TROYKA_CARD"]["VALUE"];
            if(!$sPhone)$sPhone = $this->settings["TROYKA_PHONE"]["VALUE"];

            if(!$this->checkTroyka($sNum))return false;
            if(!$this->checkPhone($sPhone))return false;

            // Предустановленные значения
            $this->cvc      = $this->settings["TROYKA_CVC"]["VALUE"];
            $this->number   = $sNum;
            $this->phone    = $sPhone;
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
            $this->pemPath  = $TROYKA_PEM_PATH;
            $this->url      = $this->settings["TROYKA_URL"]["VALUE"];
            $this->currentVersion   =
                $this->settings["TROYKA_CURRENT_VERSION"]["VALUE"];
            $this->partnerMdOrder   =
                $this->settings["TROYKA_PARTNER_MD_ORDER"]["VALUE"];
            $this->amount           = $this->settings["TROYKA_AMOUNT"]["VALUE"];
            $this->currency         = $this->settings["TROYKA_CURRENCY"]["VALUE"];
            $this->serviceId        = $this->settings["TROYKA_SERVICE_ID"]["VALUE"];
            $this->ip               = $this->settings["TROYKA_IP"]["VALUE"];
            $this->bindingId        = $this->settings["TROYKA_BINDING_ID"]["VALUE"];
            $this->emulation        = $this->settings["TROYKA_EMULATION"]["VALUE"];
            $this->simpleMode        =
               boolval(intval($arSettings["TROYKA_SIMPLE_MODE"]["VALUE"]));


            return true;
        }

        /**
            wsdl-запрос на получение списка карт
            @return массив ассоциативных массивов с информацией о карте
        */
        function getBindings(
            $sOrderNum=''  //!< Номер заказа в рамках которого идет запрос
        ){
            if($this->emulation!='success' && $this->emulation!='failed'){
                try{
                    $objSoap = new \SoapClient(
                        $this->url,
                        array(
                            'trace'=>1,
                            'local_cert'=>$this->pemPath,
                            'connection_timeout'=>$this->connection_timeout,
                            'exceptions'=>true
                        )
                    );
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, "testcard", $arSoapRequest, $e->getMessage());
                    return false;
                }
            }

            $arSoapRequest =  array(
                "getBindings"=>array(
                    "phone"=>$this->phone
                )
            ); 

            if($this->emulation=='success'){
                $arSoapResult =
                json_decode('{"simulation":1,"errorCode":0,"bindings":[{"bindingId":"ED33F6ED3B6744428031F8E43A642592","mnemonic":"ACTIVE CITIZEN 4","maskedPan":"439146******4621","cardType":1,"issuerGroup":"OWN","bindingStatus":0,"createdDate":"2016-03-24T18:26:31.791+03:00","updatedDate":"2017-07-19T18:44:34.082+03:00","is3DSecureBinding":false,"isDefaultBinding":true},{"bindingId":"1E372B5E37984980B64408517192236D","mnemonic":"ACTIVE CITIZEN 2","maskedPan":"124732******2834","cardType":1,"issuerGroup":"OWN","bindingStatus":3,"createdDate":"2016-03-24T17:32:37.634+03:00","updatedDate":"2016-03-30T13:27:30.039+03:00","is3DSecureBinding":false,"isDefaultBinding":false},{"bindingId":"91E334875218649A3758CC3DA8A0942B","mnemonic":"ACTIVE CITIZEN","maskedPan":"223522******4136","cardType":1,"issuerGroup":"OWN","bindingStatus":3,"createdDate":"2015-12-01T20:32:25.334+03:00","updatedDate":"2016-03-24T18:30:33.727+03:00","is3DSecureBinding":false,"isDefaultBinding":false}]}'); 
            }
            elseif($this->emulation=='failed'){
                $arSoapResult = json_decode('');
            }
            else{
                try{
                    $arSoapResult = $objSoap->__soapCall("getBindings",$arSoapRequest);
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, "testcard", $arSoapRequest, $arSoapResult);
                    return false;
                }
            }
            
            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog( $this->url, "testcard", $arSoapRequest, $arSoapResult);

            if($this->getWsdlErrorInfo($arSoapResult))
                return false;

            // Ищем свежую активную карту и прописываем её вместо карты по
            // умолчанию
            if(isset($arSoapResult["bindings"])){
                foreach($arSoapResult["bindings"] as $arBinding){
                    if(
                        isset($arBinding["isDefaultBinding"])
                        &&
                        $arBinding["isDefaultBinding"]==1
                        &&
                        isset($arBinding["bindingId"])
                        &&
                        trim($arBinding["bindingId"])
                    )
                    $this->bindingId = $arBinding["bindingId"];
                }
            }

            return $arSoapResult["bindings"];
        }
 
        /**
            wsdl-Проверка необходимости обновления перечня поставщиков 
        */
        function checkProviders(
            $sOrderNum  //!< Номер заказа в рамках которого идет запрос
        ){
            if(!$this->checkOrderNum($sOrderNum))return false;

            if($this->emulation!='success' && $this->emulation!='failed'){
                try{
                    $objSoap = new \SoapClient(
                        $this->url,
                        array(
                            'trace'=>1,
                            'local_cert'=>$this->pemPath,
                            'connection_timeout'=>$this->connection_timeout,
                            'exceptions'=>true
                        )
                    );
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, "testcard", $arSoapRequest, $e->getMessage());
                    return false;
                }
            }

            $arSoapRequest =  array(
                "checkProviders"=>array(
                    "phone"         =>$this->phone,
                    "currentVersion"=>$this->currentVersion

                )
            ); 

            if($this->emulation=='success'){
                $arSoapResult =
                json_decode('{"simulation":1,"errorCode":0,"updateRequired":true,"actual":false}');
            }
            elseif($this->emulation=='failed'){
            }
            else{
                try{
                $arSoapResult = 
                    $objSoap->__soapCall("checkProviders",$arSoapRequest);
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $e->getMessage());
                    return false;
                }
            }
            

            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $arSoapResult);

            if($this->getWsdlErrorInfo($arSoapResult))
                return false;
            
            
            return $arSoapResult;
        }


        /**
            wsdl-запрос на получение списка поставщиков
        */
        function getProviders(
            $sOrderNum  //!< Номер заказа в рамках которого идет запрос
        ){
            if(!$this->checkOrderNum($sOrderNum))return false;

            if($this->emulation!='success' && $this->emulation!='failed'){
                try{
                $objSoap = new \SoapClient(
                    $this->url,
                    array(
                        'trace'=>1,
                        'local_cert'=>$this->pemPath,
                        'connection_timeout'=>$this->connection_timeout
                    )
                );
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, "testcard", $arSoapRequest, $e->getMessage());
                    return false;
                }
            }
            $arSoapRequest =  array(
                "getProviders"=>array(
                    "phone"         =>$this->phone,
                    "currentVersion"=>$this->currentVersion

                )
            ); 
            
            if($this->emulation=='success'){
                $arSoapResult =
                json_decode('{"simulation":1,"errorCode":0,"iconsURL":"https:\/\/bmmobile.bm.ru\/bm\/icons\/","version":"2286","categories":{"categoryName":"transport","categoryTitle":"\u0422\u0440\u0430\u043d\u0441\u043f\u043e\u0440\u0442 \u0438 \u043f\u0430\u0440\u043a\u043e\u0432\u043a\u0438","icon":"transport.png","number":999,"categoryHidden":true,"payees":{"id":"322","payeeTitle":"\u0422\u0440\u043e\u0439\u043a\u0430 (BGW)","subline":"\u0414\u0435\u043f\u0430\u0440\u0442\u0430\u043c\u0435\u043d\u0442 \u0442\u0440\u0430\u043d\u0441\u043f\u043e\u0440\u0442\u0430 \u0438 \u0440\u0430\u0437\u0432\u0438\u0442\u0438\u044f \u0434\u043e\u0440\u043e\u0436\u043d\u043e-\u0442\u0440\u0430\u043d\u0441\u043f\u043e\u0440\u0442\u043d\u043e\u0439 \u0438\u043d\u0444\u0440\u0430\u0441\u0442\u0440\u0443\u043a\u0442\u0443\u0440\u044b \u0433.\u041c\u043e\u0441\u043a\u0432\u0430","icon":"transport.png","minAmount":"100.00","maxAmount":"30100.00","payeeHidden":true,"params":{"paramTitle":"\u041d\u043e\u043c\u0435\u0440 \u0431\u0438\u043b\u0435\u0442\u0430","paramName":"Ticket","paramType":"StringField","paramDescription":"\u041d\u043e\u043c\u0435\u0440 \u0431\u0438\u043b\u0435\u0442\u0430","paramHidden":false,"paramMask":"XXXXXXXXXX","regExp":"","maxLength":10,"serverValidation":false,"order":0}}}}');
            }
            elseif($this->emulation=='failed'){
            }
            else{
               $arSoapResult = $objSoap->__soapCall("getProviders",$arSoapRequest);
            }


            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $arSoapResult);

            if($this->getWsdlErrorInfo($arSoapResult))
                return false;
            
            return $arSoapResult;
        }

        /**
            wsdl-Запрос  расчета комиссии и лимитов на платеж
        */
        function getPaymentCapabilities(
            $sOrderNum  //!< Номер заказа в рамках которого идет запрос
        ){
            if(!$this->checkOrderNum($sOrderNum))return false;

            if($this->emulation!='success' && $this->emulation!='failed'){
                try{
                $objSoap = new \SoapClient(
                    $this->url,
                    array(
                        'trace'=>1,
                        'local_cert'=>$this->pemPath,
                        'connection_timeout'=>$this->connection_timeout,
                        'exceptions'=>true
                    )
                );
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, "testcard", $arSoapRequest, $e->getMessage());
                    return false;
                }
            }

            include_once(
                _SERVER["DOCUMENT_ROOT"]."/local/php_interface/settings.inc.php"
            );
            $sMdOrder =
                CONTOUR."-".
                str_replace("Б","b",$sOrderNum)
                ."-".date("Y")."-".date("m")."-".date("d")."-".date("H")."-".date("i");
            $arSoapRequest =  array(
                "getPaymentCapabilities"=>array(
                    "phone"         =>  $this->phone,
                    "bindingId"     =>  $this->bindingId, 
                    "partnerMdOrder"=>  $sMdOrder,
                    "amount"        =>  $this->amount,
                    "currency"      =>  $this->currency,
                    "serviceId"     =>  $this->serviceId,
                    "serviceParams" =>  array(
                        "name"  =>Ticket,
                        //!!!!!!!!!!!! Вот она!!!! Тройка моей мечты!!!!!!!!
                        "value" =>$this->number
                    ),
                    "ip"            =>  $this->ip
                )
            ); 
          
            if($this->emulation=='success'){
                $arSoapResult =
                json_decode('{"simulation":1,"errorCode":0,"bindings":{"mdOrder":"18264726402847","bindingId":"1D53F5ED2B67444289C148E73A342690","mnemonic":"ACTIVE CITIZEN 3","maskedPan":"326722******2492","cardType":1,"userSelected":true,"cvcRequired":true,"transactionAmount":{"base":"175.00","total":"175.00","fee":"0.00","currency":643}}}'); 
            }
            elseif($this->emulation=='failed'){
                $arSoapResult = 
                json_decode('{"simulation":1,"errorCode":0,"bindings":{"mdOrder":"32584575278936","bindingId":"2D638F3D30644943846128793A942291","mnemonic":"ACTIVE CITIZEN 3","maskedPan":"251944******2462","cardType":1,"userSelected":true,"cvcRequired":true,"transactionAmount":{"base":"175.00","total":"175.00","fee":"0.00","currency":643}}}');
            }
            else{
                try{
                    $arSoapResult = $objSoap->__soapCall(
                       "getPaymentCapabilities",
                       $arSoapRequest
                   );
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $e->getMessage());
                    return false;
                }
            }

  
            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);
            $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $arSoapResult);

            if($this->getWsdlErrorInfo($arSoapResult))
                return false;
            

            if(isset($arSoapResult["bindings"]["mdOrder"]))
                $this->partnerMdOrder = $arSoapResult["bindings"]["mdOrder"];

            return $arSoapResult["bindings"];
        }

         /**
            wsdl - запрос оплаты карты тройка
         */
         function payment($sOrderNum){

            if(
                !$this->simpleMode
            ){
                if(!$this->getBindings($sOrderNum))return false;
                if(!$this->checkProviders($sOrderNum))return false;
                if(!$this->getProviders($sOrderNum))return false;
            }
            $this->getPaymentCapabilities($sOrderNum);

            if($this->emulation!='success' && $this->emulation!='failed'){
                try{
                $objSoap = new \SoapClient(
                    $this->url,
                    array(
                        'local_cert'=>$this->pemPath,
                        'connection_timeout'=>$this->connection_timeout,
                        'exceptions'=>true
                    )
                );
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $e->getMessage());
                    return false;
                }
            }

            $arSoapRequest =  array(
                "payment"=>array(
                    "phone"     =>  $this->phone,
                    "mdOrder"   =>  $this->partnerMdOrder,
                    "cvc"       =>  $this->cvc
                )
            ); 

            if($this->emulation=='success'){
                $arSoapResult =
                json_decode('{"simulation":1,"errorCode":0,"mdOrder":"16025406783408","completedPayment":{"date":"2017-07-20T11:00:59.028+03:00","refnum":"523156836772","approvalCode":"422344","currency":643,"bindingId":"2D134F1D6B9C3447803178095A842091","maskedPan":"124732******5322","cardType":1,"serviceId":"322","serviceParams":{"name":"Ticket","value":"0000123712"},"transactionAmount":{"base":"175.00","total":"175.00","fee":"0.00","currency":643}}}');
            }
            elseif($this->emulation=='failed'){
                $arSoapResult = 
                json_decode('{"simulation":1,"errorCode":24,"errorDesc":"5","mdOrder":"21234535734580","completedPayment":{"date":"2017-07-20T10:49:47.065+03:00","refnum":"456366724866","approvalCode":"000000","currency":643,"bindingId":"1D337F3D3267424782C1881937646598","maskedPan":"121532******1312","cardType":1,"serviceId":"322","serviceParams":{"name":"Ticket","value":"7363455345"},"transactionAmount":{"base":"175.00","total":"175.00","fee":"0.00","currency":643}}}');
            }
            else{
                try{
                   $arSoapResult = $objSoap->__soapCall( "payment",$arSoapRequest);
                }
                catch(\Exception $e){
                    $this->errorMessage = htmlspecialchars($e->getMessage());
                    $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $e->getMessage());
                    return false;            
                }
            }

            
            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $arSoapResult);

            $this->transact = false;
            // Сохраняем номер транзакции
            if(isset($arSoapResult["completedPayment"]["refnum"]))
                $this->transact = $arSoapResult["completedPayment"]["refnum"];

            if(!$this->transact)return false;

            if($arErrorInfo = $this->getWsdlErrorInfo($arSoapResult))
                return false;
            
            
            return true;
        }


        /**
            Получение номера тройки по номеру заказа
        */
        function getTroykaNum($sOrderNum){
            return $this->getPropertyByOrderNum($sOrderNum, "TROIKA");
        }

        /**
            Получение номера транзакции по номеру заказа
        */
        function getTroykaTransactNum($sOrderNum){
            return $this->getPropertyByOrderNum($sOrderNum, "TROIKA_TRANSACT_ID");
        }

        /**
            Проверка номера карты тройка (должно быть 10 цифр)

            @return true, если номер корректен и fasle в обратном случае
            $this->error будет содержать текст ошибки
        */
        function checkTroyka(
            $sTroyka //!< Номер заказа
        ){
            $this->error = '';
            $this->errorNo = 0;
            if(!preg_match("#^\d{10}$#",$sTroyka)){
                $arBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,1);
                $this->error = ''
                    .$arBacktrace[0]['class'].":"
                    .$arBacktrace[0]['line'].":"
                    ."Некорректный номер карты тройка '$sTroyka'";
                return false;
            }
            return true;
        }

        /**
            Определяем была ли ошибка и возвращаем её параметры
            @return Если ошибок не было - false. При ошибках - массив информации
            вида
            array(
                "errorCode" =>  "wsdl-код ошибки"
                "errorText" =>  "Текст ошибки"
            )
        */
        private function getWsdlErrorInfo(
            $arSoapResult   //!< Массив ответа от wsdl
        ){
            $sErrorText = '';
            $sErrorCode = 0;
            $sErrorDesc  = 0;
            $sErrorMessage = '';
            if(
                isset($arSoapResult["errorCode"]) 
                &&
                $arSoapResult["errorCode"]==0
            ){ 
                return false;
            }
            elseif(
                isset($arSoapResult["errorCode"]) 
                & $sErrorCode = intval($arSoapResult["errorCode"])
            ){
                $sErrorText = 'Код ошибки транзации Тройки '.
                    $arSoapResult["errorCode"];
            }
            elseif(
                !isset($arSoapResult["errorCode"]) 
            ){
                $sErrorText = 'Транзакция тройки не вернула кода ошибки';
                $sErrorCode = 1;
            }

            if(isset($arSoapResult["errorDesc"]))
                $sErrorDesc = $arSoapResult["errorDesc"];
            if(isset($arSoapResult["errorMessage"]))
                $sErrorMessage = $arSoapResult["errorMessage"];

            $arLines = file(realpath(dirname(__FILE__))."/troyka_error_codes.txt");
            foreach($arLines as $sLine){
                $tmp = explode("{tab}",$sLine);
                if(!$sCode = intval(trim($tmp[0])))continue;
                if($sCode==$sErrorCode && isset($tmp[2]) &&$tmp[2]){
                    $sErrorText = $tmp[2];
                    break;
                }

            }

            $this->error = $sErrorText;
            $this->errorNo = $sErrorCode;
            $this->errorDesc = $sErrorDesc;
            $this->errorMessage = $sErrorMessage;

            return array(
                "errorCode" => $sErrorCode,
                "errorText" =>  $sErrorText,
                "errorDesc" =>  $sErrorDesc
            );

        }

        /**
            Маппинг ошибок по errorCode и errorDesc из  протокола тройки
            @return array(
                `errorCode`,
                `errorDesc`,
                `ErrorMessage`,
                `errorValue`,
                `messageType`,
                `messageText`,
                `errorCodeCOTT`,
                `errorTextCOTT`,
                `recomendCOTT`,
                `userMessegeCOTT`
                 
            )
        */
        function errorMapping(
            $sErrorCode = '',   
            $sErrorDesc = '',
            $sErrorMessage = ''
        ){
            global $DB;
            if(!$sErrorCode)         
                $sErrorCode = $this->errorNo;
            if(!$sErrorDesc)         
                $sErrorDesc = $this->errorDesc;
            if(!$sErrorMessage)         
                $sErrorMessage = $this->errorMessage;

            $sQuery = "SELECT * FROM `int_troika_error_mapping` WHERE 1";
            $sQuery.= " AND `errorCode`='".$DB->ForSql($sErrorCode)."' ";
            if($sErrorDesc)
                $sQuery.= " AND `errorDesc`='".$DB->ForSql($sErrorDesc)."' ";
            if($sErrorMessage)
                $sQuery.= " AND `errorMessage`='".$DB->ForSql($sErrorMessage)."' ";
            $sQuery .= " LIMIT 1";
            
            $arAnswer = $DB->Query($sQuery)->Fetch();
            $arAnswer["messageText"] = str_replace(
                "C",$arAnswer["errorCode"],$arAnswer["messageText"]
            );
            $arAnswer["messageText"] = str_replace(
                "D",$arAnswer["errorDesc"],$arAnswer["messageText"]
            );
            $arAnswer["messageText"] = str_replace(
                ",M",
                "",
                $arAnswer["messageText"]
            );

            return $arAnswer;
        }

        /**
            Выдача списка троек по номеру телефона
        */
        function unlinkCardByPhone($nCardNum){
            $CDB = new \DB\CDB;
            $CDB->delete("int_troika_link",["cardnum"=>$nCardNum]);
        }

        /**
            Отвязыкание номера тройки от телефона
        */
        function getCardsByPhone($nPhoneNum){
            $CDB = new \DB\CDB;
            return $CDB->searchAll("int_troika_link",["login"=>$nPhoneNum]);
        }

    }

?>
