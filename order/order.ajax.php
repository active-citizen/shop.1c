<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$answer = array("error"=>"");

global $USER;
if(!$USER->IsAuthorized()){
    $answer["error"] = "Not Authorized";
}
elseif(isset($_GET["clear_basket"])){
    CModule::IncludeModule('sale');
    CSaleBasket::DeleteAll(CUser::GetID());    
}
elseif(isset($_GET["cancel"]) && $order_id=intval($_GET["cancel"])){
    // Проверить принадлежит ли заказ пользователю
    CModule::IncludeModule('sale');
    $order = CSaleOrder::GetByID($order_id);
    if(!isset($order["USER_ID"])){
        $answer = array("error"=>"Нет заказа с ID=$order_id");
    }
    elseif(isset($order["USER_ID"]) && $order["USER_ID"]!=CUser::GetID()){
        $answer = array("error"=>"Это заказ другого пользователя");
    }
    elseif(isset($order["USER_ID"]) && $order["USER_ID"]==CUser::GetID()){
        CSaleOrder::PayOrder($order_id,"N",true);
        if(!CSaleOrder::CancelOrder($order_id,"Y","Передумал")){
            $answer["error"] .= "Заказ не был отменён.";
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
        $answer["account"] = CSaleUserAccount::GetByUserID(CUser::GetID(),"RUB");
    }
}

echo json_encode($answer);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
