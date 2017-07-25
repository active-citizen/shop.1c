<?
    require_once(realpath(dirname(__FILE__))."/integrations.class.php");

    class CParking extends CIntegration{

        var $transactsToday = 0;//!<Сделано транзакций сегодня. 
        // Доступно только после вызова isLimited 
        function __construct($sPhone){
            parent::__construct();
            if($this->error)return false;
            if(!$this->checkPhone($sPhone))return false;
            $this->phone = $sPhone;
            $this->emulation = $this->settings["PARKING_EMULATION"]["VALUE"];
            
        }

        /**
            Заказ парковки
        */
        function payment(
            $sOrderNum  // Номер заказа. Например Б-123123123
        ){
            // Проверка дневного лимита
            if($this->isLimited()){
                $this->error = "Дневной лимит транзакций к парковкам исчерпан"; 
                return false;
            }

            // Проверяем корректность номера заказа
            if(!$this->checkOrderNum($sOrderNum))return false;

            // Готовим данные для отправки
            $sPostData = $this->prepareRequestData();

            // Режим эмуляции платежа
            if( $this->emulation == 'success' || $this->emulation == 'failed')
                $arAnswer = $this->paymentRequestEmulate($sOrderNum);
            else
                $arAnswer = $this->paymentRequest($sOrderNum,$sPostData);

            // Пишем результат транзакции в лог
            $this->curlLog(
               $this->settings["PARKING_URL"]["VALUE"],
               $sOrderNum,
               $sPostData,
               $arAnswer
            );

            // Сохраняем номер транзакции, если указан
            if(isset($arAnswer["payment"]["paymentId"]))
                $this->transact = $arAnswer["payment"]["paymentId"];

            // Сохраняем текст ошибок и выходим
            if(
                isset($arAnswer["@attributes"]["errors"])
                && intval($arAnswer["@attributes"]["errors"])
            ){
                if(
                    !isset($arAnswer["error"][0])
                    &&
                    $arAnswer["error"]
                )
                    $this->riseError($arAnswer["error"]); 
                elseif(
                    isset($arAnswer["error"][0])
                )
                    $this->riseError(implode(",",$arAnswer["error"]));
                
                return false;
            }
        }


        /**
            Эмуляния запроса платежа (для прохождения тестов)
        */
        private function paymentRequestEmulate(){
            if($this->emulation=='success')
                $arAnswer = array (
                    "@attributes" => array (
                        "emulation"=>$this->emulation,
                        "errors" => 0
                    ),
                    "funds" => sprintf("%2d",
                            $this->settings["PARKING_SUM"]["VALUE"]
                     ),
                    "payment" => array (
                        "date" => date("Y-m-d H:i:s"),
                        "amount" => sprintf("%2d",
                            $this->settings["PARKING_SUM"]["VALUE"]
                        ),
                        "subscriber" => $this->phone,
                        "paymentId" => $sPaymentId = md5(
                            time().rand(1,1000000000000)
                        )
                    )
                );
             elseif($this->emulation=='failed')
                $arAnswer = array (
                    "@attributes" => array (
                        "emulation"=>$this->emulation,
                        "errors" => 1
                    ),
                    "error" => "Отправка транзакций остановлена. Эмуляция."
                );
                
            return $arAnswer;
        }

        /**
            Реальный запрос транзакции
        */
        private function paymentRequest($sPostData){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,
                $this->settings["PARKING_URL"]["VALUE"]
            );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $sPostData);
            $out = curl_exec($curl);
            $objResponse = simplexml_load_string(
                $out,
                'SimpleXmlElement',
                LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE
            );
            $arAnswer = json_decode(json_encode((array)$objResponse), TRUE);
            curl_close($curl);
            return $arAnswer;
        }
        
        /**
            Функция готовит необходимые данные для POST-запроса
        */
        private function prepareRequestData(){
            $this->transact = md5(time().rand(1,10000000));
            $arPost = array(
                'partner'    => $this->settings["PARKING_PARTNER"]["VALUE"],
                'secret'     => $this->settings["PARKING_SECRET"]["VALUE"],
                'subscriber' => $this->phone,
                'amount'     => intval($this->settings["PARKING_SUM"]["VALUE"]),
                'paymentId'  => $this->transact,
                'time'       => time()
            );
            ksort($arPost);
            $shaStr = '';

            foreach ($arPost as $key => $param) {
                $shaStr .= $key . '=' . $param . '&';
            }
            $shaStr = rtrim($shaStr, '&');
     
            unset($arPost['secret']);
            $arPost['hash'] = sha1($shaStr);
            $postStr = '';
            foreach ($arPost as $key => $param) {
                $postStr .= $key . '=' . $param . '&';
            }
            $postStr = rtrim($postStr, '&');
            return $postStr;
        }


        /**
            Завершился ли дневной лимит по транзакциям
        */
        function isLimited(
            $nPeriod = 86400 //!< Перид, за который считаем лимит
        ){
            global $DB;


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
                        "CODE"           => $this->mnemonic."_TRANSACT_ID"
                    ),
                false,
                false,
                array("ID","CODE","NAME")
            )->Fetch();
            $nOrderPropsId = $arPropValue["ID"];

            $sDate = date("Y-m-d H:i:s",time()-$nPeriod);
            // Через битриксовый API слишком жирно. Делаем прямой запрос к БД
            $sQuery = "
                SELECT 
                    COUNT(`a`.`ID`) as `count`
                FROM 
                    `b_sale_order_props_value` as `a`
                        LEFT JOIN
                    `b_sale_order` as `b`
                        ON 
                            `a`.`ORDER_PROPS_ID`=$nOrderPropsId
                            AND `a`.`VALUE`!=''
                            AND `a`.`ORDER_ID`=`b`.`ID`
                            AND `b`.`DATE_INSERT`>='$sDate'
--                            AND `b`.`STATUS_ID`='F'
                WHERE
                    `b`.`ID` IS NOT NULL
                LIMIT
                    1
                        
            ";
            $arResult = $DB->Query($sQuery)->Fetch();
            // Если ошибка запроса - объявляем, что всё, баста
            if(!isset($arResult["count"]))return true;
            $this->transactsToday = $arResult["count"];
            // Баста
            if(
                $arResult["count"]
                >=
                $this->settings[$this->mnemonic."_LIMIT"]["VALUE"]
            ) return true;
            // Лимит не выбран
            return false;
        }

    }
    
