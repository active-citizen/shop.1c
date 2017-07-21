<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->StartResultCache(false)) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/integrationSettings.class.php");
    $objSettings = new CIntegrationSettings($arParams["CODE"]);
    if($objSettings->error){
        ShowMessage(array(
            "TYPE"=>"ERROR",
            "MESSAGE"=>$objSettings->error
        ));
    }
    else{
        if($_REQUEST["CODE"]==$arParams["CODE"]){
            $arSettings = $objSettings->get();
            foreach($arSettings as $sCode=>$arSetting){
                if(isset($_REQUEST["KEY_".$sCode])){
                    $arSettings[$sCode]["VALUE"] = $_REQUEST["KEY_".$sCode];
                }
            }
            $objSettings->set($arSettings);
            LocalRedirect($_SERVER["REQUEST_URI"]);
        }
        $arResult["SETTINGS"] = $objSettings->get();

    }


    $this->IncludeComponentTemplate();
}
