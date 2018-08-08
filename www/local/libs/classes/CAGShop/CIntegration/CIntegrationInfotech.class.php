<?
    namespace Integration;
    require_once(realpath(dirname(__FILE__))."/CIntegration.class.php");
    use AGShop\Integration as Integration;

    class CIntegrationInfotech extends \Integration\CIntegration{

        private $sProtoVersionCode = '1.0';
        private $nInfotechUser = 0;
        private $sSessionId = '';
        private $nOrderId = 0;
        private $sOrderNum = '';
        private $nTimeout = 15;
        private $arTicketsIds = [];
        private $arTickets = [];
        private $arBitrixUser = [];

        /**
            Доступно только после вызова isLimited 
            
            @param $sPhone - номер телефона пользователя
            @param $sOrderNum - номер заказа
        */
        function __construct($sPhone='', $sOrderNum = ''){
            parent::__construct();
            $this->phone = $sPhone;
            $this->sOrderNum = $sOrderNum;

            // Проверяем зарегистрирован ли этот пользователь в Инфотехе
            // Если нет, то регистрируем
            if(!$this->__isUserRegister())if(!$this->__userRegister())
                return false;
                
            return true;
        }

        /**
            Получение ближайшего CategoryPriceId для
            указанного мероприятия. Для зоопарка и прочих
            периодических мероприятий, которые генерируют кучу входных билетов
            на все даты периода

            @return ближайший по дате CategoryPriceId 
        */
        function getLastCategoryPriceId($nActionId,$nCityId){
            $arSeatsList = $this->getAction($nActionId,$nCityId);
            if(
                !isset(
                    $arSeatsList[0]["actionEventList"][0]
                    ["categoryLimitList"][0]["categoryList"][0]["categoryPriceId"]
                )
            )return false;

            $nCategoryPriceId = 
                    $arSeatsList[0]["actionEventList"][0]
                    ["categoryLimitList"][0]["categoryList"][0]["categoryPriceId"];
            return $nCategoryPriceId;
        }

        /**
            Оплата заказа без размещения
             
            @param $nPriceCategory - ID ценовой категории
            @param $nAmount - количество билетов
            
        */
        function paymentWithoutSeat($nPriceCategory, $nAmount = 1){
            
                    // Резервируем место
            if(!$arSeatList = $this->__reservationWithoutSeats(
                $nPriceCategory, $nAmount
            ))return false;
            
            // Создаём заказ
            if(!$this->__createOrderWithoutSeats()){
                // Разрезервируем если не удалось создать заказ
                $this->unreserveAll();
                return false;
            }
            // Привязываем Id заказа в инфотехе к заказу в магазине
            $this->setPropertyByOrderNum(
                $this->sOrderNum,
                $this->mnemonic."_ORDER_ID",
                $this->nOrderId
            );
           
            // Оплачиваем
            if(!$this->__payOrderWithoutSeats()){
                // Отменяем заказ, если не удалось оплатить
                $this->__cancelOrderWithoutSeats();
                return false;
            }

            
            return $this->nOrderId;
        }
      
        private function __linkOrderId(){
        }

        /**
            Отправка билетов пользователю

            @param $nOrderId - Id заказа в инфотехе
        */
        public function sendTickets($nOrderId){

            $this->nOrderId = $nOrderId;

            // Получаем список заказанных билетов для заказа
            if(!$this->__getTicketsIds())return false;
            
            
            // Отправляем письмо с билетами
            if(!$this->__sendTickets())return false;
        }

        
        /**
            Регистрация текущего пользователя в Инфотех
        */
        private function __userRegister(){
            global $USER;
            if(!$this->arBitrixUser = $USER->GetByLogin(
                "u".$this->phone
            )->Fetch())return $this->addError(
                    "Нет пользователя с телефоном ".$this->phone
                );

            if(!$this->__createUser())return false;
            
            $USER->Update($this->arBitrixUser["ID"], [
                "UF_INFOTECH_USER_ID"    =>  $this->nInfotechUser,
                "UF_INFOTECH_SESS_ID"    =>  $this->sSessionId
            ]);
            
            return $this->nInfotechUser;
        }
        
        
        /**
            Зарегистрирован ли текущий пользователь в Инфотех
        */
        private function __isUserRegister(){
            global $USER;
            $this->arBitrixUser = $USER->GetList(
                ($by = "login"), ($order = "desc"),$arFilter = [
                    "LOGIN"=>"u".$this->phone,
                    "ACTIVE"=>"Y"
                ],[
                    "SELECT"=>["UF_INFOTECH_USER_ID","UF_INFOTECH_SESS_ID"],
                    "NAV_PARAMS"=>["nTopCount"=>1]
                ]
            )->Fetch();
            $fd = fopen($_SERVER["DOCUMENT_ROOT"]."/1.txt","w");
            
            if($this->arBitrixUser){
                $this->sSessionId = $this->arBitrixUser["UF_INFOTECH_SESS_ID"];
                return $this->nInfotechUser = $this->arBitrixUser["UF_INFOTECH_USER_ID"];
            }
            return false;
        }

        /**
            Отправка письма с билетом

            @return true в случае успеха
        */
        private function __sendTickets(){
            $arUser = $this->arBitrixUser;

            if(!$arAnswer = $this->__request("SEND_TICKETS_TO_EMAIL",[
                "userId"        =>  $this->nInfotechUser,
                "sessionId"     =>  $this->sSessionId,
                "orderId"       =>  $this->nOrderId,
                "email"         =>  $arUser["EMAIL"],
                "ticketIdList"  =>  $this->arTicketsIds
            ]))return false;
            return true;
        }


        /**
            Получение списка билетов

            @return массив с информацией о билетах заказа
        */
        private function __getTickets(){
            if(!$arAnswer = $this->__request("GET_TICKETS_BY_ORDER",[
                "userId"        =>  $this->nInfotechUser,
                "sessionId"     =>  $this->sSessionId,
                "orderId"       =>  $this->nOrderId,
            ]))return false;
            if(!isset($arAnswer["ticketList"]) || !$arAnswer["ticketList"])
                return $this->addError(
                    "Не удалось получить список билетов дла заказа "
                    .$this->nOrderId
                );
            $this->arTickets = $arAnswer["ticketList"];
            return $arAnswer["ticketList"];
        }

        /**
            Получение массива Id билетов заказа

            @return массив Id заказанных билетов
        */
        private function __getTicketsIds(){
            $arTickets = $this->__getTickets();
            if(!$arTickets)return false;
            $this->arTicketsIds = [];
            foreach($this->arTickets as $arTicket)
                $this->arTicketsIds[] = $arTicket["ticketId"];
            return $this->arTicketsIds;
        }

        /**
            Оплата заказа без размещения
             
            @return статус оплаты (PAID в случае успеха)
        */
        private function __payOrderWithoutSeats(){
            
            if(!$arAnswer = $this->__request("PAY_ORDER",[
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
            Отмена заказа
            
            @return true в случае успеха
        */
        private function __cancelOrderWithoutSeats(){
            if(!$arAnswer = $this->__request("CANCEL_ORDER",[
                "userId"    =>  $this->nInfotechUser,
                "sessionId" =>  $this->sSessionId,
                "orderId"   =>  $this->nOrderId
            ]))return false;
            if(
                !isset($arAnswer["statusExtStr"]) 
                || !$arAnswer["statusExtStr"]
                || $arAnswer["statusExtStr"]!='CANCELLED'
            )
                return $this->addError("Не удалось отменить заказ "
                    .$this->nOrderId.":".$arAnswer["statusExtStr"]);
            return $arAnswer["statusExtStr"];
        }

        /**
            Создание заказа без размещения
             
            @return ID заказа
        */
        private function __createOrderWithoutSeats(){
            $arUser = $this->arBitrixUser;
            if(!$this->__isEmailCorrect())return false;
           
            if(!$arAnswer = $this->__request("CREATE_ORDER_EXT",[
                "userId"    =>  $this->nInfotechUser,
                "sessionId" =>  $this->sSessionId,
//                "email"     =>  $arUser["EMAIL"],
//                "phone"     =>  $this->phone,
//                "fullName"  =>  $arUser["LAST_NAME"]." ".$arUser["NAME"]." ".
//                    $arUser["SECOND_NAME"]
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
        private function __reservationWithoutSeats($nPriceCategory, $nAmount = 1){
            if(!$this->__isEmailCorrect())return false;

            if(!$arAnswer = $this->__request("RESERVATION",[
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
        private function __unreserveAll(){
            if(!$arAnswer = $this->__request("RESERVATION",[
                "type"      =>  "UN_RESERVE_ALL",
                "userId"    =>  $this->nInfotechUser,
                "sessionId" =>  $this->sSessionId
            ]))return false;
            if(!isset($arAnswer["seatList"]) || !$arAnswer["seatList"])
                return $this->addError("Не удалось разрезервировать место");
            return $arAnswer["seatList"];
        }
        

        /**
            Проверка корректности EMAIL
            email берётся из информации о текущем авторизованном пользователе
        */
        function __isEmailCorrect(){
            $arUser = $this->arBitrixUser;
            if(preg_match("#^u\d+\@shop\.ag\.mos\.ru$#",$arUser["EMAIL"]))return $this->addError(
                "Не указан email. Некуда выслать билет."
            );
            return true;
         }


        /**
            Отправка в Инфотех запроса на регистрацию пользователя
         
            @return ID созданного пользователя
        */
        private function __createUser(){
            if(!$arAnswer = $this->__request("CREATE_USER"))return false;;
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
        public function getCities(){
            if(!$arAnswer = $this->__request("GET_CITIES"))return false;;
            if(!isset($arAnswer["cityList"]) || !$arAnswer["cityList"])
                return $this->addError("Пустой список городов");
            return $arAnswer["cityList"];
        }

        public function getActions($nCityId){
            if(!$arAnswer = $this->__request("GET_ACTIONS_V2",[
                "cityId"=>intval($nCityId)
            ]))return false;
            if(!isset($arAnswer["actionList"]) || !$arAnswer["actionList"])
                return $this->addError("Пустой список мероприятий");
            return $arAnswer["actionList"];
        }


        public function getAction($nActionId, $nCityid=0){
            $arRequest = ["actionId"=>intval($nActionId)];
            if($nCityid)$arRequest["cityId"] = intval($nCityid);
            if(!$arAnswer = $this->__request("GET_ACTION_EXT",$arRequest))
                return false;
            if(
                !isset($arAnswer['action']["venueList"]) 
                || !$arAnswer['action']["venueList"]
            )return $this->addError("Пустое мероприятие");
            return $arAnswer['action']["venueList"];
        }


        private function __request($sCommand, $arParams = []){
            $arParams["versionCode"] = $this->sProtoVersionCode;
            $arParams["command"] = $sCommand;
            $arParams["fid"] = $this->settings["INFOTECH_FID"]["VALUE"];
            $arParams["token"] = $this->settings["INFOTECH_TOKEN"]["VALUE"];

            $curl = curl_init();
            curl_setopt ($ch, CURLOPT_TIMEOUT, $this->nTimeout);
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
  
