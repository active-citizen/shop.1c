<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


//if ($this->StartResultCache(false,CUser::GetID())) {
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CSSAG/CSSAGAccount.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CUtil/CLang.class.php");

    use AGShop\Util as Util;
    use AGShop\SSAG as SSAG;
        
    $objSSAGAccount = new \SSAG\CSSAGAccount('',$USER->GetID());
    $nBalance = $objSSAGAccount->balance();


    $arResult['myBalls'] = number_format($nBalance,0 ,',',' ')
        ." ".\Util\CLang::getPoints($nBalance);

    $arResult['arUserInfo'] = $USER->GetById($USER->GetId())->GetNext();
    $arResult['FIO'] =
    $arResult['arUserInfo']["NAME"]."<br/>".$arResult['arUserInfo']["LAST_NAME"];


    $this->IncludeComponentTemplate();
//}

