<?
    namespace Integration;
    require_once(realpath(dirname(__FILE__))."/CIntegration.class.php");
    //use AGShop\Integration as Integration;

    class CIntegrationInfotech extends \Integration\CIntegration{

        private $sProtoVersionCode = '1.0';

        // Доступно только после вызова isLimited 
        function __construct($sPhone=''){
            parent::__construct();
            if($this->error)return false;
        }

        function getCities(){
            $arAnswer = $this->request("GET_CITIES");
            if(!isset($arAnswer["cityList"]) || !$arAnswer["cityList"])
                return $this->addError("Пустой список городов");
            return $arAnswer["cityList"];
        }

        function getActions($nCityId){
            $arAnswer = $this->request("GET_ACTIONS_V2",[
                "cityId"=>$nCityId
            ]);
            if(!isset($arAnswer["actionList"]) || !$arAnswer["actionList"])
                return $this->addError("Пустой список мероприятий");
            return $arAnswer["actionList"];
        }


        function getSeats($nEventId){
            $arAnswer = $this->request("GET_SEAT_LIST",[
                "actionEventId"=>$nEventId
            ]);
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
               $arParams["ORDER_NUM"],
               $arParams,
               $arData
            );
            if($arData["resultCode"]!=0)
                return $this->addError($arData["description"]);
            // Пишем результат транзакции в лог
            
            return $arData;
        }


    }
  
