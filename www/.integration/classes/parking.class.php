<?
    require_once(realpath(dirname(__FILE__))."/integrations.class.php");

    class CParking extends CIntegration{
   
        function __construct($sPhone){
            parent::__construct();
            if($this->error)return false;
            if(!$this->checkPhone($sPhone))return false;
            $this->phone = $sPhone;
            
        }

        function payment(){
            if($this->isLimited()){
                $this->error = "Дневной лимит транзакций к парковкам исчерпан"; 
                return false;
            }
            $sUniqHash = md5(time().rand(1,10000000));
            $data['unique_code']= 'parking_' . $sUniqHash;
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
            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL,
                    $this->settings["PARKING_URL"]["VALUE"]
                );
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postStr);
                $out = curl_exec($curl);
                $response = simplexml_load_string(
                    $out,
                    'SimpleXmlElement',
                    LIBXML_NOERROR + LIBXML_ERR_FATAL + LIBXML_ERR_NONE
                );
                print_r($response);
                die;
                if (false === $response) {
                    curl_close($curl);
                        return $this->error = 
                            'Ошибка платежа, пожалуйста повторите позже.';
                }
            }
            curl_close($curl);


            print_r($arPost);
            die;

        }

        /**
            Завершился ли дневной лимит по транзакциям
        */
        function isLimited(){
            return false;
        }

    }
    
