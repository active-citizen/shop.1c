<?
namespace Order;

require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CUser/CUser.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogSKU.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogOffer.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogStore.class.php");
require_once(realpath(__DIR__."/..")."/CIntegration/CIntegration.class.php");
require_once(realpath(__DIR__."/..")."/CIntegration/CIntegrationTroyka.class.php");
require_once(realpath(__DIR__."/..")."/CIntegration/CIntegrationParking.class.php");
require_once(realpath(__DIR__."/..")."/CSync/CSync.class.php");
require_once(realpath(__DIR__)."/COrderStatus.class.php");
require_once(realpath(__DIR__)."/COrderProperty.class.php");
require_once(realpath(__DIR__."/..")."/CSSAG/CSSAGAccount.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\User as User;
use AGShop\Catalog as Catalog;
use AGShop\Order as Order;
use AGShop\Sync as Sync;
use AGShop\Integration as Integration;
use AGShop\SSAG as SSAG;

/**
    Управление заказами
*/
class COrder extends \AGShop\CAGShop{
    
    private $arOrderParams = []; // Массив параметров заказа
    private $arSKUs = [];
    private $arProps = [];
    
    private $arEnabledOrderParams = [
        "Id"            =>  "ID заказа",
        "Num"           =>  "Номер заказа",
        "UserId"        =>  "Автор заказа",
        "StatusId"      =>  "Статус заказа",
        "XML_ID"        =>  "XML_ID заказа",
        "DateInsert"    =>  "Дата добавления заказа",
        "DateUpdate"    =>  "Дата обновления заказа"
    ];

    
    function __construct(){
        parent::__construct();
        $this->objUser = new \User\CUser;
        $this->objStatus = new \Order\COrderStatus;
        \CModule::IncludeModule("sale");
    }

    /**
        Добавление к заказу торгового предложения
    */
    function addSKU($nSKUId, $nStoreId, $nCount, $nPrice = 0){
        $nSKUId = intval($nSKUId);
        $nStoreId = intval($nStoreId);
        $nCount = intval($nCount);
        if(!$nCount){
            $this->addError("Неверно указано количество товара");
            return false;
        }
        
        $objStore = new \Catalog\CCatalogStore;
        if(!$objStore->fetch($nStoreId)){
            $this->addError("Склад с ID $nStoreId не существует");
            return false;
        }
        
        $objSKU = new \Catalog\CCatalogSKU;
        if(!$objSKU->fetch($nSKUId)){
            $this->addError("Не найти добавить торговое предложение $nSKUId ");
            return false;
        }
        
        $arSKU = $objSKU->get();
        
        // Тройки и парковки - строго по 1
        if(
            (
                isset($arSKU["PRODUCT_PROPERTIES"]["ARTNUMBER"])
                &&
                $arSKU["PRODUCT_PROPERTIES"]["ARTNUMBER"]=='troyka'
            )
            ||
            (
                isset($arSKU["PRODUCT_PROPERTIES"]["ARTNUMBER"])
                &&
                $arSKU["PRODUCT_PROPERTIES"]["ARTNUMBER"]=='parking'
            )
        )$nCount = 1;
        

        $this->arSKUs[] = [
            "SKU"       => $arSKU,
            "AMOUNT"    => $nCount,
            "STORE_ID"  => $nStoreId,
            "PRICE"     => $nPrice
        ];
        return true;
    }
    
    function getSKUs(){
        return $this->arSKUs;
    }

    function delete(){
        $CDB = new \DB\CDB;
        $nOrderId = $this->getParam("Id");
        
        $CDB->delete(\AGShop\CAGShop::t_sale_order,["ID"=>$nOrderId]);
        $CDB->delete(\AGShop\CAGShop::t_index_order,["ID"=>$nOrderId]);
        $CDB->delete(\AGShop\CAGShop::t_sale_basket,["ORDER_ID"=>$nOrderId]);
        $CDB->delete(\AGShop\CAGShop::t_sale_order_props_value,["ORDER_ID"=>$nOrderId]);
        return true;
    }


    /**
        Создание аукционного заказа

        @param $nUserId - пользователь, на ккотого заводим заказ
        @param $nOfferId - ID предложения
        @param $nStoreId - ID склада
        @param $nPrice - цена за единицу
        @param $nAmount - количество
    */
    function createFromAuction(
        $nUserId, 
        $nOfferId, $nStoreId, $nPrice, $nAmount,
        $arOptions = []
    ){
        if(!isset($arOptions["ZNI"]))$arOptions["ZNI"] = 'N';
        if(!isset($arOptions["PREFIX"]))$arOptions["PREFIX"] = 'Ц-';
        if(!isset($arOptions["DATE_ADD"]))$arOptions["DATE_ADD"] =
             date("d.m.Y H:i:s");


        $nTotalSum = $nPrice * $nAmount;
        $CDB = new \DB\CDB;
        $objCCatalogStore = new \Catalog\CCatalogStore;
        $objCCatalogOffer = new \Catalog\CCatalogOffer;

        // Снимаем нужное количество со склада
        $objCCatalogStore->move( $nOfferId, $nStoreId, -$nAmount);
        
        $res = \CSaleDelivery::GetList(array(),array("ACTIVE"=>"Y"));
        if(!$delivery = $res->GetNext()){
            $this->addError("Нет активных служб доставки");
            return false;
        }

        // Добавляем к заказу торговое предложение
        $this->addSKU($nOfferId, $nStoreId, $nAmount, $nPrice);
        // Добавляем заказ 
        $sInitialStatusId = 'AA';
        
        $arFields = [];
        $arFields["LID"] = SITE_ID;
        $arFields["PERSON_TYPE_ID"] = 1;
        $arFields["STORE_ID"] = $nStoreId;
        $arFields["PAYED"] = 'N';
        $arFields["CANCELED"] = "N";
        $arFields["STATUS_ID"] = $sInitialStatusId;
        $arFields["PRICE"] = $nTotalSum;
        $arFields["CURRENCY"] = "BAL";
        $arFields["USER_ID"] = $nUserId;
        //$arFields["PAY_SYSTEM_ID"] = $paySystem["ID"];
        $arFields["PRICE_DELIVERY"] = 0;
        $arFields["DELIVERY_ID"] = $delivery["ID"];
        $arFields["DISCOUNT_VALUE"] = 0;
        $arFields["TAX_VALUE"] = 0;
        $arFields["USER_DESCRIPTION"] = "";
        $arFields["DATE_INSERT"] = $arOptions["DATE_ADD"];
        $objCSaleOrder = new \CSaleOrder;
        if(!$nOrderId = $objCSaleOrder->Add($arFields)){
            $this->addError("Не удалось добавить заказ: "
                .$resCSaleOrder->LAST_ERROR);
            // Возвращаем всё что взяли на склад
            $this->returnToStore();
            return false;
        }
        
        $this->setParam("Id",$nOrderId);
        $this->setParam("Num",$arOptions["PREFIX"].$nOrderId);
        $sOrderNum = $this->getParam("Num");
        $this->setParam("StatusId",$sInitialStatusId);
        // Сохраняем параметры заказа
        $this->saveParams();
        $this->linkBasket($arSKUs);
        
        // Обновляем свойства заказа из свойств товара (для поиска)
        $this->orderPropertiesUpdate();
        
        // Обновляем индексную таблицу
        $objCSync = new \Sync\CSync;
        $objCSync->syncUser($nUserId);
        $nOrderId = $this->getParam("Id");
        if($nOrderId)$objCSync->syncOrder($nOrderId);

        $this->setZNI($arOptions["ZNI"],'AA');
        
        if(!$this->getErrors())return $nOrderId;
        return true; 
    }

    function createFromSite($sCustomNum = ''){
        $CDB = new \DB\CDB;
        // Получаем выбранные торговые предложения
        $arSKUs = $this->getSKUs();
        
        // Проверяем количество на складе каждого предложения и блокируем, 
        // если надо (снимаем единицу)
        $objCCatalogStore = new \Catalog\CCatalogStore;
        $objCCatalogOffer = new \Catalog\CCatalogOffer;
        // Массив для запоминания того, что наблокировали и сколько
        $arStoreLocked = [];
        $nTotalSum = 0;
        foreach($arSKUs as $nSkuNum=>$arSKU){
            if(!$objCCatalogOffer->isActive($arSKU["SKU"]["OFFER"]["ID"]))
                return $this->addError('Поощрения снято с реализации');
            // Получаем количество товара на нужном складе
            $nAmount = $objCCatalogStore->getProductAmount(
                $arSKU["SKU"]["OFFER"]["ID"],
                $arSKU["STORE_ID"]
            );
            if($nAmount>=$arSKU["AMOUNT"]){
                // Снимаем нужное количество со склада
                $objCCatalogStore->move(
                    $arSKU["SKU"]["OFFER"]["ID"],
                    $arSKU["STORE_ID"],
                    -$arSKU["AMOUNT"]
                );
                // Запоминаем что забрали
                $arStoreLocked[] = $nSkuNum;
                $nTotalSum += $arSKU["AMOUNT"]*$arSKU["SKU"]["PRODUCT_PROPERTIES"]["MAXIMUM_PRICE"];
            }
            else{
                // Возвращаем всё что взяли обратно на склад (не все позиции, а которые уже декрементировали)
                foreach($arStoreLocked as $nLockedItem)
                    $objCCatalogStore->move(
                        $arSKUs[$nLockedItem]["SKU"]["OFFER"]["ID"],
                        $arSKUs[$nLockedItem]["STORE_ID"],
                        $arSKUs[$nLockedItem]["AMOUNT"]
                    );
                $this->addError("Недостаточно товара ".$arSKU["SKU"]["OFFER"]["NAME"]
                    ." на складе "
                    .$objCCatalogStore->getTitleById($arSKU["STORE_ID"])
                );
                return false;
            }
        }
        ///////////// После этого считаем, что все SKU заказа зарезервированы на 
        ///////////// складе и все надо возвращать при ошибках
        
        // Проверяем месячные лимиты
        $objCCatalogOffer = new \Catalog\CCatalogOffer;
        foreach($arSKUs as $nSkuNum=>$arSKU){
            if(!$sCustomNum && $failedLimit = $objCCatalogOffer->failedMonLimit(
                $this->getParam("UserId"),
                $arSKU["SKU"]["OFFER"]["ID"],
                $arSKU["AMOUNT"]
            )){
                // Возвращаем всё что взяли на склад
                $this->returnToStore();
                $this->addError("Вы исчерпали месячный лимит заказов данного поощрения.");
                return false;
            }
        }

        // Проверяем суточные лимиты
        foreach($arSKUs as $nSkuNum=>$arSKU){
            if(!$sCustomNum && $failedLimit = $objCCatalogOffer->failedDailyLimit(
                $arSKU["SKU"]["OFFER"]["ID"],
                $arSKU["AMOUNT"]
            )){
                // Возвращаем всё что взяли на склад
                $this->returnToStore();
                $this->addError("Сегодня нельзя заказать такое количество
                товара. Уменьшите число заказываемых единиц или приходите завтра.");
                return false;
            }
        }

        // Проверяем дневные лимиты по тройкам и парковкам, посылаем в сад, если 
        // вышел хотя бы один
        foreach($arSKUs as $nSkuNum=>$arSKU){
            $sArtNumber = '';
            if(isset($arSKU["SKU"]["PRODUCT_PROPERTIES"]["ARTNUMBER"]))
                $sArtNumber = $arSKU["SKU"]["PRODUCT_PROPERTIES"]["ARTNUMBER"];
            
            
            if( $sArtNumber=='parking'){
                $objIntegration = new \Integration\CIntegration('PARKING');
            }
            // Если это тройка и дневной лимит вышел - показываем фигу
            if( $sArtNumber=='troyka'){
                $objIntegration = new \Integration\CIntegration("TROYKA");
            }
            if($sArtNumber=='parking' ||  $sArtNumber=='troyka'){
                // Удаляем повисшие блокировки
                $objIntegration->clearLocks();

                // Определяем вышел ли дневной лимит 
                $bIsLimited = $objIntegration->isLimited();
                if($bIsLimited){
                    // Возвращаем всё что взяли на склад
                    $this->returnToStore();
                    $this->addError("Дневной лимит заказа данного поощрения исчерпан.");
                    return false;
                }
                // Ставим блокировку
                if(!$nLockId = $objIntegration->setLock($this->getParam("UserId"))){
                    // Возвращаем всё что взяли на склад
                    $this->returnToStore();
                    $this->addError("Не удалось создать блокировку.");
                    return false;
                }
            }
        }


        $res = \CSaleDelivery::GetList(array(),array("ACTIVE"=>"Y"));
        if(!$delivery = $res->GetNext()){
            $this->addError("Нет активных служб доставки");
            return false;
        }

        // Проверяем сумму на счёте
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $objSSAGAccount = new \SSAG\CSSAGAccount('',\CUser::GetID());
        $arAccount = ["CURRENT_BUDGET"=>$objSSAGAccount->balance()];
        if(!$sCustomNum && $arAccount["CURRENT_BUDGET"]<$nTotalSum){
            $this->addError("Недостаточно баллов на счёте");
            // Возвращаем всё что взяли на склад
            $this->returnToStore();
            return false;
        }
        
        // Добавляем заказ 
        $sInitialStatusId = 'AA';
        
        $arFields = [];
        $arFields["LID"] = SITE_ID;
        $arFields["PERSON_TYPE_ID"] = 1;
        $arFields["STORE_ID"] = $arSKU["STORE_ID"];
        $arFields["PAYED"] = 'N';
        $arFields["CANCELED"] = "N";
        $arFields["STATUS_ID"] = $sInitialStatusId;
        $arFields["PRICE"] = $nTotalSum;
        $arFields["CURRENCY"] = "BAL";
        $arFields["USER_ID"] = $this->getParam("UserId");
        //$arFields["PAY_SYSTEM_ID"] = $paySystem["ID"];
        $arFields["PRICE_DELIVERY"] = 0;
        $arFields["DELIVERY_ID"] = $delivery["ID"];
        $arFields["DISCOUNT_VALUE"] = 0;
        $arFields["TAX_VALUE"] = 0;
        $arFields["USER_DESCRIPTION"] = "";
        $objCSaleOrder = new \CSaleOrder;
        if(!$nOrderId = $objCSaleOrder->Add($arFields)){
            $this->addError("Не удалось добавить заказ: ".$resCSaleOrder->LAST_ERROR);
            // Возвращаем всё что взяли на склад
            $this->returnToStore();
            return false;
        }
        
        $this->setParam("Id",$nOrderId);
        $this->setParam("Num",$sCustomNum?$sCustomNum:"Б-".$nOrderId);
        $sOrderNum = $this->getParam("Num");
        $this->setParam("StatusId",$sInitialStatusId);
        // Сохраняем параметры заказа
        $this->saveParams();
        // Крепим к нему корзину
        $this->linkBasket();
        
        // Обновляем свойства заказа из свойств товара (для поиска)
        $this->orderPropertiesUpdate();
        
        ///////////// Проводим работу по интеграции
        // Статус тройки
        // 0 - не заказывалась
        // 1 - успешный заказ
        // 2 - ошибочный заказ
        $stoykaStatus = 0;
        /////// Действия над картой-тройкой
        if($sArtNumber == "troyka"){
            $nTroykaNum = trim($_REQUEST["troyka"]);
            
            $objTroyka = new \Integration\CIntegrationTroyka($nTroykaNum);            
            if($objTroyka->error)$this->addError($objTroyka->error);

            // Запоминаем для заказа номер тройки
            $objTroyka->linkOrder($this->getParam('Num'));
            if($objTroyka->error)$this->addError($objTroyka->error);

            // Производим транзакцию в тройку
            $objTroyka->payment($this->getParam('Num'));
            if($objTroyka->errorNo){
                // Мапинг кодов ошибок шлюза в сообщения для посетителя
                $arErrors = $objTroyka->errorMapping();
                $this->addError(
                    isset($arErrors["messageText"])
                    ?
                    $arErrors["messageText"]
                    :
                    $objTroyka->error
                );
                // Сохраняем неуспешный статус
                $stoykaStatus = 2;
            }
            else{
                // Сохраняем успешный статус
                $stoykaStatus = 1;
            }

            // Запоминаем номер транзакции тройки при любом статусе
            $objTroyka->linkOrderTransact($this->getParam('Num'));
        }

        // Статус парковки
        // 0 - не заказывалась
        // 1 - Успешно
        // 2 - неудачно
        $sParkingStatus = 0;
        if($sArtNumber=='parking'){
            
            $objCUser = new \User\CUser;
            $objCUser->fetch("ID", $this->getParam("UserId"));
            $arUser = $objCUser->get();
            $objParking = new \Integration\CIntegrationParking(
                str_replace("u","",$arUser["LOGIN"])
            );

            // Производим транзакцию в парковку
            $objParking->payment($sOrderNum);
            if($objParking->getErrors()){
                
                // Мапинг кодов ошибок шлюза в сообщения для посетителя
                $this->addError($objParking->getErrors());
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

        
        // Если тройка провалилась - баллы не снимаем
        if($stoykaStatus == 2){
        }
        // Если парковка провалилась - баллы не снимаем
        elseif($sParkingStatus == 2){
        }
        // Для админа баллы не снимаем, ибо юниттест
        elseif($this->getParam("UserId")!=1){
            //////////// Снимает баллы
            if(!$bPointsSuccess = $objSSAGAccount->transaction(
                -$nTotalSum,
                 "Заказ Б-$nOrderId в магазине поощрений АГ"
            ))$this->addError($objSSAGAccount->error);

            ///////////
            if($bPointsSuccess)
                $objSSAGAccount->update();
        }
        
        // Если тойка провалилась - остатки возвращаем
        if($stoykaStatus == 2){
            // Снимаем блокировку
            $objIntegration->resetLock($nLockId);
            // Чистим зависшие блогировки
            $objIntegration->clearLocks();
            // Возврат остатков на склад
            $this->returnToStore();
        }
        // Если парковка провалилась - остатки возвращаем
        elseif($sParkingStatus == 2){
            // Снимаем блокировку
            $objIntegration->resetLock($nLockId);
            // Чистим зависшие блогировки
            $objIntegration->clearLocks();
            // Возврат остатков на склад
            $this->returnToStore();
        }
        // Если снялись баллы - остатки оставляем снятыми
        elseif($bPointsSuccess){
            // Отмечаем интеграции как выполненные
            if( $sArtNumber=='troyka' ||  $sArtNumber=='parking'){
                $objIntegration->doneLock($nLockId, $nOrderId) ;
                $objIntegration->clearLocks();
            }
            
        }
        elseif(!$bPointsSuccess){
            // Возврат остатков на склад если с оплатой вышла беда
            $this->returnToStore();
        }
        
        // Обновляем индексную таблицу
        $objCSync = new \Sync\CSync;
        $objCSync->syncUser($this->getParam("UserId"));
        $nOrderId = $this->getParam("Id");
        if($nOrderId)$objCSync->syncOrder($nOrderId);

        // Ставим статус заказ и отправляем соответствующее ЗНИ, в зависимости от результата заказа
        ///// Ставим в очередь на ЗНИ
        // Если тойка успешно заказалась - Выполнен
        if($stoykaStatus == 1)
            $this->setZNI('F','AA');
        // Если тойка провалилась - Аннулирован
        elseif($stoykaStatus == 2)
            $this->setZNI('AF','AA');
        // Если парковка успешно заказалась - Выполнен
        elseif($sParkingStatus == 1)
            $this->setZNI('F','AA');
        // Если парковка провалилась - Аннулирован
        elseif($sParkingStatus == 2)
            $this->setZNI('AF','AA');
        // Если не удалось снять баллы - аннулирован
        elseif(!$bPointsSuccess)
            $this->setZNI('AF','AA');
        // Во всех остальных случаях - В работее
        else
            $this->setZNI('N','AA');
        
        if(!$this->getErrors())return $nOrderId;
        
        return false;
    }


    /*
        Отправка запроса на изменение
    */
    function setZNI($sStatusId,$sOldStatusId){
        
        $this->saveProperty("CHANGE_REQUEST", $sStatusId);
        $nOrderId = $this->getParam("Id");

        if($sStatusId=='F'){
            $this->saveProperty("SHIPDATE", date("Y-m-d H:i:s"));
        }

    
        // Сохраняем запись в истории
        \CSaleOrderChange::AddRecord($nOrderId,
            $sStatusId?"ORDER_ZNI":"ORDER_ZNI_CHECK",
            ["STATUS_ID"=>$sStatusId,"OLD_STATUS_ID"=>$sOldStatusId],
            "ORDER"
        );
    }


    /**
        Обновление свойств у заказа из свойств товара
    */
    function orderPropertiesUpdate(){
        global $DB;
        
        $nOrderId = $this->getParam("Id");
        $objCSaleOrderPropsValue = new \CSaleOrderPropsValue;
        
        $arOrder = \CSaleOrder::GetList(
            array(),
            array(
                "ID"=>$nOrderId
            ),
            false,
            array(
                "nTopCount"=>1
            ),
            array("ID","USER_ID","USER_NAME","USER_LAST_NAME","DATE_INSERT") 
        )->Fetch();
        if(!$arOrder)return false;
    
        $arPropGroup = \CSaleOrderPropsGroup::GetList(
            array(),
            $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
            false,
            array("nTopCount"=>1)
        )->GetNext();
        $nPropGroup = $arPropGroup["ID"];
    
        $resPropValues = \CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            array(
                    "ORDER_ID"       => $arOrder["ID"],
                    "PERSON_TYPE_ID" => 1,
                    "PROPS_GROUP_ID" => $nPropGroup,
                ),
            false,
            false,
            array("ID","CODE","NAME")
        );
    
        $arOrder["PROPERTIES"] = array();
        $nextFlag = false;
        while($arProp = $resPropValues->GetNext()){
            $sQuery = "
                SELECT
                    `ID`,
                    `VALUE`
                FROM
                    `b_sale_order_props_value`
                WHERE
                    `ORDER_ID`='".$arOrder["ID"]."'
                    AND
                    `ORDER_PROPS_ID`='".$arProp["ID"]."'
                LIMIT 
                    1
            ";
            $arValue = $DB->Query($sQuery)->Fetch();
            /*
            Во имя оптимизации
            $arValue =  
                CSaleOrderPropsValue::GetList(
                    array(),
                    $arFilterProp = array(
                        "ORDER_ID"=>$arOrder["ID"],
                        "ORDER_PROPS_ID"=>$arProp["ID"]
                    ),
                    false,
                    array("nTopCount"=>1),
                    array("VALUE_ORIG","VALUE")
                )->GetNext();
            */
    
            if(is_array($arValue["VALUE_ORIG"]))
                $arValue = '';
            else
                $arValue = $arValue["VALUE"];
            if($arValue){
                //$nextFlag = true;
                //break;
            }
            $arOrder["PROPERTIES"][$arProp["CODE"]] = array (
                "PROPERTY_SETTINGS" =>  $arProp,
                "PROPERTY_VALUE"    =>  $arValue
            );
        }
        $arOrder["PROPERTIES"]["NAME_LAST_NAME"]["PROPERTY_VALUE"] = 
            $arOrder["USER_LAST_NAME"]." ".$arOrder["USER_NAME"];
        
        $tmp = explode(" ",$arOrder["DATE_INSERT"]);
    
        $arBasket = \CSaleBasket::GetList(
            array(),
            array("ORDER_ID"=>$arOrder["ID"]),
            false,
            array("nTopCount"=>1)
        )->GetNext();
    
        $arOffer = \CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arBasket["PRODUCT_ID"],
                "IBLOCK_ID" =>  $this->IBLOCKS["OFFER"]
            ),
            false,
            array("nTopCount"=>1),            
            array("ID","PROPERTY_CML2_LINK")
        )->GetNext();
    
        $arCatalog = \CIBlockElement::GetList(
            array(),
            array(
                "ID"        =>  $arOffer["PROPERTY_CML2_LINK_VALUE"],
                "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"]
            ),
            false,
            array("nTopCount"=>1),            
            array(
                "IBLOCK_SECTION_ID","NAME","DETAIL_PAGE_URL",
                "PROPERTY_DAYS_TO_EXPIRE","PROPERTY_MANUFACTURER_LINK"
            )
        )->GetNext();
    
        $arManufacturer = \CIBlockElement::GetList(
            array(),
            array("ID"=>$arCatalog["PROPERTY_MANUFACTURER_LINK_VALUE"]),
            false,
            array("nTopCount"=>1),
            array("ID","NAME")
        )->GetNext();
    
    
        $arOrder["PROPERTIES"]["PRODUCT_URL"]["PROPERTY_VALUE"] = 
            $arCatalog["DETAIL_PAGE_URL"];
        $arOrder["PROPERTIES"]["PRODUCT_NAME"]["PROPERTY_VALUE"] = 
            $arCatalog["NAME"];
        $arOrder["PROPERTIES"]["SECTION_ID"]["PROPERTY_VALUE"] = 
            $arCatalog["IBLOCK_SECTION_ID"];
        $arOrder["PROPERTIES"]["MANUFACTURER_ID"]["PROPERTY_VALUE"] = 
            $arManufacturer["ID"];
        $arOrder["PROPERTIES"]["MANUFACTURER_NAME"]["PROPERTY_VALUE"] = 
            $arManufacturer["NAME"];
    
        $arCategory = \CIBlockSection::GetList(
            array(),
            array(
                "ID"        =>  $arCatalog["IBLOCK_SECTION_ID"],
                "IBLOCK_ID" =>  $this->IBLOCKS["CATALOG"]
            ),
            false,
            array("NAME","SECTION_PAGE_URL"),
            array("nTopCount"=>1)            
        )->GetNext();
        $arOrder["PROPERTIES"]["SECTION_NAME"]["PROPERTY_VALUE"] =  
            $arCategory["NAME"];
        $arOrder["PROPERTIES"]["SECTION_URL"]["PROPERTY_VALUE"] =  
            $arCategory["SECTION_PAGE_URL"];
    
    
        $objCSaleOrderPropsValue = new \CSaleOrderPropsValue;
    //    $bDebug = true;
        foreach($arOrder["PROPERTIES"] as $sPropName=>$arPropValue)
            $this->saveProperty($sPropName, $arPropValue["PROPERTY_VALUE"]);
    
        return true;
    }

    /*
        Сохранение параметров заказа, если известен параметр Id
    */
    function saveParams(){
        $arFields = [];
        if($sVal = $this->getParam("UserId"))$arFields["USER_ID"] = $sVal;
        if($sVal = $this->getParam("StatusId"))$arFields["STATUS_ID"] = $sVal;
        if($sVal = $this->getParam("Num"))$arFields["ADDITIONAL_INFO"] = $sVal;
        if($sVal = $this->getParam("XML_ID"))$arFields["XML_ID"] = $sVal;
        if($sVal = $this->getParam("DateInsert"))$arFields["DATE_INSERT"] = $sVal;
        if($sVal = $this->getParam("DateUpdate"))$arFields["DATE_UPDATE"] = $sVal;
        $nOrderId = $this->getParam("Id");
        $objCSaleOrder = new \CSaleOrder;
        $objCSaleOrder->Update($nOrderId, $arFields);
        return true;
    }
    
    /*
        Сохранение свойств заказа, если известен параметр Id
    */
    function saveProperties(){
        foreach($this->arProps as $sPropName=>$sPropValue)
            $this->saveProperty($sPropName, $sPropValue);
    }
    
    /*
        Сохранение одного свойства заказа, если известен параметр Id
    */
    function saveProperty($sPropertyCode, $sPropValue){
        $nOrderId = $this->getParam("Id");
        $arPropGroup = \CSaleOrderPropsGroup::GetList(
            [],$arPropGroupFilter = ["NAME"=>"Индексы для фильтров"],
            false,["nTopCount"=>1])->GetNext();
        $nPropGroup = $arPropGroup["ID"];

        $arPropValue = \CSaleOrderProps::GetList(
            ["SORT" => "ASC"],
            [
                "ORDER_ID"       => $nOrderId,
                "PERSON_TYPE_ID" => 1,
                "PROPS_GROUP_ID" => $nPropGroup,
                "CODE"           => $sPropertyCode 
            ],
            false,false,["ID","CODE","NAME"]
        )->Fetch();

        $arFilter = [
            "ORDER_ID"      =>  $nOrderId,
            "ORDER_PROPS_ID"=>  $arPropValue["ID"],
            "CODE"          =>  $arPropValue["CODE"],
            "NAME"          =>  $arPropValue["NAME"]
        ];
        if(
            $arExistPropValue = 
            \CSaleOrderPropsValue::GetList([], $arFilter)->GetNext()
        ){
            $arFilter["VALUE"] = $sPropValue;
            if(!\CSaleOrderPropsValue::Update(
                $arExistPropValue["ID"],
                $arFilter 
            )){
                $this->addError("Ошибка обновления свойства заказа
                ".print_r($arFilter1));
                return false;
            }
        }
        elseif($sPropValue){
            $arFilter["VALUE"] = $sPropValue;
            if(!\CSaleOrderPropsValue::Add($arFilter)){
                $this->addError("Ошибка добавления свойства заказа
                ".print_r($arFilter,1));
                return false;
            }
        }
        return true;
    }

    /**
        Добавляем все SKU в корзину, а корзину к заказу
    */
    function linkBasket(){
        $CDB = new \DB\CDB;
        $arSKUs = $this->getSKUs();
        foreach($this->arSKUs as $arSKUs){
            $CDB->insert(
                \AGShop\CAGShop::t_sale_basket,
                [
                    "FUSER_ID"      =>$this->getParam("UserId"), 
                    "ORDER_ID"      =>$this->getParam("Id"), 
                    "PRODUCT_ID"    =>$arSKUs["SKU"]["OFFER"]["ID"], 
                    "QUANTITY"      =>$arSKUs["AMOUNT"], 
                    "NAME"          =>$arSKUs["SKU"]["OFFER"]["NAME"], 
                    "PRICE"         =>
                        isset($arSKUs["PRICE"]) && $arSKUs["PRICE"]
                        ?
                        $arSKUs["PRICE"]
                        :
                        $arSKUs["SKU"]["PRODUCT_PROPERTIES"]
                        ["MINIMUM_PRICE"], 
                    "DATE_UPDATE"   =>date("Y-m-d H:i:s"), 
                    "CURRENCY"      =>"BAL", 
                    "LID"           =>"s1", 
                    "MODULE"        =>"catalog", 
                    "CAN_BUY"       =>"Y", 
                    "DELAY"         =>"N"
                ]
            );
        }
        return true;
    }

    /**
        Получить заказ по его номеру
    */
    function getByNum($sNum){
        $CDB = new \DB\CDB;
        $arResult = $CDB->searchOne(\AGShop\CAGShop::t_sale_order,[
            "ADDITIONAL_INFO"=>$sNum
        ]);
        return $arResult;
    }

    /**
        Получить заказ по его ID
    */
    function getById($nId){
        $nId = intval($nId);
        $CDB = new \DB\CDB;
        $arResult = $CDB->searchOne(\AGShop\CAGShop::t_sale_order,[
            "ID"=>$nId
        ]);
        return $arResult;
    }


    /**
        Получение корзины по ID заказа
    */
    function getBasketById($nId){
        $nId = intval($nId);
        $CDB = new \DB\CDB;
        $sQuery = "
            SELECT
                `basket`.`PRODUCT_ID` as `OFFER_ID`,
                `basket`.`PRICE` as `PRICE`,
                `basket`.`NAME` as `NAME`,
                `basket`.`QUANTITY` as `QUANTITY`
                
            FROM
                `".\AGShop\CAGShop::t_sale_basket."` as `basket`
            WHERE
                `basket`.`ORDER_ID` = ".$nId."
        ";
        $arResult = $CDB->sqlSelect($sQuery);
        return $arResult;
    }

    /**
        Вернуть все позиции что зарезервировали на склад
    */
    function returnToStore(){
//        return false;
        $objCCatalogStore = new \Catalog\CCatalogStore;
        $arSKUs = $this->getSKUs();
        foreach($arSKUs as $arSKU)
            $objCCatalogStore->move(
                $arSKU["SKU"]["OFFER"]["ID"],
                $arSKU["STORE_ID"],
                $arSKU["AMOUNT"]
            );
    }

    function getPropertyByCode($sPropCode){
        $sPropCode = htmlspecialchars($sPropCode);
        if(!isset($this->arProps[$sPropCode])){
            $this->addError("Свойство $sPropCode не установлено");
            return false;
        }
        return $this->arProps[$sPropCode];
    }


    function setPropertyByCode($sPropCode, $sPropValue){
        $sPropName = htmlspecialchars($sPropCode);
        $objProp = new \Order\COrderProperty;
        if(!$objProp->existsByCode($sPropCode)){
            $this->addError("Несуществующее свойство заказа $sPropCode");
            return false;
        }
        $this->arProps[$sPropCode] = $sPropValue;
        return true;
    }

    function fetchProperty($sPropName){
        $objProp = \OrderPropery\COrderPropery;
        $objProp->fetch($sPropName);
    }
    
    /**
        Получение всех свойств заказа по его ID
    */
    function fetchAllProperties($nId){
        $nId = intval($nId);
        $CDB = new \DB\CDB;
        $sQuery = "
            SELECT
                `CODE`,`VALUE`
            FROM
                ".\AGShop\CAGShop::t_sale_order_props_value."
            WHERE
                `ORDER_ID`=$nId
        ";
        
        $arQuery = $CDB->sqlSelect($sQuery);
        $arResult = [];
        $this->arProps = [];
        foreach($arQuery as $arItem)
            $this->arProps[$arItem["CODE"]] = $arItem["VALUE"];
        return $arResult;
        
    }
    
    function getPropery($sPropName){
        if(!isset($this->arProps[$sPropName])){
            $this->addError("Неизвестное свойство заказа "
                .htmlspecialchars($sPropName));
            return false;
        }
        return $this->arProps[$sPropName];
    }
    
    function getAllProperties(){
        return $this->arProps;
    }

    function getOrderType(){
        return $this->sOrderType;
    }

    function setParam($sParamName, $paramValue){
        $sMethodName = "__setParam".$sParamName;
        if(!isset($this->arEnabledOrderParams[$sParamName])){
            $this->addError("Неизвестный параметр заказа $sParamName");
            return false;
        }
        elseif(!method_exists($this, $sMethodName)){
            $this->addError("Нет обработчика параметра $sParamName");
            return false;
        }
        
        if(!$this->$sMethodName($paramValue))return false;
        
        return true;
    }
    
    
    function getParam($sParamName){
        $sMethodName = "__getParam".$sParamName;
        if(!isset($this->arEnabledOrderParams[$sParamName])){
            $this->addError("Неизвестный параметр заказа $sParamName");
            return false;
        }
        elseif(!method_exists($this, $sMethodName)){
            $this->addError("Нет обработчика параметра $sParamName");
            return false;
        }
        
        return $this->$sMethodName();
    }
    
    function __setParamNum($sValue){
        if(!preg_match("#^(.*?)\-(\d+)$#", $sValue, $m)){
            $this->addError("Некорректный формат номера заказа $sValue");
            return false;
        }
        
        $this->sOrderType = $m[1];
        $this->arOrderParams["Num"] = $sValue;
        
        return true;
    }

    function __setParamUserId($sValue){
        $this->arOrderParams["UserId"] = $sValue;
        
        return true;
    }

    function __setParamId($sValue){
        $this->arOrderParams["Id"] = $sValue;
        
        return true;
    }

    function __setParamStatusId($sValue){
        $this->arOrderParams["StatusId"] = $sValue;
        
        return true;
    }

    function __setParamXML_ID($sValue){
        $this->arOrderParams["XML_ID"] = $sValue;
        
        return true;
    }


    function __setParamDateInsert($sValue){
        if(!$sDate = $this->getDateISO($sValue))return false;
        $this->arOrderParams["DateInsert"] = $sDate;
        return true;
    }

    function __setParamDateUpdate($sValue){
        if(!$sDate = $this->getDateISO($sValue))return false;
        $this->arOrderParams["DateUpdate"] = $sDate;
        return true;
    }

    function __getParamNum(){
        return $this->arOrderParams["Num"];
    }

    function __getParamId(){
        return $this->arOrderParams["Id"];
    }

    function __getParamUserId(){
        return $this->arOrderParams["UserId"];
    }

    function __getParamStatusId(){
        return $this->arOrderParams["StatusId"];
    }

    function __getParamXML_ID(){
        return $this->arOrderParams["XML_ID"];
    }


    function __getParamDateInsert(){
        return $this->arOrderParams["DateInsert"];
    }

    function __getParamDateUpdate(){
        return $this->arOrderParams["DateUpdate"];
    }
    
}
