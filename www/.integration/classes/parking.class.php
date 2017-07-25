<?
    require_once(realpath(dirname(__FILE__))."/integrations.class.php");

    class CParking extends CIntegration{
   
        function __construct($sPhone){
            parent::__construct();
            if($this->error)return false;
            if(!$this->checkPhone($sPhone))return false;
            $this->phone = $sPhone;
            
        }

        /**
            Заказ парковки
        */
        function payment(
            $sOrderNum  // Номер заказа. Например Б-123123123
        ){
            // Проверяем корректность номера заказа
            if(!$this->checkOrderNum())return false;

            // Готовим данные для отправки
            $sPostData = $this->prepareRequestData();

            // Режим эмуляции платежа
            if( $this->emulation = 'success' || $this->emulation = 'failed')
                $sPaymentAnswer = $this->paymentRequestEmulate($sOrderNum);
            else
                $sPaymentAnswer = $this->paymentRequest($sOrderNum,$sPostData);

            // Пишем результат транзакции в лог
            $this->curlLog(
               $this->settings["PARKING_URL"]["VALUE"],
               $sOrderNum,
               json_encode($sPostData),
               json_encode($arAnswer)
            ); 
            return true;

            die; 


            die;

            if($this->isLimited()){
                $this->error = "Дневной лимит транзакций к парковкам исчерпан"; 
                return false;
            }



            print_r($arPost);
            die;

        }


        /**
            Эмуляния запроса платежа (для прохождения тестов)
        */
        private function paymentRequestEmulate(){
            if($this->emulation=='success')
                $arAnswer = array (
                    "@attributes" => array (
                        "emulation"=>true,
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
                        "emulation"=>true,
                        "errors" => 1
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
            $sUniqHash = md5(time().rand(1,10000000));
            $arPost = array(
                'partner'    => $this->settings["PARKING_PARTNER"]["VALUE"],
                'secret'     => $this->settings["PARKING_SECRET"]["VALUE"],
                'subscriber' => $this->phone,
                'amount'     => intval($this->settings["PARKING_SUM"]["VALUE"]),
                'paymentId'  => $sUniqHash,
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
        function isLimited(){
            return false;
        }

    }
    
