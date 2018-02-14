<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/COrder/COrder.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CCatalog/CCatalogProduct.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CSSAG/CSSAGAccount.class.php");
use AGShop\Order as Order;
use AGShop\SSAG as SSAG;
use AGShop\Catalog as Catalog;

$answer = ["error"=>""];

global $USER;
if(!$USER->IsAuthorized()){
    $answer["error"] = "Not Authorized";
}
elseif(isset($_GET["mark"]) && isset($_GET["product"])){
    $mark = intval($_GET["mark"]);
    $product=intval($_GET["product"]);
    CModule::IncludeModule('iblock');

    // Смотрим, не было ли уже оценки у этого пользователя
    $res = CIBlockElement::GetList(
        array(),
        array(
            "IBLOCK_CODE"=>"marks",
            "PROPERTY_MARK_PRODUCT"=>$product,
            "PROPERTY_MARK_USER"=>CUser::GetID()
        ),
        false,
        array("nTopCount"=>1),
        array("PROPERTY_MARK")
    );
    if($res->GetNext()){
        echo json_encode(array("error"=>"Вы уже голосовали за этот товар"));
        die;
    }
    
    // Получаем все оценки для товара
    $res = CIBlockElement::GetList(
        array(),
        $arrr = array("IBLOCK_CODE"=>"marks","PROPERTY_MARK_PRODUCT"=>$product),
        false,
        array(),
        array("PROPERTY_MARK")
    );
    $count = 0;
    $sum = 0;
    while($row = $res->GetNext()){
        $count++;
        $sum+=$row["PROPERTY_MARK_VALUE"];
    }
    // Вычисляем новое среднее
    $answer["percent"] = ($sum+$mark)/($count+1)/5;
    
    // Обновляем рейтинг товара
    CIBlockElement::SetPropertyValueCode($product,"RATING",$answer["percent"]);
    
    
    
    // Записываем оценку
    $res = CIBlock::GetList(array(),array("CODE"=>"marks"));
    $iblock = $res->GetNext();
    $iblockId = $iblock["ID"];
    $objIBlockElement = new CIBlockElement;
    if(!$elementId = $objIBlockElement->Add(array(
        "NAME"=>$product."_".CUser::GetID(),
        "IBLOCK_ID"=>$iblockId
    ))){
        echo json_encode(
            array("error"=>"Ошибка добавления"
                .print_r($objIBlockElement,1))
        );
        die;
    }
    CIBlockElement::SetPropertyValues($elementId,$iblockId,array(
        "MARK_PRODUCT"=>array("VALUE"=>$product),
        "MARK"=>array("VALUE"=>$mark),
        "MARK_USER"=>array("VALUE"=>CUser::GetID()),
    ));

}
elseif(isset($_GET["add_order"])){
    require(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CLock/CLock.class.php"
    );
    // Пользователь не может сделать более одного заказа в 10 секунд
    $objLock = new \Lock\CLock("USERORDER", $USER->GetID(), 10);
    if($objLock->isLocked()){echo json_encode(["no"]);die;}
    $objLock->lock();

    
    if(!$nQuantity = intval($_GET["quantity"]))$nQuantity = 1;
    $nId = intval($_GET["id"]);
    $nStoreId = intval($_GET["store_id"]);
    
    $objCOrder = new \Order\COrder;
    $objCOrder->setParam("UserId", $USER->GetID());
    $objCOrder->addSKU($nId, $nStoreId, $nQuantity);

    if(!$nOrderId = $objCOrder->createFromSite())
        $answer = ["order"=>["ERROR"=>$objCOrder->getErrors()]];
    else
        $answer["redirect_url"] = "/profile/order/";        
}
elseif(isset($_GET["wish"])){

    if(
        isset($_COOKIE["LOGIN"])
        &&
        $_COOKIE["LOGIN"]
    ) $sUserLogin = $_COOKIE["LOGIN"];
    
    $objCatalogProduct = new \Catalog\CCatalogProduct;

    // Чистим кэш плиток
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/customcache.lib.php");
    customCacheClear($sDir = '',$sUserLogin);

    $act =  $_GET["wish"]=='on'?'on':'off';
    $productId = isset($_GET["productid"])?intval($_GET["productid"]):0;
    $userId = CUser::GetID();

    $answer["wishes"] = $objCatalogProduct->wish($productId, $act, $userId);
    if($objCatalogProduct->getErrors())
        $answer = ["order"=>["ERROR"=>$objCatalogProduct->getErrors()]];
    else{
        // Перердаем классы для удаления и переключения
        if($act=='on'){
            $answer["addclass"] = 'wish-on';
            $answer["removeclass"] = 'wish-off';;
        }
        elseif($act=='off'){
            $answer["addclass"] = 'wish-off';
            $answer["removeclass"] = 'wish-on';;
        }
    }
}
elseif(isset($_GET["cancel"]) && $order_id=intval($_GET["cancel"])){
    require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");
    $sOrderPrefix = getOrderPrefixById($order_id);

    if($sOrderPrefix=='М'){
        $answer = array(
            "order"=>array(
                "ERROR"=>array(
                    "Невозможно вернтуть заказ с префиксом М"
                )
            )
        );
        echo json_encode($answer);
        die;
    }

    $arProperties =  orderGetProperties($order_id,["CHANGE_REQUEST"]);
    if(
        !isset($arProperties["CHANGE_REQUEST"]["VALUE"])
        ||
        !trim($arProperties["CHANGE_REQUEST"]["VALUE"])
    ){
        // Проверить принадлежит ли заказ пользователю
        CModule::IncludeModule('sale');
        CModule::IncludeModule('iblock');
        $order = CSaleOrder::GetByID($order_id);
        
        // Определяем для каждого заказа возможность его отменить
        $res = CIBlockElement::GetList(
            array(),
            array("IBLOCK_ID"=>OFFER_IB_ID),
            false,
            array("nTopCount"=>1),
            array("IBLOCK_ID")
        );
        $res = $res->GetNext();
        $IBlockId = $res["IBLOCK_ID"];
        
        $res = CIBlockElement::GetList(
            array(),
            array("IBLOCK_ID"=>CATALOG_IB_ID),
            false,
            array("nTopCount"=>1),
            array("IBLOCK_ID")
        );
        $res = $res->GetNext();
        $IBlockId2 = $res["IBLOCK_ID"];

        // Вычисляем может ли быьт заказ отменён
        $res = CSaleBasket::GetList(array(),array("ORDER_ID"=>$order_id),false);
        $canCancel = true;
        while($product = $res->GetNext()){

            $res = CIBlockElement::GetProperty(
                $IBlockId,$product["PRODUCT_ID"],array(), array("CODE"=>"CML2_LINK")
            );
            $res = $res->GetNExt();
            
            $res = CIBlockElement::GetProperty(
                $IBlockId2,$res["VALUE"],array(), array("CODE"=>"CANCEL_ABILITY")
            );
            $prop = $res->GetNext();
            
            if(!$prop["VALUE_ENUM"]){
                $canCancel = false;
                break;
            }

        }
        if(!$canCancel){
            $answer = array("error"=>"Заказ ID=$order_id не может быть отменн");
            die;
        }

        
        if(!isset($order["USER_ID"])){
            $answer = array("error"=>"Нет заказа с ID=$order_id");
        }
        elseif(isset($order["USER_ID"]) && $order["USER_ID"]!=CUser::GetID()){
            $answer = array("error"=>"Это заказ другого пользователя");
        }
        elseif(isset($order["USER_ID"]) && $order["USER_ID"]==CUser::GetID() 
            // Нельзя отменять заказы из опенкарта
            && preg_match("#^.*\-\d+$$#", $order["ADDITIONAL_INFO"])
        ){
            require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/order.class.php");
            // Если у заказа уже есть ЗНИ
            OrderSetZNI($order["ID"],"AG",$order["STATUS_ID"]);

            /*
            if(!CSaleOrder::CancelOrder($order["ID"],"Y","Передумал")){
                //$answer["error"] .= "Заказ не был отменён.";
            }
            else{
                //CSaleOrder::StatusOrder($order["ID"],"AG");
            }
            */

            /*

            Смена статуса и манебэк перенесены в success - ответ

            $obOrder = new bxOrder();
            $resOrder = $obOrder->addEMPPoints(
                $order["SUM_PAID"],
                "Отмена заказа Б-".$order["ID"]." в магазине поощрений АГ"
            );
            $moneyBack = true;
            CSaleOrder::PayOrder($order["ID"],"N",true,false);
            CSaleOrder::StatusOrder($order["ID"],"AG");
            eventOrderStatusSendEmail(
                $order["ID"], ($ename="AG"), ($arFields = array()), ($stat= "AG")
            );

            */

            //CSaleOrder::Update($order["ID"], array("DATE_UPDATE"=>'00.00.0000 00:00:00'));
       }
   }
}
else{
    if(!isset($_GET["offer_id"]) || !$offer_id = intval($_GET["offer_id"])){
        $answer["error"] = "Offer ID is not defined";
    }
    elseif(!isset($_GET["store_id"]) || !$store_id = intval($_GET["store_id"])){
    }
    else{
        $res = CUser::GetByID(CUser::GetID());
        $answer["profile"] = $res->GetNext();
        $answer["profile"]["SESSID"] = bitrix_sessid();
        
        // Получаем информацию по складу
        CModule::IncludeModule('catalog');
        CModule::IncludeModule('sale');
        $res = CCatalogStore::GetList(array(),array("ID"=>$store_id),false,array("nTopCount"=>1));
        $answer["store"] = $res->GetNext();

        // Получаем информацию по товарному предложению
        $res = CCatalogProduct::GetList(array(),array("ID"=>$offer_id),false,array("nTopCount"=>1));
        $product = $res->GetNext();
        
        $answer["product"] = $product;
        $answer["price"] = CCatalogProduct::GetOptimalPrice($offer_id);
        $objSSAGAccount = new \SSAG\CSSAGAccount('',CUser::GetID());
        $answer["account"] = $objSSAGAccount->balance();
    }
}

echo json_encode($answer);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
