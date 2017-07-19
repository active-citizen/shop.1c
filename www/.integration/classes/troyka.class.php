<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/.integration/classes/curllogger.class.php");

    class CTroyka{
        var $number = false;    //!< Номер карты тройка
        var $transact = '';     //!< ID транзакции
        var $phone = '';        //!< Номер телефона владельца банк.карты
        var $cvc = '';          //!< CVC банковской карты
        var $error  = '';       //!< Текст последней ошибки
        var $errorNo= 0;        //!< Номер ошибки (может отсутствовать
        var $errorDesc=0;       //!< Из протокола тройки
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
            if(!$sNum)$sNum = $TROYKA_CARD;
            if(!$sPhone)$sPhone = $TROYKA_PHONE;

            CModule::IncludeModule("sale");
            if(!$this->checkTroyka($sNum))return false;
            if(!$this->checkPhone($sPhone))return false;

            // Предустановленные значения
            $this->cvc      = $TROYKA_CVC;
            $this->number   = $sNum;
            $this->phone    = $sPhone;
            $this->pemPath  = $TROYKA_PEM_PATH;
            $this->url      = $TROYKA_URL;
            $this->currentVersion   = $TROYKA_CURRENT_VERSION;
            $this->partnerMdOrder   = $TROYKA_PARTNER_MD_ORDER;
            $this->amount           = $TROYKA_AMOUNT;
            $this->currency         = $TROYKA_CURRENCY;
            $this->serviceId        = $TROYKA_SERVICE_ID;
            $this->ip               = $TROYKA_IP;
            $this->bindingId        = $TROYKA_BINDING_ID;

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
            
            $arSoapResult = $objSoap->__soapCall("getBindings",$arSoapRequest);

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
            
            $arSoapResult = $objSoap->__soapCall("checkProviders",$arSoapRequest);

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
            
            $arSoapResult = $objSoap->__soapCall("getProviders",$arSoapRequest);

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
          
            $arSoapResult = $objSoap->__soapCall(
                "getPaymentCapabilities",
                $arSoapRequest
            );
  
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

            $this->getBindings($sOrderNum);
            $this->checkProviders($sOrderNum);
            $this->getProviders($sOrderNum);
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

            $arSoapResult = $objSoap->__soapCall( "payment",$arSoapRequest);
            
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
                if(isset($arSoapResult["errorDesc"]))
                    $sErrorDesc = $arSoapResult["errorDesc"];
            }
            elseif(
                !isset($arSoapResult["errorCode"]) 
            ){
                $sErrorText = 'Транзакция тройки не вернула кода ошибки';
            }
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

            return array(
                "errorCode" => $sErrorCode,
                "errorText" =>  $sErrorText,
                "errorDesc" =>  $sErrorDesc
            );

        }
    }

?>
