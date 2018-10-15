<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CIntegration/CIntegration.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CIntegration/CIntegrationParking.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CIntegration/CIntegrationInfotech.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CUser/CUser.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCatalog/CCatalogProduct.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCatalog/CCatalogSection.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCatalog/CCatalogWishes.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/COrder/COrder.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CCache/CCache.class.php"
);
require_once($_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CAuction/CAuction.class.php"
);

use AGShop\Integration as Integration;
use AGShop\User as User;
use AGShop\Order as Order;
use AGShop\Catalog as Catalog;
use AGShop\Cache as Cache;
use AGShop\CAuction as Auction;


//if ($this->StartResultCache(false,CUser::GetID())) {
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");
    $RU = $_SERVER["REQUEST_URI"];
    ///////////////////// Значения по умолчанию ////////////////////////////////
    if(!isset($arParams["PRODUCT_CODE"]))$arParams["PRODUCT_CODE"] = '';
    if(!isset($arParams["CATALOG_IBLOCK_ID"]))$arParams["CATALOG_IBLOCK_ID"] 
        = CATALOG_IB_ID;
    if(!isset($arParams["OFFER_IBLOCK_ID"]))$arParams["OFFER_IBLOCK_ID"] 
        = OFFER_IB_ID;
    if(!isset($arParams["USER_ID"]))$arParams["USER_ID"] = $USER->GetId();
    if(!isset($arParams["ALL_POINTS_LIMIT"]))$arParams["ALL_POINTS_LIMIT"] = 1000;


    ///////////////// Определяем сумму на счету пользователя /////////////////
    $objCUser = new \User\CUser;
    $arResult["ACCOUNT"] =[
        "CURRENT_BUDGET"=>$objCUser->getPoints($arParams["USER_ID"])
    ];

    // Определяем категории пользователей
    $arResult["USERCATS_IDS"] = \User\CUser::getCategories($arParams["USER_ID"]);

    ////////////////////// Общая информация о продукте ////////////////////////
    $objCProduct = new \Catalog\CCatalogProduct;
    $objCache = new \Cache\CCache(
        "card_product_common_info",
        $arParams["PRODUCT_CODE"],
        COMMON_CACHE_TIME
    );
    if(!$arResult["CATALOG_ITEM"] = $objCache->get()){
        $arResult["CATALOG_ITEM"] = $objCProduct->getByCode(
            $arParams["PRODUCT_CODE"]
        );
        $objCache->set($arResult["CATALOG_ITEM"]);
    }
    if($objCProduct->isActive($arResult["CATALOG_ITEM"]["ID"])):



        ///////////////////// Информацация о разделе /////////////////////////////
        $objCSection = new \Catalog\CCatalogSection;
        $arResult["CATALOG_ITEM"]["SECTION_INFO"] = $objCSection->getById(
            $arResult["CATALOG_ITEM"]["IBLOCK_SECTION_ID"]
        );

        /////////////////// Сколько у товара всего желающих //////////////////////
        $objCWishes = new \Catalog\CCatalogWishes;
        $arResult["WISHES"] = $objCWishes->getCountByCatalogId(
            $arResult["CATALOG_ITEM"]["ID"]
        );

        //////////////////// Входит ли товар с писок моих желаний /////////////////
        $arResult["MYWISH"] = $objCWishes->isWished(
            $arResult["CATALOG_ITEM"]["ID"], $arParams["USER_ID"]
        );

        //////////////////// Свойства элемента каталога //////////////////////////
        $arResult["CATALOG_ITEM"]["PROPERTIES"] = 
            $objCProduct->getPropertiesForCard($arResult["CATALOG_ITEM"]["ID"]);

        ///// Вычисляем количество заказанного в этом месяце товара пользователем //
        $objCOffer = new \Catalog\CCatalogOffer;
        $arLimitInfo = $objCOffer->getMounthProductCount(CUser::GetId(), 
            $arResult["CATALOG_ITEM"]["ID"]
        );
        $arResult["MON_ORDERS"] = $arLimitInfo["count"];
        $arResult["NEXT_ORDER"] = $arLimitInfo["next"];
        
        $arLimitInfo = $objCOffer->getDailyProductCount($arResult["CATALOG_ITEM"]["ID"]);
        $arResult["DAILY_ORDERS"] = $arLimitInfo["count"];

        ///////////////////////////// Вычисляем рейтинг  //////////////////////////
        $arResult["CATALOG_ITEM"]["RATING"] = round(
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING"][0]["VALUE"]*5,2
        );

        // Вычисляем допустимые категории граждан
        $arResult["CATALOG_ITEM"]["USERCATS"] = [];
        if(isset($arResult["CATALOG_ITEM"]["PROPERTIES"]["USERSCATS"]))
            foreach($arResult["CATALOG_ITEM"]["PROPERTIES"]["USERSCATS"] as $arProperty)
                $arResult["CATALOG_ITEM"]["USERCATS"][] = $arProperty["VALUE"];

        ///////////////////// Получаем торговые предложения ///////////////////////
        $arOffers = $objCOffer->getOffersForCard(
            $arResult["CATALOG_ITEM"]["ID"],$arResult["CATALOG_ITEM"]
        );

        $arResult["INFOTECH_ACTIVE"] = true;
        //////////// Получаем доступность билетов по периодическому инфотеку
        if(
            isset($arOffers["OFFERS"][0]["PROPERTIES"]["INFOTECH_CITY_ID"][0]["VALUE"])
            &&
            $arOffers["OFFERS"][0]["PROPERTIES"]["INFOTECH_CITY_ID"][0]["VALUE"]
            &&
            isset($arOffers["OFFERS"][0]["PROPERTIES"]["INFOTECH_ACTION_ID"][0]["VALUE"])
            &&
            $arOffers["OFFERS"][0]["PROPERTIES"]["INFOTECH_ACTION_ID"][0]["VALUE"]
        ){
            $nCityId =
               $arOffers["OFFERS"][0]["PROPERTIES"]["INFOTECH_CITY_ID"][0]["VALUE"];
            $nActionId =
               $arOffers["OFFERS"][0]["PROPERTIES"]["INFOTECH_ACTION_ID"][0]["VALUE"];
               
            $objCache = new \Cache\CCache(
                "infotech_catPriceId",
                $nCityId."_".$nActionId,
                COMMON_CACHE_TIME
            );
            $nCategoryPriceId = $objCache->get();
            if(!$nCategoryPriceId){
                $objInfotech = new \Integration\CIntegrationInfotech(
                    str_replace("u","",$USER->GetLogin()),
                    0
                );

                $nCategoryPriceId = $objInfotech->getLastCategoryPriceId(
                    $nActionId, $nCityId
                );
                $objCache->set($nCategoryPriceId?$nCategoryPriceId:"none");
            }

            if(!intval($nCategoryPriceId))$arResult["INFOTECH_ACTIVE"] = false;
        }

        foreach($arOffers as $sKey=>$sValue){
            $arResult[$sKey] = $sValue;
        }
        // Является ли товар аукционом
        $objAuction = new \Auction\CAuction;
        $arResult["AUCTION"] = $objAuction->isAuction(
            $arResult["CATALOG_ITEM"]["ID"]
        );

        $arResult["BET"] = [];
        if($arResult["AUCTION"]){
            $arResult["BET"] = $objAuction->getActiveBet(
                $arOffers["OFFERS"][0]["ID"],
                $USER->GetID()
            );

            if($arResult["AUCTION"]["IS_FINISHED"])
                $arResult["AUCTION_WINNERS"] = $objAuction->getWinners( 
                    $arOffers["OFFERS"][0]["ID"],
                    $USER->GetID()
                );
        }

        $arIBlock = CIBlock::GetList(array(),array("CODE"=>"marks"))->GetNext();
        $iblockId = $arIBlock["ID"];
        // Определяем ставил ли пользователь оценку этому товару
        $arResult["MARK"] = CIBlockElement::GetList(
            array(), 
            $arField = array(
                "IBLOCK_ID"=>$iblockId,
                "PROPERTY_MARK_USER"=>$USER->GetId(),
                "PROPERTY_MARK_PRODUCT"=>$arResult["CATALOG_ITEM"]["ID"]
            ),
            false,
            array(),
            array("PROPERTY_MARK")
        )->GetNext();
        // Считаем количество отзывов
        $resComments = CForumMessage::GetList(array("POST_DATE"=>"DESC"),array("TOPIC_ID"=>$arResult["CATALOG_ITEM"]["PROPERTIES"]["FORUM_TOPIC_ID"][0]["VALUE"]));
        $arResult["MESSAGES"] = $resComments->SelectedRowsCount();

        // Узнаём статус активного гражданина 
        $arResult["USER_INFO"] = CUser::GetList(
            ($by="personal_country"), ($order="desc"),
            array("ID"=>CUser::GetId()),
            array(
                "SELECT"=>array("UF_USER_ALL_POINTS","UF_USER_AG_STATUS"),
                "NAV_PARAMS"=>array("nTopCount"=>1)
            )
        )->GetNext();
        $arResult["USER_INFO"]["UF_USER_ALL_POINTS"] = $arResult["ACCOUNT"]
            ["CURRENT_BUDGET"];


        // Очистка описания товара от говна

        $arResult["CATALOG_ITEM"]["DETAIL_TEXT"] = 
            cardTextClear($arResult["CATALOG_ITEM"]["DETAIL_TEXT"]);
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["RECEIVE_RULES"][0]
            ["~VALUE"]["TEXT"]
            = cardTextClear(
                $arResult["CATALOG_ITEM"]["PROPERTIES"]["RECEIVE_RULES"][0]
                    ["~VALUE"]["TEXT"]
            );
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["CANCEL_RULES"][0]
            ["~VALUE"]["TEXT"]
            = cardTextClear(
                $arResult["CATALOG_ITEM"]["PROPERTIES"]["CANCEL_RULES"][0]
                    ["~VALUE"]["TEXT"]
            );

    //    $arResult["CATALOG_ITEM"]["DETAIL_TEXT"] = str_replace(
    //        "{break}","\n",$arResult["CATALOG_ITEM"]["DETAIL_TEXT"]
    //    );


        // Вычисляем нужно ли прятать по дате отключения
        $arResult["HIDE_ON_DATE"] = false;
        if(
            isset($arResult["CATALOG_ITEM"]["PROPERTIES"]["HIDE_DATE"][0]["VALUE"])
            && 
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["HIDE_DATE"][0]["VALUE"]
        ){
            $tmp =
                date_parse(
                    $arResult["CATALOG_ITEM"]["PROPERTIES"]["HIDE_DATE"][0]["VALUE"]
                );
            if(isset($tmp["error_count"]) && !$tmp["error_count"]){
                $nHideTimestamp = mktime(
                    $tmp["hour"], $tmp["minute"], $tmp["second"],
                    $tmp["month"], $tmp["day"], $tmp["year"]
                );
                if(time()>$nHideTimestamp)$arResult["HIDE_ON_DATE"] = true;
            }
        }
        // Картинки по порядку
        $arResult["OFFERS"][0]["PROPERTIES"]["MORE_PHOTO"] = 
            array_reverse($arResult["OFFERS"][0]["PROPERTIES"]["MORE_PHOTO"]);

        $arResult["ALL_PICS"] = [];
        foreach($arResult["CATALOG_ITEM"]["PROPERTIES"]["MORE_PHOTO"] as $arProductPic)
            $arResult["ALL_PICS"][] = $arProductPic["FILE_PATH"];

        foreach($arResult["OFFERS_PROPS"] as $sPropCode=>$arProp)
            foreach($arProp['values'] as $nValId=>$arValue)
                $arResult["ALL_PICS"] = array_merge($arResult["ALL_PICS"], $arValue["pics"]);
        $arResult["ALL_PICS"] = array_unique($arResult["ALL_PICS"]);
        

    // Проверка активности товара
    else:
        $arResult["CATALOG_ITEM"]["ACTIVE"]='N';
    endif;

    $this->IncludeComponentTemplate();
//}

    /**
        Очиска текста товара от лишних cстилей для достижения единообразия
        вёрстки 
    */
    function cardTextClear($text){

        $text =  str_replace(
            "\n","",
            $text 
        );
       
        $text =   preg_replace(
            "#\s+#"," ",
            $text
        );

        $text =   preg_replace(
            "#>\s+<#","><",
            $text 
        );
          
        $text =   preg_replace(
            "/style=\".*?\"/i", "",
            $text 
        );
        
       
        $text =   preg_replace(
            "/<br.*?>/i", "",
            $text 
        );

        $text =   preg_replace(
            "#<p>\s+&nbsp;</p>#", "",
            $text 
        );

        $text =   preg_replace(
            "#>\s+#", ">",
            $text 
        );

        $text =   preg_replace(
            "#>\(#", "> (",
            $text 
        );

         $text =   preg_replace(
            "#<div> &nbsp;</div>#", "",
            $text 
        );
 
        return $text;
    }
