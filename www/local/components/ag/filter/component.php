<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogStore.class.php"
    );
    use AGShop\Catalog as Catalog;

if ($this->StartResultCache(
    false
    ,CUser::GetID()
)) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/catalog.lib.php");



    CModule::IncludeModule("sale");
    $res = CSaleUserAccount::GetList(
        ["TIMESTAMP_X"=>"DESC"],
        ["USER_ID"=>CUser::GetID()]
    );
    $arResult['account'] = $res->GetNext();
    

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

    /*******************************************
     * МФЦ
    ********************************************/
    $objStore = new \Catalog\CCatalogStore;
    $arResult["STORES"] = $objStore->getAllActive();
    foreach($arResult["STORES"] as $nKey=>$arStore)
        if(!$arStore["CODE"])
            $arResult["STORES"][$nKey]["CODE"] = CUtil::translit($arStore["TITLE"],"ru",[
                "change_case"   =>  false,
                "replace_space" =>  "",
                "replace_other" =>  ""
            ]);
        
    $this->IncludeComponentTemplate();
}


