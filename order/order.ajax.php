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
elseif(isset($_GET["wish"])){
    $act =  $_GET["wish"]=='on'?'on':'off';
    
    CModule::IncludeModule('iblock');
    // Проверяем есть ли такой товар
    $productId = isset($_GET["productid"])?intval($_GET["productid"]):0;
    
    $userId = CUser::GetID();
    if(!$productId){
        $answer = array("error"=>"Не указан ID товара");
        echo json_encode($answer);
        die;
    }
    if(!$userId){
        $answer = array("error"=>"Не указан ID пользователя");
        echo json_encode($answer);
        die;
    }
    
    $arFields = array("ID"=>$productId,"IBLOCK_CODE"=>"clothes");
    $res = CIBlockElement::GetList(array(),$arFields,false);
    if(!$res->GetNext()){
        $answer = array("error"=>"Товар с ID=$productId не существует");
        echo json_encode($answer);
        die;
    }
    
    // Узнаём ID инфоблока
    $res = CIBlock::GetList(array(),array("CODE"=>"whishes"));
    $iblock = $res->GetNext();
    

    $arFields = array("IBLOCK_ID"=>$iblock["ID"],"NAME"=>$productId."_".$userId);
    // Ишем желание с этими условиями
    $res = CIBlockElement::GetList(array(),$arFields,false);
    $elementId = $res->GetNext();
    
    // Если надо добавить, но уже есть
    if($act=='on' && $elementId){
        $answer = array("error"=>"Желание этого товара этим пользователем уже добавлено");
        echo json_encode($answer);
        die;
    }
    // Если надо удалить, но нечего
    elseif($act=='off' && !$elementId){
        $answer = array("error"=>"Желание этого товара этим пользователем не добавлено");
        echo json_encode($answer);
        die;
    }
    
    $iblockObj = new CIBlockElement;
    // Добавление
    if($act=='on' && $elementId = $iblockObj->Add($arFields)){
        // Устанавливаем свойства
        CIBlockElement::SetPropertyValues($elementId,$iblock["ID"],array("WISH_USER"=>$userId,"WISH_PRODUCT"=>$productId));
    }
    // Удалить
    elseif($act=='off'){
        $iblockObj->Delete($elementId["ID"]);
    }
    // Сообщить об ошибке
    else{
        $answer["error"] = $iblock->LAST_ERROR;
    }
    
    // Получаем актуальное число вишей, если нет ошибок
    if(!$answer["error"]){
        $res = CIBlockElement::GetList(array(),array(
            "IBLOCK_ID"=>$iblock["ID"],
            "PROPERTY_WISH_PRODUCT"=>$productId
        ),false);
        
        $answer["wishes"] = $res->SelectedRowsCount();
    }
    
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
