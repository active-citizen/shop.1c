<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


//if ($this->StartResultCache(false,CUser::GetID())) {

    CModule::IncludeModule("sale");
    $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
    $arResult['account'] = $res->GetNext();
    
    $arResult['MY_BALLS'] = number_format($arResult['account']["CURRENT_BUDGET"],0 ,',',' ');
    $arResult['myBalls'] = $arResult['MY_BALLS']." ".get_points($arResult['account']["CURRENT_BUDGET"]);

    $arResult['arUserInfo'] = $USER->GetById($USER->GetId())->GetNext();
    $arResult['FIO'] =
    $arResult['arUserInfo']["NAME"]."<br/>".$arResult['arUserInfo']["LAST_NAME"];


    $this->IncludeComponentTemplate();
//}

