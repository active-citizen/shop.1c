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
            return $this->request("GET_CITIES");    
        }

        private function request($sCommand, $arParams = []){
            $arParams["versionCode"] = $this->sProtoVersionCode;
            $arParams["command"] = $sCommand;
            $arParams["fid"] = $this->settings["INFOTECH_FID"]["VALUE"];
            $arParams["token"] = $this->settings["INFOTECH_TOKEN"]["VALUE"];
            new \XPrint($arParams);

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
            new \XPrint($arData);;
            
        }


    }
  
