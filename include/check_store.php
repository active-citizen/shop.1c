<?php
// Получаем торговые предложения корзины залогиненного пользователя
$res = CSaleBasket::GetList(array(),array("USER_ID"=>CUser::GetID(),"ORDER_ID"=>''));
// Создаём массив торговых предложений по ID с количеством
$tradeOffers = array();
while($goods_in_cart = $res->GetNext()){
    $tradeOffers[$goods_in_cart["PRODUCT_ID"]] = $goods_in_cart["QUANTITY"];
}
// Для каждого торгового предложения проверяем на каких складах есть достаточное количество товара
$stores = array();
foreach($tradeOffers as $offerId=>$quantity){
    $res = CCatalogStoreProduct::GetList(array(),array("=PRODUCT_ID"=>$offerId,"!STORE_ID"=>1));
    while($store = $res->GetNext()){
        if(!isset($stores[$store["STORE_ID"]]))$stores[$store["STORE_ID"]] = array();
        // Запоминаем в складе количество товара, если этого количества достаточно
        if($store["AMOUNT"]>=$tradeOffers[$store["PRODUCT_ID"]])$stores[$store["STORE_ID"]][$store["PRODUCT_ID"]] = $store["AMOUNT"];
            
    }
}
// Оставляем только те склады, на которых есть все торговые предложения
foreach($stores as $store_id=>$offers)
    if(count($stores[$store_id])<count($tradeOffers))unset($stores[$store_id]);
