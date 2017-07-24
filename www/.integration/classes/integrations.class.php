<?
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/bitrix/modules/main/include/prolog_before.php");
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/.integration/classes/curllogger.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"].
        "/.integration/classes/integrationSettings.class.php");

    class CIntegration {
        var $transact = '';     //!< ID транзакции
        var $phone = '';        //!< Номер телефона владельца банк.карты
        var $emulation = false; //!< Режим эмуляции ('success','failed', false)
        var $error  = '';       //!< Текст последней ошибки
        var $errorNo= 0;        //!< Номер ошибки (может отсутствовать


        /**
            Добавление свойства заказу по его номеру и коду свойства
        */
        function setPropertyByOrderNum(
            $nOrderNum,
            $sPropertyCode,
            $sPropertyValue
        ){
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
                        "CODE"           => $sPropertyCode 
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
                $arFilter["VALUE"] = $sPropertyValue;
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
            elseif($sPropertyValue){
                $arFilter["VALUE"] = $aPropertyValue;
                if(!CSaleOrderPropsValue::Add($arFilter)){
                    $this->error = ''
                        .__CLASS__.":"
                        .__LINE__.":"
                        ."Ошибка добавления свойства заказа";
                    return false;
                }
            }
            return true;

        }


        /**
            Получение свойства заказа по номеру заказа и коду свойства
        */
        function getPropertyByOrderNum(
            $sOrderNum,
            $sPropertyCode
        ){
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
                        "CODE"           => $sPropertyCode 
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
                    ."$sPropertyCode для заказа '".$arOrder["ID"].
                    "' не существует";
                return false;
            }

            return $arExistPropValue["VALUE"];
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
          
    }
