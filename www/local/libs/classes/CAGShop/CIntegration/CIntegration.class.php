<?
    namespace Integration;

    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CLog/CCurlLogger.class.php");
    require_once(realpath(__DIR__)."/CIntegrationSetting.class.php");
//    require_once(realpath(__DIR__."/..")."/COrder/COrder.class.php");

    use AGShop;
    use AGShop\Log as Log;
    use AGShop\Order as Order;
    

    class CIntegration extends \AGShop\CAGShop{
        var $transact = '';     //!< ID транзакции
        var $phone = '';        //!< Номер телефона владельца банк.карты
        var $emulation = false; //!< Режим эмуляции ('success','failed', false)
        var $error  = '';       //!< Текст последней ошибки
        var $errorNo= 0;        //!< Номер ошибки (может отсутствовать
        var $settings = array();//!< Настройки, полученные из БД
        var $debug = false;      //!< Режим отладки
        var $mnemonic = '';     //!< Мнемоника класса для всяких префиксов     
        var $transactsToday = 0;//!<Сделано транзакций сегодня. 


        function __construct($sMnemonic = ''){
            parent::__construct();
            // Получаем настройки из БД
            require($_SERVER["DOCUMENT_ROOT"]."/.integration/secret.inc.php");


            if(!$sMnemonic)
                $this->mnemonic = str_replace(
                    "INTEGRATION\CINTEGRATION","",
                    mb_strtoupper(get_called_class())
                );
            else
                $this->mnemonic = $sMnemonic;


            $objSettings = new \Integration\CIntegrationSettings($this->mnemonic);
            $this->settings = $objSettings->get(); 
            unset($objSettings);
            if($objSettings->error)$this->error = $objSettings->error;
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
            $objOrder = new \Order\COrder;
            $objOrder->setParam("Id", $nOrderId);
            $objOrder->saveProperty($sPropertyCode, $sPropertyValue);
            return true;

        }


        /**
            Получение свойства заказа по номеру заказа и коду свойства
        */
        function getPropertyByOrderNum(
            $sOrderNum,
            $sPropertyCode
        ){
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
                $this->addError("$sPropertyCode для заказа '".$arOrder["ID"].
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
            $objCurlLogger = new \Log\CCurlLogger;
            
            if(!$nLogId = $objCurlLogger->addLog(array(
                "ORDER_NUM" =>  $sOrderid,
                "URL"       =>  $sUrl,
                "DATA"      =>  json_encode($sOutput),
                "POST_DATA" =>  json_encode($sInput)
            ))){
                $this->addError($objCurlLogger->getErrors());
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
            $objOrder = new \Order\COrder;
            if(!$arOrder = $objOrder->getByNum($sOrderNum)){
                $this->addError("Заказ с номером '$sOrderNum' не существует");
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
            if(!preg_match("#^\d{10,11}$#",$sPhone)){
                $this->addError("Некорректный номер телефона");
                return false;
            }
            return true;
        }

        /**
            Завершился ли дневной лимит по транзакциям
        */
        function isLimited(
            $nTimestamp = 0 //!< Дата, за которую надо посмотреть лимит
        ){
            global $DB;

            if(!$nTimestamp)
                $nTimestamp = time();


            // Получение ID группы свойств заказа
            /*
            $arPropGroup = CSaleOrderPropsGroup::GetList(
                array(),
                $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
                false,
                array("nTopCount"=>1)
            )->GetNext();
            $nPropGroup = $arPropGroup["ID"];
            */

            // Костыли, костылёчки, костылики
            if($this->mnemonic == 'TROYKA')
                $mnemonic  = "TROIKA";
            else
                $mnemonic = $this->mnemonic;

            // Получаем ID свойства закака "транзакция
            /*
            $arPropValue = CSaleOrderProps::GetList(
                array("SORT" => "ASC"),
                $arFilter = array(
                        "ORDER_ID"       => $nOrderId,
                        "PERSON_TYPE_ID" => 1,
                        "PROPS_GROUP_ID" => $nPropGroup,
                        "CODE"           => $mnemonic ."_TRANSACT_ID"
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();
            $nOrderPropsId = $arPropValue["ID"];
            */

            $sStartDate = date("Y-m-d",$nTimestamp)." 00:00:00";
            $sEndDate =  date("Y-m-d",$nTimestamp)." 23:59:59";

            // Через битриксовый API слишком жирно. Делаем прямой запрос к БД
            /*
            $sQuery = "
                SELECT 
                    COUNT(`a`.`ID`) as `count`
                FROM 
                    `b_sale_order_props_value` as `a`
                        LEFT JOIN
                    `b_sale_order` as `b`
                        ON 
                            `a`.`ORDER_PROPS_ID`=$nOrderPropsId
                            AND `b`.`STATUS_ID`='F'
                            AND `b`.`DATE_INSERT`>='$sStartDate'
                            AND `b`.`DATE_INSERT`<='$sEndDate'
                            AND `a`.`VALUE`!=''
                            AND `a`.`ORDER_ID`=`b`.`ID`
                WHERE
                    `b`.`ID` IS NOT NULL
                LIMIT
                    1
                        
            ";
            */
            $sQuery = "
                SELECT 
                    COUNT(ID) as `count`
                FROM
                    `index_lock`
                WHERE
                    `TYPE`='".$this->mnemonic."'
                    AND
                    `CTIME`>='$sStartDate'
                    AND 
                    `CTIME`<='$sEndDate'
                LIMIT
                    1
            ";

            $arResult = $DB->Query($sQuery)->Fetch();
            // Если ошибка запроса - объявляем, что всё, баста
            if(!isset($arResult["count"]))return true;
            $this->transactsToday = $arResult["count"];
            // Баста
            if(
                $this->transactsToday
                >=
                $this->settings[$this->mnemonic."_LIMIT"]["VALUE"]
            ) return true;
            // Лимит не выбран
            return false;
        }


        /**
            Естановка блокировни на единицу товара в день
        */
        function setLock($nUserId = 0){
            global $USER;
            global $DB;
            if(!$nUserId)$nUserId = $USER->GetID();
            $sDate = date("Y-m-d H:i:s");
            $sQuery = "
                INSERT INTO `index_lock`(
                    `CTIME`,
                    `USER_ID`,
                    `TYPE`,
                    `STATUS`,
                    `LOCK_DATE`,
                    `TICKET`
                )
                VALUES(
                    '$sDate',
                    '$nUserId',
                    '".$this->mnemonic."',
                    'LOCK',
                    '$sDate',
                    '".$this->transactsToday."'
                )
            ";
            $res = $DB->Query($sQuery);
            return $DB->LastID();
              
        }

        /**
            Удаление блокировки - товар снова доступен
            к заказу
        */
        function resetLock($nLockId){
            global $DB;
            $nLock = intval($nLock);
            $sQuery = "
                DELETE FROM `index_lock`
                WHERE `ID` = $nLockId
                LIMIT 1
            ";
            $DB->Query($sQuery);
        }

        /**
            Зафиксировать блокировку. Товар куплен
        */
        function doneLock(
            $nLockId,
            $nOrderId = 0
        ){
            global $DB;
            $nLock = intval($nLock);
            $sQuery = "
                UPDATE `index_lock`
                SET 
                    `ORDER_ID` = ".intval($nOrderId).",
                    `LOCK_DATE`='0000-00-00 00:00:00',
                    `STATUS`= 'DONE'
                WHERE `ID` = $nLockId
                LIMIT 1
            ";
            $DB->Query($sQuery);
        }
    
        /**
            Удалить зависшие блокировки
        */
        function clearLocks($nLifeTime = 600){
            global $DB;
            $sDate = date("Y-m-d H:i:s", time()-$nLifeTime);
            $sQuery = "
                DELETE FROM `index_lock`
                WHERE 
                    `LOCK_DATE`!='0000-00-00 00:00:00'
                    AND
                    `LOCK_DATE` <= '$sDate'
            ";
            $DB->Query($sQuery);
        }
    }
