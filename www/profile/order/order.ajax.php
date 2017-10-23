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
    $res = CCatalogProduct::GetList(
        array(),
        array("ID"=>intval($_GET['id'])),
        false,
        array("nTopCount"=>1)
    );
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
    // Чистим корзину()
    $resBasket->DeleteAll(CSaleBasket::GetBasketUserID());
    // Добавляем в корзину товар
    if(!$resBasket->Add($arFields)){
        echo json_encode(
            array(
                "STATUS"=>"FAILED",
                "MESSAGE"=>"Товар не добавлен:"
                    .print_r($resBasket)
            )
        );
        die;    
    }

    $answer = array(
        "STATUS"=>"OK",
        "MESSAGE"=>"Товар добавлен",
        "store_id"=>intval($_GET["store_id"])
    );
}
elseif(isset($_GET["add_order"])){
    CModule::IncludeModule('sale');
    CModule::IncludeModule("catalog");
    require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");
    
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

    // Проверяем месячный лимит (возвращает месячный лимит если он исчерпан)
    if($failedLimit = failedMonLimit(CUSer::GetId(),$arrBasket["PRODUCT_ID"])){
        $answer = array(
            "order"=>array(
                "ERROR"=>array(
                    "Вы исчерпали месячный лимит заказов данного поощрения."
                )
            )
        );
        echo json_encode($answer);
        die;
    }


    // Получаем ID элемента каталога для данного предложения
    $arProduct = CIBlockElement::GetList(
        array(),
        array(
            "IBLOCK_ID" =>  OFFER_IB_ID,
            "ID"        =>  $arrBasket["PRODUCT_ID"]
        ),
        false,
        array("nTopCount"=>1),
        array(
            "PROPERTY_CML2_LINK"
        )
    )->GetNext();
    // Получаем свойство элемента каталога "НЕВЫБИРВЕМЫЙ ОСТАТОК"
    $arStoreLimit = CIBlockElement::GetProperty(
        CATALOG_IB_ID,
        $arProduct["PROPERTY_CML2_LINK_VALUE"],
        array(),
        array("CODE"=>"STORE_LIMIT")
    )->GetNExt();
   
    $nStoreLimit = DEFAULT_STORE_LIMIT;
    if(
        isset($arStoreLimit["VALUE"])
        && 
        $arStoreLimit["VALUE"]
    )
    $nStoreLimit = $arStoreLimit["VALUE"];

    // Получаем свойство элемента каталога "АРТИКУЛ"
    $arArtNumber = CIBlockElement::GetProperty(
        CATALOG_IB_ID,
        $arProduct["PROPERTY_CML2_LINK_VALUE"],
        array(),
        array("CODE"=>"ARTNUMBER")
    )->GetNExt();
    
    $sArtNumber = $arArtNumber["VALUE"];


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


    // Получаем состояние счёта чере API
    /*

    Обновляется при каждой загрузке страницы - тут не нужно


    equire_once(
        $_SERVER["DOCUMENT_ROOT"]
            ."/.integration/classes/active-citizen-bridge.class.php"
    );
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/point.class.php");
    $objPoints = new bxPoint; 
    $arPoints = $objPoints->fetchAccountFromAPI();
    if(
        $arPoints["errors"]
        ||
        !isset($arPoints["status"]["current_points"])
    ){
        $answer = array(
            "order"=>array(
                "ERROR"=>array(
                    "Ошибка получения состояния счёта.".
                    print_r($arPoints["errors"], 1)
                )
            )
        );
        echo json_encode($answer);
        die;
    }
    $account = ["CURRENT_BUDGET"=>$arPoints["status"]["current_points"]]; 
    */

    // Проверяем сумму на счёте
    $totalSum = $arrBasket["PRICE"]*$arrBasket["QUANTITY"];
    $account = CSaleUserAccount::GetByUserID(CUSer::GetID(), 'BAL');
    if($account["CURRENT_BUDGET"]<$totalSum){
        $answer = array(
            "order"=>array(
                "ERROR"=>array(
                    "Недостаточно баллов на счёте"
                )
            )
        );
        echo json_encode($answer);
        die;
    }
    // Проверяем количество на складе
    $arProductStore = CCatalogStoreProduct::GetList(
        array(),
        array(
            "STORE_ID"  =>  intval($_GET["store_id"]),
            "PRODUCT_ID"=>  $arrBasket["PRODUCT_ID"]
        )
    )->GetNext();


    if(
        (
        !isset($arProductStore["AMOUNT"])
        ||
        $arProductStore["AMOUNT"]<=0
        )
    ){
        $answer = array(
            "order"=>array(
                "ERROR"=>array(
                    "Исчерпание остатков."
                )
            )
        );
        echo json_encode($answer);
        die;
    }
    $arFields = array();
    $arFields["LID"] = SITE_ID;
    $arFields["PERSON_TYPE_ID"] = 1;
    $arFields["STORE_ID"] = intval($_GET["store_id"]);
    $arFields["PAYED"] = 'N';
    $arFields["CANCELED"] = "N";
    $arFields["STATUS_ID"] = "AA";
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
    /////////////////////
    // Успешное добавление заказа
    ///////////////////
    if($orderId = $resCSaleOrder->Add($arFields)){
        $sOrderNum = 'Б-'.$orderId;
        //// Запоминаем номер заказа
        $resCSaleOrder->Update($orderId,array("ADDITIONAL_INFO"=>$sOrderNum));
        // Делаем заказ оплаченным

        CSaleOrder::PayOrder(
            $orderId, "Y", false
        );


        // Назначаем заказу корзину
        CSaleBasket::OrderBasket($orderId, $_SESSION["SALE_USER_ID"], SITE_ID);

        // Утратила актуальность в связи с добавление свойства заказа ЗНИ
        // 21.07.2017
        // CSaleOrder::Update($orderId, array("DATE_UPDATE"=>'00.00.00 00:00:00'));
        $answer["redirect_url"] = "/profile/order/";

        //// Обновляем свойства заказа из значений товарного каталога
        orderPropertiesUpdate($orderId);

        // Статус тройки
        // 0 - не заказывалась
        // 1 - успешный заказ
        // 2 - ошибочный заказ
        $stoykaStatus = 0;
        /////// Действия над картой-тройкой
        if(isset($_REQUEST["troyka"]) && $nTroykaNum = trim($_REQUEST["troyka"])){
            require_once(
                $_SERVER["DOCUMENT_ROOT"]
                    ."/.integration/classes/troyka.class.php"
            );

            // Подключаемся и получаем настройки шлюза
            $objTroyka = new CTroyka($nTroykaNum);            
            if($objTroyka->error){
                $answer = array(
                    "order"=>array(
                        "ERROR"=>array(
                            $objTroyka->error
                        )
                    )
                );
            }

            // Запоминаем для заказа номер тройки
            $objTroyka->linkOrder($sOrderNum);
            if($objTroyka->error){
                $answer = array(
                    "order"=>array(
                        "ERROR"=>array(
                            $objTroyka->error
                        )
                    )
                );
            }

            // Производим транзакцию в тройку
            $objTroyka->payment($sOrderNum);
            if($objTroyka->error){
                // Мапинг кодов ошибок шлюза в сообщения для посетителя
                $arErrors = $objTroyka->errorMapping();
                $answer = array(
                    "order"=>array(
                        "ERROR"=>array(
                            isset($arErrors["messageText"])
                            ?
                            $arErrors["messageText"]
                            :
                            $objTroyka->error
                        )
                    )
                );
                // Сохраняем неуспешный статус
                $stoykaStatus = 2;
            }
            else{
                // Сохраняем успешный статус
                $stoykaStatus = 1;
            }

            // Запоминаем номер транзакции тройки при любом статусе
            $objTroyka->linkOrderTransact($sOrderNum);
        }

        // Статус парковки
        // 0 - не заказывалась
        // 1 - Успешно
        // 2 - неудачно
        $sParkingStatus = 0;
        if($sArtNumber=='parking'){
            require_once(
                $_SERVER["DOCUMENT_ROOT"]
                    ."/.integration/classes/parking.class.php"
            );
            $arUser = $USER->GetById($USER->GetId())->Fetch();
            $objParking = new CParking(str_replace("u","",$arUser["LOGIN"]));


            // Производим транзакцию в парковку
            $objParking->payment($sOrderNum);
            if($objParking->error){
                // Мапинг кодов ошибок шлюза в сообщения для посетителя
                $answer = array(
                    "order"=>array(
                        "ERROR"=>array(
                            $objParking->error.(
                                IS_MOBILE
                                ?
                                " Попробуйте выйти из мобильного приложения и
                                снова зайти под своим аккаунтом."
                                :
                                " Попробуйте выйти и снова зайти в свой аккаунт
                                на сайте http://ag.mos.ru."
                            )
                        )
                    )
                );
                // Сохраняем неуспешный статус
                $sParkingStatus = 2;
            }
            else{
                // Сохраняем успешный статус
                $sParkingStatus = 1;
            }
            // Запоминаем номер транзакции 
            $objParking->linkOrderTransact($sOrderNum);
        }

        // Если тойка провалилась - баллы не снимаем
        if($stoykaStatus == 2){
        }
        // Если парковка провалилась - баллы не снимаем
        elseif($sParkingStatus == 2){
        }
        else{
            //////////// Снимает баллы
            require_once(
                $_SERVER["DOCUMENT_ROOT"]
                    ."/.integration/classes/order.class.php"
            );
            $obOrder = new bxOrder();
            if(!$bPointsSuccess = $obOrder->addEMPPoints(
                -$totalSum,
                "Заказ Б-$orderId в магазине поощрений АГ"
            )){
                $answer = [
                    "order"=>[
                        "ERROR" => [$obOrder->error]
                    ]
                ];
            }

            ///////////
        }

        // Если тойка провалилась - остатки не снимаем
        if($stoykaStatus == 2){
        }
        // Если парковка провалилась - остатки не снимаем
        elseif($sParkingStatus == 2){
        }
        // Если не снялись баллы - остатки не снимаем
        elseif($bPointsSuccess){
            //////////////////////// Снимаем остатки ////////////////////////
            // Получаем список товаров к заказу
            $sql = "SELECT PRODUCT_ID,QUANTITY FROM `b_sale_basket` WHERE
            `ORDER_ID`=".intval($orderId);
            $res = $DB->Query($sql);
            while($arProduct = $res->Fetch()){
                $nQuantity = $arProduct["QUANTITY"];
                $nProductId = $arProduct["PRODUCT_ID"];
                $nStoreId = intval($_GET["store_id"]);
                // Смотрим сколько этого товара на складе
                $nCQuantity = 0;
                // Если записей с остатком нет - пропустить его
                if($arStoreProduct = CCatalogStoreProduct::GetList(
                    array(),
                    $arStoreProductFilter = array(
                        "PRODUCT_ID"=>  $nProductId,
                        "STORE_ID"  =>  $nStoreId
                    ),
                    false,
                    array("nTopCount"=>1),
                    array("ID","PRODUCT_ID","AMOUNT")
                )->GetNext()){
                    $nCQuantity = $arStoreProduct["AMOUNT"];
                }
                else{
                    continue;
                }
           
                // Устанавливаем новое значение остатка
                if(!CCatalogStoreProduct::Update(
                    $arStoreProduct["ID"],
                    $arF = array(
                        "AMOUNT"              =>  
                            $nCQuantity-$nQuantity>=0
                            ?
                            $nCQuantity-$nQuantity
                            :
                            0
                ))){
                    print_r($objCCatalogStoreProduct);
                    die;
                }
                
            }
        }
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

        // Обновляем индексную таблицу
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/indexes.lib.php");
        syncUser(CUser::GetID());
        if($orderId)syncOrder($orderId);

        ///// Ставим в очередь на ЗНИ
        // Если тойка успешно заказалась - Выполнен
        if($stoykaStatus == 1)
            orderSetZNI($orderId,'F','AA');
        // Если тойка провалилась - Аннулирован
        elseif($stoykaStatus == 2)
            orderSetZNI($orderId,'AF','AA');
        // Если парковка успешно заказалась - Выполнен
        elseif($sParkingStatus == 1)
            orderSetZNI($orderId,'F','AA');
        // Если парковка провалилась - Аннулирован
        elseif($sParkingStatus == 2)
            orderSetZNI($orderId,'AF','AA');
        // Если не удалось снять баллы - аннулирован
        elseif(!$bPointsSuccess)
            orderSetZNI($orderId,'AF','AA');
        // Во всех остальных случаях - В работее
        else
            orderSetZNI($orderId,'N','AA');
     }
    else{
        $answer["error"] = "Не могу создать заказ: "
            .($account["CURRENT_BUDGET"]+2*$totalSum);
    }
    
}
elseif(isset($_GET["clear_basket"])){
    CModule::IncludeModule('sale');
    CSaleBasket::DeleteAll(CUser::GetID());    
}
elseif(isset($_GET["wish"])){

    if(
        isset($_COOKIE["LOGIN"])
        &&
        $_COOKIE["LOGIN"]
    ) $sUserLogin = $_COOKIE["LOGIN"];

    // Чистим кэш плиток
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/customcache.lib.php");
    customCacheClear($sDir = '',$sUserLogin);

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
    require($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");
    
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
