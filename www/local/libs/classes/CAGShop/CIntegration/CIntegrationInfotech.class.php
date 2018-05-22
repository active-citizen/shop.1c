<?
    namespace Integration;
    require_once(realpath(dirname(__FILE__))."/CIntegration.class.php");
    //use AGShop\Integration as Integration;

    class CIntegrationInfotech extends \Integration\CIntegration{

        private $sProtoVersionCode = '1.0';
        private $sPhone = '';
        private $nInfotechUser = 0;
        private $sSessionId = '';
        private $nOrderId = 0;
        private $sOrderNum = '';

        /**
            Доступно только после вызова isLimited 
            
            @param $sPhone - номер телефона пользователя
            @param $sOrderNum - номер заказа
        */
        function __construct($sPhone='', $sOrderNum = ''){
            parent::__construct();
            $this->sPhone = $sPhone;
            $this->sOrderNum = $sOrderNum;
        }

        /**
            Оплата заказа без размещения
             
            @param $nPriceCategory - ID ценовой категории
            @param $nAmount - количество билетов
            
        */
        function paymentWithoutSeat($nPriceCategory, $nAmount = 1){
            
            // Проверяем зарегистрирован ли этот пользователь в Инфотехе
            // Если нет, то регистрируем
            if(!$this->isUserRegister())if(!$this->userRegister())return false;
            
            // Резервируем место
            if(!$arSeatList = $this->reservationWithoutSeats(
                $nPriceCategory, $nAmount
            ))return false;
            
            // Создаём заказ
            if(!$this->createOrderWithoutSeats()){
                // Разрезервируем
                $this->unreserveAll();
                return false;
            }
            
            // Оплачиваем
            if(!$this->payOrderWithoutSeats())
                return false;
            
            return $this->nOrderId;
        }
        
        
        /**
            Регистрация текущего пользователя в Инфотех
            
            @return Инфотех ID пользователя
        */
        function userRegister(){
            global $USER;
            if(!$arUser = $USER->GetByLogin("u".$this->sPhone)->Fetch())
                return $this->addError(
                    "Нет пользователя с телефоном ".$this->sPhone
                );
            
            if(!$this->createUser())
                return false;
            
            
            $USER->Update($USER->GetID(), [
                "UF_INFOTECH_USER_ID"       =>  $this->nInfotechUser,
                "UF_INFOTECH_SESS_ID"    =>  $this->sSessionId
            ]);
            
            return $this->nInfotechUser;
        }
        
        
        /**
            Зарегистрирован ли текущий пользователь в Инфотех
            
            @param $sPhone - телефон
        */
        function isUserRegister($sPhone){
            global $USER;
            $arUser = $USER->GetList(
                ($by = "LOGIN"), ($order = "desc"),[
                    "LOGIN"=>"u".$sPhone
                ],[
                    "SELECT"=>["UF_INFOTECH_USER_ID","UF_INFOTECH_SESS_ID"],
                    "NAV_PARAMS"=>["nTopCount"=>1]
                ]
            )->Fetch();
            
            if($arUser["UF_INFOTECH_USER_ID"]){
                $this->sSessionId = $arUser["UF_INFOTECH_SESS_ID"];
                return $this->nInfotechUser = $arUser["UF_INFOTECH_USER_ID"];
            }
            return false;
        }



        /**
            Оплата заказа без размещения
             
            @return статус оплаты (PAID в случае успеха)
        */
        function payOrderWithoutSeats(){
            
            if(!$arAnswer = $this->request("CREATE_ORDER_EXT",[
                "userId"    =>  $this->nInfotechUser,
                "sessionId" =>  $this->sSessionId,
                "orderId"   =>  $this->nOrderId
            ]))return false;
            if(
                !isset($arAnswer["statusExtStr"]) 
                || !$arAnswer["statusExtStr"]
                || $arAnswer["statusExtStr"]!='PAID'
            )
                return $this->addError("Не удалось оплатить заказ:".$arAnswer["statusExtStr"]);
            return $arAnswer["statusExtStr"];
        }


        /**
            Создание заказа без размещения
             
            @return ID заказа
        */
        function createOrderWithoutSeats(){
            global $USER;
            $arUser = $USER->GetByID($USER->GetID());
            
            if(!$arAnswer = $this->request("CREATE_ORDER_EXT",[
                "userId"    =>  $this->nInfotechUser,
                "sessionId" =>  $this->sSessionId,
                "email"     =>  $arUser["EMAIL"],
                "phone"     =>  $this->sPhone,
                "fullName"  =>  $arUser["LAST_NAME"]." ".$arUser["NAME"]." ".
                    $arUser["SECOND_NAME"]
            ]))return false;
            if(!isset($arAnswer["orderId"]) || !intval($arAnswer["orderId"]))
                return $this->addError("Не удалось создать заказ");
            $this->nOrderId = $arAnswer["orderId"];
            return $arAnswer["orderId"];
        }

        /**
            Резервирование места без размещения
             
            @param $nPriceCategory - ID категории цены
            @param $nAmount - количество билетов
             
            @return true в случае успеха
        */
        function reservationWithoutSeats($nPriceCategory, $nAmount = 1){
            if(!$arAnswer = $this->request("RESERVATION",[
                "type"      =>  "RESERVE",
                "userId"    =>  $this->nInfotechUser,
                "sessionId" =>  $this->sSessionId,
                "categoryQuantityMap"   =>  [
                    $nPriceCategory => $nAmount
                ]
            ]))return false;
            if(!isset($arAnswer["seatList"]) || !$arAnswer["seatList"])
                return $this->addError("Не удалось зарезервировать место");
            return $arAnswer["seatList"];
        }
        
        /**
            Разрезервирование
            
            @params $arSeatList - список зарезервированных мест
        
        */
        function unreserveAll(){
            if(!$arAnswer = $this->request("RESERVATION",[
                "type"      =>  "UN_RESERVE_ALL",
                "userId"    =>  $this->nInfotechUser,
                "sessionId" =>  $this->sSessionId
            ]))return false;
            if(!isset($arAnswer["seatList"]) || !$arAnswer["seatList"])
                return $this->addError("Не удалось разрезервировать место");
            return $arAnswer["seatList"];
        }
        


        /**
            Отправка в Инфотех запроса на регистрацию пользователя
         
            @return ID созданного пользователя
        */
        function createUser(){
            if(!$arAnswer = $this->request("CREATE_USER"))return false;;
            if(!isset($arAnswer["userId"]) || !intval($arAnswer["userId"]))
                return $this->addError("Не удалось получить ID пользователя");
            if(!isset($arAnswer["sessionId"]) || !$arAnswer["sessionId"])
                return $this->addError("Не удалось получить ID сессии пользователя");
            $this->nInfotechUser = $arAnswer["userId"];
            $this->sSessionId = $arAnswer["sessionId"];
            return $arAnswer["userId"];
        }

        /**
            Отправка в Инфотех запроса на регистрацию пользователя
            
            @
         
            @return список городов
        */
        function getCities(){
            if(!$arAnswer = $this->request("GET_CITIES"))return false;;
            if(!isset($arAnswer["cityList"]) || !$arAnswer["cityList"])
                return $this->addError("Пустой список городов");
            return $arAnswer["cityList"];
        }

        function getActions($nCityId){
            if(!$arAnswer = $this->request("GET_ACTIONS_V2",[
                "cityId"=>$nCityId
            ]))return false;;
            if(!isset($arAnswer["actionList"]) || !$arAnswer["actionList"])
                return $this->addError("Пустой список мероприятий");
            return $arAnswer["actionList"];
        }


        function getSeats($nEventId){
            if(!$arAnswer = $this->request("GET_SEAT_LIST",[
                "actionEventId"=>$nEventId
            ]))return false;
            if(!isset($arAnswer["seatList"]) || !$arAnswer["seatList"])
                return $this->addError("Пустой список мест");
            return $arAnswer["seatList"];
        }


        private function request($sCommand, $arParams = []){
            $arParams["versionCode"] = $this->sProtoVersionCode;
            $arParams["command"] = $sCommand;
            $arParams["fid"] = $this->settings["INFOTECH_FID"]["VALUE"];
            $arParams["token"] = $this->settings["INFOTECH_TOKEN"]["VALUE"];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,
                $this->settings["INFOTECH_URL"]["VALUE"]
            );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($arParams));
            $out = curl_exec($curl);
            $sData = gzdecode($out);
            if(!$sData)$sData = $out;

            $arData = json_decode($sData,true,64,JSON_UNESCAPED_UNICODE);
            $this->curlLog(
               $this->settings["INFOTECH_URL"]["VALUE"],
               $this->sOrderNum?$this->sOrderNum:"infotex",
               $arParams,
               $arData
            );
            if($arData["resultCode"]!=0)
                return $this->addError($arData["description"]);
            // Пишем результат транзакции в лог
            
            return $arData;
        }


    }
  
