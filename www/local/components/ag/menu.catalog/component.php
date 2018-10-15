<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();



//if ($this->StartResultCache(false)) {

    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogSection.class.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CCatalog/CCatalogWishesStatistic.class.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/COrder/COrderStatistic.class.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CUser/CUser.class.php");
    use AGShop\Catalog as Catalog;
    use AGShop\User as User;
    use AGShop\Order as Order;
    
    $objSection = new \Catalog\CCatalogSection;
    $arSections = $objSection->get([
        "ACTIVE"=>"Y",
        "ONLY_WITH_PRODUCTS"=>true,
        "ONLY_WITH_PRESENT_PRODUCTS"=>true
    ]);
    $arResult["CURRENT_SECTION"] = '';
    foreach($arSections as $nKey=>$arSection){
        if(isset($arIconsClasses[$arSection["CODE"]]))
            $arSections[$nKey]["CLASSNAME"]=$arIconsClasses[$arSection["CODE"]];
        else
            $arSections[$nKey]["CLASSNAME"]=$sSectionIconDefault;
        if(preg_match("#^/catalog/".$arSection["CODE"]."/#",$_SERVER["REQUEST_URI"])){
            $arSections[$nKey]["CURRENT"]=true;
            $arResult["CURRENT_SECTION"] = $arSection["CODE"];
        }
        
    }
    $arResult["SECTIONS"] = $arSections;


    $nUserId = $USER->GetID();
    $objUser = new \User\CUser;
    $arResult["BALANCE"] = $objUser->getPoints($nUserId);

    $objOrderStatistic = new \Order\COrderStatistic($nUserId);
    $arResult["ORDERS_COUNT"] = $objOrderStatistic->get(); 

    $objStatistic = new \Catalog\CCatalogWishesStatistic($nUserId);
    $arResult["WISHES_COUNT"] = $objStatistic->get();


    $this->IncludeComponentTemplate();
//}

