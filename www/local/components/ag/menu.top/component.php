<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


//if ($this->StartResultCache(false,CUser::GetID())) {
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CUser/CUser.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CUtil/CLang.class.php");

    use AGShop\Util as Util;
    use AGShop\User as User;
        
    $nUserId = $USER->GetID();
    $objUser = new \User\CUser;
    $nBalance = $objUser->getPoints($nUserId);


    $arResult['myBalls'] = number_format($nBalance,0 ,',',' ')
        ." ".\Util\CLang::getPoints($nBalance);

    $arResult['arUserInfo'] = $objUser->getById($nUserId);
    $arResult['FIO'] =
        $arResult['arUserInfo']["NAME"]
            ."<br/>".$arResult['arUserInfo']["LAST_NAME"];


    $this->IncludeComponentTemplate();
//}

