<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegrationSetting.class.php");
    use AGShop\Integration as Integration;

    $objSettings = new \Integration\CIntegrationSettings($arParams["CODE"]);
    if($objSettings->error){
        echo "<pre>";
        print_r($_REQUEST);
        die;
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
