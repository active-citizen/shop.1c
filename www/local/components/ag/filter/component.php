<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//if ($this->StartResultCache(false, CUser::GetID())) {
    CModule::IncludeModule("sale");
    $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
    $arResult['account'] = $res->GetNext();
    
    $arResult['MY_BALLS'] = number_format($arResult['account']["CURRENT_BUDGET"],0 ,',',' ');
    $arResult['myBalls'] = 
        $arResult['MY_BALLS']
        ." ".get_points($arResult['account']["CURRENT_BUDGET"]);


    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"WANTS"));
    $arResult["IWANTS"] = array();
    while($iwant = $res->getNext())$arResult["IWANTS"][$iwant["ID"]]=$iwant;
    
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"INTERESTS"));
    $arResult["INTERESTS"] = array();
    while($interest = $res->getNext())$arResult["INTERESTS"][$interest["ID"]]=$interest;
    
    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"TYPES"));
    $TYPES = array();
    while($type = $res->getNext())$TYPES[$type["ID"]]=$type;

    $this->IncludeComponentTemplate();
//}
