<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/.integration/classes/curllogger.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/.integration/classes/integrationSettings.class.php");


    class CTroyka{
        var $number = false;    //!< Номер карты тройка
        var $transact = '';     //!< ID транзакции
        var $simpeMode= false;  //!< Упрощенный режим запросов (2 из 5)
        var $phone = '';        //!< Номер телефона владельца банк.карты
        var $emulation = false; //!< Режим эмуляции ('success','failed', false)
        var $cvc = '';          //!< CVC банковской карты
        var $error  = '';       //!< Текст последней ошибки
        var $errorNo= 0;        //!< Номер ошибки (может отсутствовать
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

        function __construct($sNum='',$sPhone=''){
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");

            $objSettings = new CIntegrationSettings('TROYKA');
            $arSettings = $objSettings->get(); 

            if(!$sNum)$sNum = $arSettings["TROYKA_CARD"]["VALUE"];
            if(!$sPhone)$sPhone = $arSettings["TROYKA_PHONE"]["VALUE"];

            CModule::IncludeModule("sale");
            if(!$this->checkTroyka($sNum))return false;
            if(!$this->checkPhone($sPhone))return false;

            // Предустановленные значения
            $this->cvc      = $arSettings["TROYKA_CVC"]["VALUE"];
            $this->number   = $sNum;
            $this->phone    = $sPhone;
            $this->pemPath  = $TROYKA_PEM_PATH;
            $this->url      = $arSettings["TROYKA_URL"]["VALUE"];
            $this->currentVersion   =
            $arSettings["TROYKA_CURRENT_VERSION"]["VALUE"];
            $this->partnerMdOrder   =
            $arSettings["TROYKA_PARTNER_MD_ORDER"]["VALUE"];
            $this->amount           = $arSettings["TROYKA_AMOUNT"]["VALUE"];
            $this->currency         = $arSettings["TROYKA_CURRENCY"]["VALUE"];
            $this->serviceId        = $arSettings["TROYKA_SERVICE_ID"]["VALUE"];
            $this->ip               = $arSettings["TROYKA_IP"]["VALUE"];
            $this->bindingId        = $arSettings["TROYKA_BINDING_ID"]["VALUE"];
            $this->emulation        = $arSettings["TROYKA_EMULATION"]["VALUE"];
            $this->simleMode        =
               boolval(intval($arSettings["TROYKA_SIMPLE_MODE"]["VALUE"]));


            return true;
        }

        /**
            wsdl-запрос на получение списка карт
            @return массив ассоциативных массивов с информацией о карте
        */
        function getBindings(
            $sOrderNum  //!< Номер заказа в рамках которого идет запрос
        ){
            if(!$this->checkOrderNum($sOrderNum))return false;
            $objSoap = new SoapClient(
                $this->url,
                array(
                    'trace'=>1,
                    'local_cert'=>$this->pemPath
                )
            );

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
                $arSoapResult = $objSoap->__soapCall("getBindings",$arSoapRequest);
            }
            
            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $arSoapResult);

            if($this->getWsdlErrorInfo($arSoapResult))
                return false;

            // Ищем свежую активную карту и прописываем её вместо карты по
            // умолчанию
            if(isset($arSoapResult["bindings"])){
                foreach($arSoapResult["bindings"] as $arBinding){
                    if(
                        isset($arBinding["bindingStatus"])
                        &&
                        $arBinding["bindingStatus"]==0
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

            $objSoap = new SoapClient(
                $this->url,
                array(
                    'trace'=>1,
                    'local_cert'=>$this->pemPath
                )
            );

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
                $arSoapResult = 
                    $objSoap->__soapCall("checkProviders",$arSoapRequest);
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

            $objSoap = new SoapClient(
                $this->url,
                array(
                    'trace'=>1,
                    'local_cert'=>$this->pemPath
                )
            );
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

            $objSoap = new SoapClient(
                $this->url,
                array(
                    'trace'=>1,
                    'local_cert'=>$this->pemPath
                )
            );

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
            }
            else{
                $arSoapResult = $objSoap->__soapCall(
                    "getPaymentCapabilities",
                    $arSoapRequest
                );
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

            if(!$this->simleMode){
                $this->getBindings($sOrderNum);
                $this->checkProviders($sOrderNum);
                $this->getProviders($sOrderNum);
            }
            $this->getPaymentCapabilities($sOrderNum);

            $objSoap = new SoapClient(
                $this->url,
                array(
                    'trace'=>1,
                    'local_cert'=>$this->pemPath
                )
            );

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
                json_decode('{"simulation":1,"errorCode":24,"errorDesc":"5","mdOrder":"25313421236581","completedPayment":{"date":"2017-07-20T10:49:47.065+03:00","refnum":"236161345345","approvalCode":"000000","currency":643,"bindingId":"ED53FFED3B6C444280C1F8E93A642599","maskedPan":"252319******253569"cardType":1,"serviceId":"322","serviceParams":{"name":"Ticket","value":"3996128752"},"transactionAmount":{"base":"175.00","total":"175.00","fee":"0.00","currency":643}}}');
            }
            else{
                $arSoapResult = $objSoap->__soapCall( "payment",$arSoapRequest);
            }

            
            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog( $this->url, $sOrderNum, $arSoapRequest, $arSoapResult);

            // Сохраняем номер транзакции
            if(isset($arSoapResult["completedPayment"]["refnum"]))
                $this->transact = $arSoapResult["completedPayment"]["refnum"];

            if($arErrorInfo = $this->getWsdlErrorInfo($arSoapResult))
                return false;
            
            
            return true;
        }

        /**
            привязки номера тройки к заказу
        */
        function linkOrder(
            $nOrderNum,      // Номер заказа
            $sTroykaNum = '-'// ПУстой номер карты (только для автотеста)
        ){
            if($sTroykaNum!='-')
                $this->number = $sTroykaNum;
            $this->error = ''; 
            if(!$this->checkOrderNum($nOrderNum))return false;

            $arOrder = CSaleOrder::GetList(
                array(),
                array("ADDITIONAL_INFO"=>$nOrderNum),
                false,
                array("nTopCount"=>1),
                array("ID")
            )->Fetch();
            if(!isset($arOrder["ID"])){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Заказ с номером '$nOrderNum' не существует";
                return false;
            }

            $arPropGroup = CSaleOrderPropsGroup::GetList(
                array(),
                $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
                false,
                array("nTopCount"=>1)
            )->GetNext();
            $nPropGroup = $arPropGroup["ID"];


            $arPropValue = CSaleOrderProps::GetList(
                array("SORT" => "ASC"),
                array(
                        "ORDER_ID"       => $arOrder["ID"],
                        "PERSON_TYPE_ID" => 1,
                        "PROPS_GROUP_ID" => $nPropGroup,
                        "CODE"           => "TROIKA" 
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();

            $arFilter = array(
                "ORDER_ID"      =>  $arOrder["ID"],
                "ORDER_PROPS_ID"=>  $arPropValue["ID"],
                "CODE"          =>  $arPropValue["CODE"],
                "NAME"          =>  $arPropValue["NAME"]
            );
            if(
                $arExistPropValue = 
                CSaleOrderPropsValue::GetList(Array(), $arFilter)->GetNext()
            ){
                $arFilter["VALUE"] = $this->number;
                if(!CSaleOrderPropsValue::Update(
                    $arExistPropValue["ID"],
                    $arFilter 
                )){
                    $this->error = ''
                        .__CLASS__.":"
                        .__LINE__.":"
                        ."Ошибка обновления свойства заказа";
                    return false;
                }
            }
            elseif($this->number){
                $arFilter["VALUE"] = $this->number;
                if(!CSaleOrderPropsValue::Add($arFilter)){
                    $this->error = ''
                        .__CLASS__.":"
                        .__LINE__.":"
                        ."Ошибка добавления свойства заказа";
                    return false;
                }
            }
        }

        /**
            привязки номера транзакции к заказу
        */
        function linkOrderTransact(
            $nOrderNum,      // Номер заказа
            $sTransactNum = '-'// ПУстой номер транзакции (только для автотеста)
        ){
            if($sTransactNum!='-')
                $this->transact = $sTransactNum;
            $this->error = ''; 
            if(!$this->checkOrderNum($nOrderNum))return false;

            $arOrder = CSaleOrder::GetList(
                array(),
                array("ADDITIONAL_INFO"=>$nOrderNum),
                false,
                array("nTopCount"=>1),
                array("ID")
            )->Fetch();
            if(!isset($arOrder["ID"])){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Заказ с номером '$nOrderNum' не существует";
                return false;
            }

            $arPropGroup = CSaleOrderPropsGroup::GetList(
                array(),
                $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
                false,
                array("nTopCount"=>1)
            )->GetNext();
            $nPropGroup = $arPropGroup["ID"];


            $arPropValue = CSaleOrderProps::GetList(
                array("SORT" => "ASC"),
                array(
                        "ORDER_ID"       => $arOrder["ID"],
                        "PERSON_TYPE_ID" => 1,
                        "PROPS_GROUP_ID" => $nPropGroup,
                        "CODE"           => "TROIKA_TRANSACT_ID" 
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();

            $arFilter = array(
                "ORDER_ID"      =>  $arOrder["ID"],
                "ORDER_PROPS_ID"=>  $arPropValue["ID"],
                "CODE"          =>  $arPropValue["CODE"],
                "NAME"          =>  $arPropValue["NAME"]
            );
            if(
                $arExistPropValue = 
                CSaleOrderPropsValue::GetList(Array(), $arFilter)->GetNext()
            ){
                $arFilter["VALUE"] = $this->transact;
                if(!CSaleOrderPropsValue::Update(
                    $arExistPropValue["ID"],
                    $arFilter 
                )){
                    $this->error = ''
                        .__CLASS__.":"
                        .__LINE__.":"
                        ."Ошибка обновления свойства заказа";
                    return false;
                }
            }
            elseif($this->number){
                $arFilter["VALUE"] = $this->transact;
                if(!CSaleOrderPropsValue::Add($arFilter)){
                    $this->error = ''
                        .__CLASS__.":"
                        .__LINE__.":"
                        ."Ошибка добавления свойства заказа";
                    return false;
                }
            }
        }
        /**
            Получение номера тройки по номеру заказа
        */
        function getTroykaNum($sOrderNum){
            $this->error = ''; 
            if(!$this->checkOrderNum($sOrderNum))return false;
            
            $arOrder = CSaleOrder::GetList(
                array(),
                array("ADDITIONAL_INFO"=>$sOrderNum),
                false,
                array("nTopCount"=>1),
                array("ID","ADDITIONAL_INFO")
            )->Fetch();

            if(!isset($arOrder["ID"])){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Заказ с номером '$sOrderNum' не существует";
                return false;
            }

            $arPropGroup = CSaleOrderPropsGroup::GetList(
                array(),
                $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
                false,
                array("nTopCount"=>1)
            )->GetNext();
            $nPropGroup = $arPropGroup["ID"];


            $arPropValue = CSaleOrderProps::GetList(
                array("SORT" => "ASC"),
                array(
                        "ORDER_ID"       => $arOrder["ID"],
                        "PERSON_TYPE_ID" => 1,
                        "PROPS_GROUP_ID" => $nPropGroup,
                        "CODE"           => "TROIKA" 
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();

            $arFilter = array(
                "ORDER_ID"      =>  $arOrder["ID"],
                "ORDER_PROPS_ID"=>  $arPropValue["ID"],
                "CODE"          =>  $arPropValue["CODE"],
                "NAME"          =>  $arPropValue["NAME"]
            );
            $arExistPropValue = 
            CSaleOrderPropsValue::GetList(Array(), $arFilter)->Fetch();

            if(
                !isset($arExistPropValue["VALUE"])
                ||
                !$arExistPropValue["VALUE"]
            ){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Номер тройки для заказа '".$arOrder["ID"]."' не указан";
                return false;
            }

            return $arExistPropValue["VALUE"];
        }

        /**
            Получение номера транзакции по номеру заказа
        */
        function getTroykaTransactNum($sOrderNum){
            $this->error = ''; 
            if(!$this->checkOrderNum($sOrderNum))return false;
            
            $arOrder = CSaleOrder::GetList(
                array(),
                array("ADDITIONAL_INFO"=>$sOrderNum),
                false,
                array("nTopCount"=>1),
                array("ID","ADDITIONAL_INFO")
            )->Fetch();

            if(!isset($arOrder["ID"])){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Заказ с номером '$sOrderNum' не существует";
                return false;
            }

            $arPropGroup = CSaleOrderPropsGroup::GetList(
                array(),
                $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
                false,
                array("nTopCount"=>1)
            )->GetNext();
            $nPropGroup = $arPropGroup["ID"];


            $arPropValue = CSaleOrderProps::GetList(
                array("SORT" => "ASC"),
                array(
                        "ORDER_ID"       => $arOrder["ID"],
                        "PERSON_TYPE_ID" => 1,
                        "PROPS_GROUP_ID" => $nPropGroup,
                        "CODE"           => "TROIKA_TRANSACT_ID" 
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();

            $arFilter = array(
                "ORDER_ID"      =>  $arOrder["ID"],
                "ORDER_PROPS_ID"=>  $arPropValue["ID"],
                "CODE"          =>  $arPropValue["CODE"],
                "NAME"          =>  $arPropValue["NAME"]
            );
            $arExistPropValue = 
            CSaleOrderPropsValue::GetList(Array(), $arFilter)->Fetch();

            if(
                !isset($arExistPropValue["VALUE"])
                ||
                !$arExistPropValue["VALUE"]
            ){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Номер транзакции тройки для заказа '".
                        $arOrder["ID"]."' не указан";
                return false;
            }

            return $arExistPropValue["VALUE"];
        }
        /**
            Запись запросов в лог

            @return ID лога или false в случае неуспеха
        */
        function curlLog(
            $sUrl,      //!< запроса
            $sOrderid,  //!< Номер заказа к которому относится запрос
            $sInput,    //!< Тело запроса
            $sOutput    //!< Тело ответа
        ){
            $this->error = '';
            $objCurlLogger = new CCurlLogger();
            if(!$nLogId = $objCurlLogger->addLog(array(
                "ORDER_NUM" =>  $sOrderid,
                "URL"       =>  $sUrl,
                "DATA"      =>  json_encode($sOutput),
                "POST_DATA" =>  json_encode($sInput)
            ))){
                $this->error = $objCurlLogger->error;
                return false;
            }

            return $nLogId;
        }

        /**
            Проверка номера заказа

            @return true, если номер корректен и fasle в обратном случае
            $this->error будет содержать текст ошибки
        */
        function checkOrderNum(
            $sOrderNum //!< Номер заказа
        ){
            $this->error = '';
            $this->errorNo = 0;
            if(!preg_match("#^(Б\-\d+|\d+)$#",$sOrderNum)){
                $arBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,1);
                $this->error = ''
                    .$arBacktrace[0]['class'].":"
                    .$arBacktrace[0]['line'].":"
                    ."Некорректный номер заказа";
                return false;
            }
            return true;
        }

        /**
            Проверка номера телефона (должно быть 10 цифр)

            @return true, если номер корректен и fasle в обратном случае
            $this->error будет содержать текст ошибки
        */
        function checkPhone(
            $sPhone //!< Номер заказа
        ){
            $this->error = '';
            $this->errorNo = 0;
            if(!preg_match("#^\d{10}$#",$sPhone)){
                $arBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,1);
                $this->error = ''
                    .$arBacktrace[0]['class'].":"
                    .$arBacktrace[0]['line'].":"
                    ."Некорректный номер телефона";
                return false;
            }
            return true;
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
                "M",$arAnswer["ErrorMessage"],$arAnswer["messageText"]
            );

            return $arAnswer;
        }
    }

?>
