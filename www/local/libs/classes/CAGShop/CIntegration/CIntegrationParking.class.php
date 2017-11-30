<?
    namespace Integration;
    require_once(realpath(dirname(__FILE__))."/CIntegration.class.php");
    use AGShop\Integration as Integration;

    class CIntegrationParking extends \Integration\CIntegration{

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
            // Проверимне тут
            /*
            if($this->isLimited()){
                $this->error = "Дневной лимит транзакций к парковкам исчерпан"; 
                return false;
            }
            */

            // Проверяем корректность номера заказа
            if(!$this->checkOrderNum($sOrderNum))return false;

            // Готовим данные для отправки
            $sPostData = $this->prepareRequestData();

            // Режим эмуляции платежа
            if( $this->emulation == 'success' || $this->emulation == 'failed')
                $arAnswer = $this->paymentRequestEmulate();
            else
                $arAnswer = $this->paymentRequest($sPostData);

            // Пишем результат транзакции в лог
            $this->curlLog(
               $this->settings["PARKING_URL"]["VALUE"],
               $sOrderNum,
               $sPostData,
               $arAnswer
            );
            $arAnswer = json_decode(json_encode((array)$arAnswer), TRUE);

            // Сохраняем номер транзакции, если указан
            if(isset($arAnswer["payment"]["paymentId"]))
                $this->transact = $arAnswer["payment"]["paymentId"];

            // Сохраняем текст ошибок и выходим
            if(
                isset($arAnswer["@attributes"]["errors"])
                && 
                intval($arAnswer["@attributes"]["errors"])
            ){
                $this->addError("Некорректный ответ сервиса пополнения порковочных баллов");
                return false;
            }
            elseif(!$arAnswer){
                $this->addError("Пустой ответ сервиса пополнения порковочных баллов");
                return false;
            }
            elseif(isset($arAnswer[0]) && !trim($arAnswer[0])){
                $this->addError("Пустой ответ шлюза парковок");
                return false;
            }
            return true;
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


    }
    
