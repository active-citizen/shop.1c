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
elseif(isset($_GET["mark"]) && isset($_GET["product"])){
    $mark = intval($_GET["mark"]);
    $product=intval($_GET["product"]);
    CModule::IncludeModule('iblock');

    // Смотрим, не было ли уже оценки у этого пользователя
    $res = CIBlockElement::GetList(
        array(),
        array("IBLOCK_CODE"=>"marks","PROPERTY_MARK_PRODUCT"=>$product,"PROPERTY_MARK_USER"=>CUser::GetID()),
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
        echo json_encode(array("error"=>"Ошибка добавления".print_r($objIBlockElement,1)));
        die;
    }
    CIBlockElement::SetPropertyValues($elementId,$iblockId,array(
        "MARK_PRODUCT"=>array("VALUE"=>$product),
        "MARK"=>array("VALUE"=>$mark),
        "MARK_USER"=>array("VALUE"=>CUser::GetID()),
    ));

}
elseif(isset($_POST["create_order"]) && $offer_id=intval($_POST["create_order"])){
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/order.class.php");
    $BxOrder = new bxOrder();
    $BxOrder->createOrder(array(
        "quantity"  => $_POST["quantity"],
        "offer_id"  => $_POST["create_order"],
        "store_id"  => $_POST["store_id"],
        "name"      => $_POST["name"],
        "email"     => $_POST["email"],
        "address"   => $_POST["address"]
    ));
    echo json_encode(array("redirect_url"=>"/profile/order/"));
}
elseif(isset($_GET["add_to_basket"])){
    CModule::IncludeModule('sale');
    CModule::IncludeModule('catalog');
    CModule::IncludeModule('price');
    CModule::IncludeModule('iblock');
    $res = CCatalogProduct::GetList(array(),array("ID"=>intval($_GET['id'])),false,array("nTopCount"=>1));
    $product = $res->GetNext();
    if(!$product){
        echo json_encode(array("STATUS"=>"FAILED","MESSAGE"=>"Товар не найден"));
        die;
    }
    $price = CPrice::GetBasePrice($product["ID"]);
    if(!$price){
        echo json_encode(array("STATUS"=>"FAILED","MESSAGE"=>"Нет цены"));
        die;
    }
    
    $res = CIBlock::GetList(array(),array("CODE"=>"clothes_offers"));
    $iblock = $res->GetNext();
    $OfferIblockId = $iblock["ID"];
    
    $res = CIBlockElement::GetProperty($OfferIblockId,$product["ID"]);
    
    $props = array();
    while($prop = $res->GetNext()){
        if($prop["CODE"]=='CML2_LINK')continue;
        if($prop["CODE"]=='MORE_PHOTO')continue;
        if(!$prop["VALUE"])continue;
        if($prop["PROPERTY_TYPE"]=='E')$prop["VALUE"] = $prop["VALUE"];
        if($prop["PROPERTY_TYPE"]=='S')$prop["VALUE"] = $prop["VALUE"];
        if($prop["PROPERTY_TYPE"]=='L')$prop["VALUE"] = $prop["VALUE_ENUM"];
        $props[] = array(
            "NAME" => $prop["NAME"],
            "CODE" => $prop["CODE"],
            "VALUE" => $prop["VALUE"]
        );
    }
    
    $arFields = array(
        "PRODUCT_ID"=>  $product["ID"],
        "PRICE"     =>  $price["PRICE"],
        "CURRENCY"  =>  "BAL",
        "QUANTITY"  =>  intval($_GET["quantity"]),
        "LID" => LANG,
        "DELAY" => "N",
        "CAN_BUY" => "Y",
        "NAME"    =>    $product["ELEMENT_NAME"],
        "MODULE" => "catalog",
        "PROPS" =>  $props
    );
    
    $resBasket = new CSaleBasket;
    if(!$resBasket->Add($arFields)){
        echo json_encode(array("STATUS"=>"FAILED","MESSAGE"=>"Товар не добавлен:".print_r($resBasket)));
        die;    
    }
    $answer = array("STATUS"=>"OK","MESSAGE"=>"Товар добавлен","store_id"=>intval($_GET["store_id"]));
}
elseif(isset($_GET["add_order"])){
    CModule::IncludeModule('sale');
    
    $res = CSaleBasket::GetList(array("DATE_INSERT"=>"DESC"), array(
        "FUSER_ID" => CSaleBasket::GetBasketUserID(),
        "LID" => SITE_ID,
        "ORDER_ID" => "NULL"
    ),false,false,array("ID"));
    
    if(!$basket = $res->GetNext()){
        $answer = array("error"=>"Корзина пуста");
        echo json_encode($answer);
        die;
    }
    $basketId = $basket["ID"];
    
    $arrBasket = CSaleBasket::GetByID($basketId);
    
    $res = CSalePaySystem::GetList(array(),array("ACTIVE"=>"Y"));
    if(!$paySystem = $res->GetNext()){
        $answer = array("error"=>"Нет активных платёжных систем");
        echo json_encode($answer);
        die;
    }
    
    $res = CSaleDelivery::GetList(array(),array("ACTIVE"=>"Y"));
    if(!$delivery = $res->GetNext()){
        $answer = array("error"=>"Нет активных служб доставки");
        echo json_encode($answer);
        die;
    }
    
    // Проверяем сумму на счёте
    $account = CSaleUserAccount::GetByUserID(CUSer::GetID(),"BAL");
    $totalSum = $arrBasket["PRICE"]*$arrBasket["QUANTITY"];
    if($account["CURRENT_BUDGET"]<$totalSum){
        $answer = array("error"=>"Недостаточно баллов на счёте");
        echo json_encode($answer);
        die;
    }
    
    $arFields = array();
    $arFields["LID"] = SITE_ID;
    $arFields["PERSON_TYPE_ID"] = 1;
    $arFields["STORE_ID"] = intval($_GET["store_id"]);
    $arFields["PAYED"] = 'N';
    $arFields["CANCELED"] = "N";
    $arFields["STATUS_ID"] = "N";
    $arFields["PRICE"] = $totalSum;
    $arFields["CURRENCY"] = "BAL";
    $arFields["USER_ID"] = CUSer::GetID();
    //$arFields["PAY_SYSTEM_ID"] = $paySystem["ID"];
    $arFields["PRICE_DELIVERY"] = 0;
    $arFields["DELIVERY_ID"] = $delivery["ID"];
    $arFields["DISCOUNT_VALUE"] = 0;
    $arFields["TAX_VALUE"] = 0;
    $arFields["USER_DESCRIPTION"] = "";

    $resCSaleOrder = new CSaleOrder;
    if($orderId = $resCSaleOrder->Add($arFields)){
        CSaleBasket::OrderBasket($orderId, $_SESSION["SALE_USER_ID"], SITE_ID);
//        CSaleUserTransact::Add(array("USER_ID"=>CUSer::GetID(),"AMOUNT"=>$totalSum,"CURRENCY"=>"BAL","DEBIT"=>"N","ORDER_ID"=>$orderId))
        CSaleOrder::PayOrder($orderId,"Y",true,false);
        $answer["redirect_url"] = "/profile/order/detail/$orderId/";
    }
    else{
        $answer["error"] = "Не могу создать заказ: ".($account["CURRENT_BUDGET"]+2*$totalSum);
    }
    
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
    CModule::IncludeModule('iblock');
    $order = CSaleOrder::GetByID($order_id);
    
    // Определяем для каждого заказа возможность его отменить
    $res = CIBlockElement::GetList(array(),array("IBLOCK_CODE"=>"clothes_offers"),false,array("nTopCount"=>1),array("IBLOCK_ID"));
    $res = $res->GetNext();
    $IBlockId = $res["IBLOCK_ID"];
    
    $res = CIBlockElement::GetList(array(),array("IBLOCK_CODE"=>"clothes"),false,array("nTopCount"=>1),array("IBLOCK_ID"));
    $res = $res->GetNext();
    $IBlockId2 = $res["IBLOCK_ID"];

    // Вычисляем может ли быьт заказ отменён
    $res = CSaleBasket::GetList(array(),array("ORDER_ID"=>$order_id),false);
    $canCancel = true;
    while($product = $res->GetNext()){

        $res = CIBlockElement::GetProperty($IBlockId,$product["PRODUCT_ID"],array(), array("CODE"=>"CML2_LINK"));
        $res = $res->GetNExt();
        
        $res = CIBlockElement::GetProperty($IBlockId2,$res["VALUE"],array(), array("CODE"=>"CANCEL_ABILITY"));
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
    elseif(isset($order["USER_ID"]) && $order["USER_ID"]==CUser::GetID()){
        CSaleOrder::PayOrder($order_id,"N",true,false);
        if(!CSaleOrder::CancelOrder($order_id,"Y","Передумал")){
            $answer["error"] .= "Заказ не был отменён.";
        }
        else{
            CSaleOrder::StatusOrder($order_id,"AG");
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
