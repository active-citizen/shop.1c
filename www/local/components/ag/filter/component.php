<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($this->StartResultCache(
    false
    ,CUser::GetID()
)) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/catalog.lib.php");

    CModule::IncludeModule("sale");
    $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
    $arResult['account'] = $res->GetNext();
    

    /*
    $arResult['MY_BALLS'] = number_format(
        $arResult['account']["CURRENT_BUDGET"],0 ,',',' '
    );    
    $arResult['myBalls'] = 
        $arResult['MY_BALLS'] 
        ." ".get_points($arResult['account']["CURRENT_BUDGET"]);


    ////////////////////////////////////////////////////
    /////  Составляем справочник хотелок
    ////////////////////////////////////////////////////
    $arResult["IWANTS"] = filterGetTags(
        IWANT_IBLOCK_ID,IWANT_PROPERTY_ID,
        $arParams["SECTION_ID"]
    );
    */


    ////////////////////////////////////////////////////
    /////  Составляем справочник интересов
    ////////////////////////////////////////////////////
    if(!IS_MOBILE)
    $arResult["INTERESTS"] = filterGetTags(
        INTEREST_IBLOCK_ID,INTEREST_PROPERTY_ID,
        $arParams["SECTION_ID"]
    );




    $res = CIBlockPropertyEnum::GetList(array(),array("CODE"=>"TYPES"));
    $TYPES = array();
    while($type = $res->getNext())$TYPES[$type["ID"]]=$type;

    $this->IncludeComponentTemplate();
}


