<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/.integration/classes/curllogger.class.php");

    class CTroyka{
        var $number = false;    // Номер карты тройка
        var $error = '';
        var $url = 'https://bmmobile.bm.ru/bm/api/ws/3.2/actions?wsdl';
        var $pemPath = '';


        function __construct($nNum){

            CModule::IncludeModule("sale");

            if(!preg_match("#^\d{10}$#",$nNum)){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Некорректный номер тройки";
                return false;
            }

            $this->crtPath =
               $_SERVER["DOCUMENT_ROOT"]."/.integration/troyka.crt";

            $this->pemPath =
               $_SERVER["DOCUMENT_ROOT"]."/.integration/troyka.pem";

            $this->keyPath =
               $_SERVER["DOCUMENT_ROOT"]."/.integration/troyka.key";

            $this->number = $nNum;

            return true;
        }

        /*
            Получение списка карт
        */
        function getBindings($sPhone, $sOrderNum = '0000000000'){
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
            $objSoap = new SoapClient(
                $this->url,
                array(
                    'trace'=>1,
                    'local_cert'=>$this->pemPath
                )
            );
            $arSoapResult = $objSoap->__soapCall(
                "getBindings", $arSoapRequest =  array(
                    "getBindings"=>array(
                        "phone"=>$TROYKA_PHONE
                    )
                ) 
            );

            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog(
                $this->url,
                $sOrderNum,
                $arSoapRequest,
                $arSoapResult
            );

            if(!isset($arSoapResult["bindings"]) || !$arSoapResult["bindings"]){
                $this->error = ''  
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Не закреплено ни одной карты";
                return false;
            }

            return $arSoapResult["bindings"];
        }
   
        /**
            привязки номера тройки к заказу
        */
        function linkOrder(
            $nOrderNum,      // Номер заказа
            $sTroykaNum = '-'
        ){
            if($sTroykaNum=='-')
                $sTroykaNum = $this->number;
            $this->error = ''; 
            if(!preg_match("#^(Б\-\d+|\d+)$#",$nOrderNum)){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Некорректный номер заказа";
                return false;
            }

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
                $arFilter["VALUE"] = $sTroykaNum;
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
            elseif($sTroykaNum){
                $arFilter["VALUE"] = $sTroykaNum;
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
            if(!preg_match("#^(Б\-\d+|\d+)$#",$sOrderNum)){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Некорректный номер заказа";
                return false;
            }

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

        function payment($sOrderNum){
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");
            $this->error = ''; 
            if(!preg_match("#^(Б\-\d+|\d+)$#",$sOrderNum)){
                $this->error = ''
                    .__CLASS__.":"
                    .__LINE__.":"
                    ."Некорректный номер заказа";
                return false;
            }

            $arSoapRequest =  array(
                "payment"=>array(
                    "phone"     =>  $TROYKA_CARD,
                    "mdOrder"   =>  $this->number,
                    "cvc"       =>  $TROYKA_CVC
                )
            ); 

            $arSoapResult = array();
            /*
            $arSoapResult = $objSoap->__soapCall(
                "payment",             
            );
            */

            $arSoapResult = json_decode(json_encode((array)$arSoapResult), TRUE);

            $this->curlLog(
                $this->url,
                $sOrderNum,
                $arSoapRequest,
                $arSoapResult
            );
            
            if(
                isset($arSoapResult["errorCode"]) 
                &&
                $arSoapResult["errorCode"]==0
            ){ 
                return true;
            }
            elseif(
                isset($arSoapResult["errorCode"]) 
                & $sErrorCode = intval($arSoapResult["errorCode"])
            ){
                $this->error = 'Код ошибки транзации Тройки '.
                    $arSoapResult["errorCode"];
            }
            elseif(
                !isset($arSoapResult["errorCode"]) 
            ){
                $this->error = 'Транзакция тройки не вернула кода ошибки';
            }
            return false;
        }

        /**
            Запись запросов в лог
        */
        private function curlLog($sUrl,$sOrderid,$sInput,$sOutput){
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

    }

?>
