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
        var $settings = array();//!< Настройки, полученные из БД
        var $debug = true;      //!< Режим отладки
        var $mnemonic = '';     //!< Мнемоника класса для всяких префиксов     


        function __construct(){
            // Получаем настройки из БД
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");

            $this->mnemonic = mb_strtoupper(mb_substr(get_called_class(),1));

            $objSettings = new CIntegrationSettings($this->mnemonic);
            if($objSettings->error)$this->error = $objSettings->error;
            $this->settings = $objSettings->get(); 
            unset($objSettings);
        }

        /**
            Добавление свойства заказу по его номеру и коду свойства
        */
        function setPropertyByOrderNum(
            $nOrderNum,
            $sPropertyCode,
            $sPropertyValue
        ){
            // Костыль из за того, что свойство в своё время было названо
            // по-другому. И с ним теперь жить
            $sPropertyCode = str_replace("TROYKA", "TROIKA",$sPropertyCode);

            if(!$nOrderId = $this->checkOrderNum($nOrderNum))return false;

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
                        "ORDER_ID"       => $nOrderId,
                        "PERSON_TYPE_ID" => 1,
                        "PROPS_GROUP_ID" => $nPropGroup,
                        "CODE"           => $sPropertyCode 
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();

            $arFilter = array(
                "ORDER_ID"      =>  $nOrderId,
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
                    $this->riseError("Ошибка обновления свойства заказа
                    ".print_r($arFilter1));
                    return false;
                }
            }
            elseif($sPropertyValue){
                $arFilter["VALUE"] = $aPropertyValue;
                if(!CSaleOrderPropsValue::Add($arFilter)){
                    $this->riseError("Ошибка добавления свойства заказа
                    ".print_r($arFilter,1));
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
            if(!$nOrderId = $this->checkOrderNum($sOrderNum))return false;

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
                        "ORDER_ID"       => $nOrderId,
                        "PERSON_TYPE_ID" => 1,
                        "PROPS_GROUP_ID" => $nPropGroup,
                        "CODE"           => $sPropertyCode 
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();

            $arFilter = array(
                "ORDER_ID"      =>  $nOrderId,
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
                $this->riseError("$sPropertyCode для заказа '".$arOrder["ID"].
                    "' не существует");
                return false;
            }

            return $arExistPropValue["VALUE"];
        }

        /**
            привязки номера карты к заказу
        */
        function linkOrder(
            $nOrderNum,      // Номер заказа
            $sTroykaNum = '-'// ПУстой номер карты (только для автотеста)
        ){
            if($sTroykaNum!='-')
                $this->number = $sTroykaNum;
            $this->error = ''; 
            $this->setPropertyByOrderNum(
                $nOrderNum,$this->mnemonic,$this->number
            );
        }



        /**
            привязки номера транзакции к заказу
        */
        function linkOrderTransact(
            $nOrderNum,      // Номер заказа
            $sTroykaNum = '-'// ПУстой номер карты (только для автотеста)
        ){
            if($sTroykaNum!='-')
                $this->number = $sTroykaNum;
            $this->error = ''; 
            $this->setPropertyByOrderNum(
                $nOrderNum,$this->mnemonic."_TRANSACT_ID",$this->transact
            );
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
                $this->riseError($objCurlLogger->error);
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
                $this->riseError("Некорректный номер заказа:".$sOrderNum);
                return false;
            }

            $arOrder = CSaleOrder::GetList(
                array(),
                array("ADDITIONAL_INFO"=>$sOrderNum),
                false,
                array("nTopCount"=>1),
                array("ID")
            )->Fetch();
            if(!isset($arOrder["ID"])){
                $this->riseError("Заказ с номером '$sOrderNum' не существует");
                return false;
            }

            return $arOrder["ID"];
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
            if(!preg_match("#^\d{10,11}$#",$sPhone)){
                $this->riseError("Некорректный номер телефона");
                return false;
            }
            return true;
        }

        function riseError($sError){
            $this->error = $sError;
            
            if($this->debug){
                $this->error .="\nBacktrace:\n";
                $arBacktrace = debug_backtrace(
                    DEBUG_BACKTRACE_PROVIDE_OBJECT,5
                );
                foreach($arBacktrace as $arBackItem) 
                    $this->error .= ""
                        .$arBackItem["file"]
                        ." : "
                        .$arBackItem["line"]
                        .";\n"; 
            }

        }
          
    }
