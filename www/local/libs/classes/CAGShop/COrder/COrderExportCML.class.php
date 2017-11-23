<?php
namespace Order;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogOffer.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogStore.class.php");
require_once(realpath(__DIR__."/..")."/CUser/CUser.class.php");
require_once(realpath(__DIR__)."/COrderProperty.class.php");
require_once(realpath(__DIR__)."/COrderStatus.class.php");
require_once(realpath(__DIR__)."/COrder.class.php");

use AGShop;
use AGShop\DB as DB;
use AGShop\Order as Order;
use AGShop\User as User;
use AGShop\Catalog as Catalog;

if(!defined("QUERY_LOCK_FILE"))
    define(
        "QUERY_LOCK_FILE",
        $sFilename = realpath($_SERVER["DOCUMENT_ROOT"]."/..")."/tmp/query.lock"
    );

if(!defined("ORDER_LOCK_FILE"))
    define(
        "ORDER_LOCK_FILE",
        $sFilename = realpath($_SERVER["DOCUMENT_ROOT"]."/..")."/tmp/order.lock"
    );

class COrderExportCML extends \AGShop\CAGShop{
    
    private $nQuant = ORDER_EXPORT_QUANT;
    
    function __construct(){
        parent::__construct();
    }
    
    function getLastZNI($sSessionId = '', $nLockSeconds = ''){
        if(!$sSessionId){
            $this->addError("Не указана ID сессии");
            return false;
        }
            
        // Если блокировку поставили и она не протухла - отваливаемся
        // Иначе ставим свою и выдаём обмен
        if($nWaitTime = $this->orderQueryIsLocked($nLockSeconds)){
            $this->addError("Failed : query is locked. Wait for $nWaitTime seconds");
            return false;
        }
        else{
            $this->orderQuerySetLock();
        }

        $CDB = new \DB\CDB;
        $objCOrderProperty = new \Order\COrderProperty;
        $objCOrderProperty->fetchByCode("CHANGE_REQUEST");
        $arProperty = $objCOrderProperty->get();

        
        $sQuery = "
            SELECT
                `order_prop`.`ORDER_ID` as `ORDER_ID`,
                `order_prop`.`VALUE`    as `ZNI`
            FROM 
                `".\AGShop\CAGShop::t_sale_order_props_value."` as `order_prop`
            WHERE
                `order_prop`.`ORDER_PROPS_ID` = ".$arProperty["ID"]."
                AND
                `order_prop`.`VALUE`!=''
            LIMIT
                ".$this->nQuant."
            
        ";
        $arOrdersZNI = $CDB->sqlSelect($sQuery);
        
        $objCUser = new \User\CUser;
        $objCOrder = new \Order\COrder;
        $objCOrderStatus = new \Order\COrderStatus;
        $objCCatalogOffer = new \Catalog\CCatalogOffer;
        $objCCatalogStore = new \Catalog\CCatalogStore;
        
        // Получаем информацию по каждому продукту заказа
        foreach($arOrdersZNI as $sKey=>$arOrderZNIItem){
            $arOrdersZNI[$sKey]["ORDER"] =
                $objCOrder->getById($arOrderZNIItem["ORDER_ID"]);

            $objCOrder->fetchAllProperties($arOrderZNIItem["ORDER_ID"]);
            $arOrdersZNI[$sKey]["ORDER_PROPERTIES"] =
                $objCOrder->getAllProperties();

            
            $objCUser->fetch("ID", $arOrdersZNI[$sKey]["ORDER"]["USER_ID"]);
            $arOrdersZNI[$sKey]["USER"] = $objCUser->get();
            
            $arOrdersZNI[$sKey]["BASKET"] =
                $objCOrder->getBasketById($arOrderZNIItem["ORDER_ID"]);
            
            foreach($arOrdersZNI[$sKey]["BASKET"] as $nBasketKey=>$arBasketItem){
                $arOrdersZNI[$sKey]["BASKET"][$nBasketKey]["OFFER"] = 
                    $objCCatalogOffer->getById($arBasketItem["OFFER_ID"]);
            }
                
        }
        
        $arResult = [];
        foreach($arOrdersZNI as $arOrderZNI){
            // Отмечаем заказ как "отданный в рамках сессии обмена 
            $this->orderSetSessionId($arOrderZNI["ORDER_ID"],$sSessionId);
            $arOrder = [];
            $arOrder["Ид"] = $arOrderZNI["ORDER"]["XML_ID"];
            $arOrder["Номер"] = $arOrderZNI["ORDER"]["ADDITIONAL_INFO"];
            $arOrder["Дата"] = $arOrderZNI["ORDER"]["DATE_INSERT"];
            $arOrder["ДатаИзменения"] = $this->getDateISO($arOrderZNI["ORDER"]["DATE_UPDATE"]);
            $arOrder["Время"] = $this->getTime($arOrderZNI["ORDER"]["DATE_INSERT"]);
            
            if($objCOrderStatus->fetch("ID", $arOrderZNI["ORDER"]["STATUS_ID"])){
                $arStatus = $objCOrderStatus->get();
                $arOrder["СостояниеЗаказа"] = $arStatus["NAME"];
            }
            
            if($objCOrderStatus->fetch("ID", $arOrderZNI["ZNI"])){
                $arStatusZNI = $objCOrderStatus->get();
                $arOrder["ЗНИ"] = $arStatusZNI["NAME"];
            }
            
            
            if(
                isset($arOrderZNI["ORDER_PROPERTIES"]["TROIKA"]) && 
                $arOrderZNI["ORDER_PROPERTIES"]["TROIKA"]
            )$arOrder["НомерТройки"] = $arOrderZNI["ORDER_PROPERTIES"]["TROIKA"];
            
            if(
                isset($arOrderZNI["ORDER_PROPERTIES"]["TROIKA_TRANSACT_ID"]) && 
                $arOrderZNI["ORDER_PROPERTIES"]["TROIKA_TRANSACT_ID"]
            )$arOrder["НомерОперации"] = $arOrderZNI["ORDER_PROPERTIES"]["TROIKA_TRANSACT_ID"];
            elseif(
                isset($arOrderZNI["ORDER_PROPERTIES"]["PARKING_TRANSACT_ID"]) && 
                $arOrderZNI["ORDER_PROPERTIES"]["PARKING_TRANSACT_ID"]
            )$arOrder["НомерОперации"] = $arOrderZNI["ORDER_PROPERTIES"]["PARKING_TRANSACT_ID"];
            
            if(
                isset($arOrderZNI["ORDER_PROPERTIES"]["CLOSE_DATE"]) && 
                $arOrderZNI["ORDER_PROPERTIES"]["CLOSE_DATE"]
            )$arOrder["ДатаИстеченияБронирования"] = $arOrderZNI["ORDER_PROPERTIES"]["CLOSE_DATE"];

            $arOrder["ЭлектроннаяПочта"] = $arOrderZNI["USER"]["EMAIL"];
            $arOrder["Клиент"] = $arOrderZNI["USER"]["LAST_NAME"]." ".$arOrderZNI["USER"]["NAME"];
            $arOrder["Фамилия"] = $arOrderZNI["USER"]["LAST_NAME"];
            $arOrder["Имя"] = $arOrderZNI["USER"]["NAME"];
            $arOrder["Телефон"] = str_replace("u","",$arOrderZNI["USER"]["LOGIN"]);
            
            if($objCCatalogStore->fetch($arOrderZNI["ORDER"]["STORE_ID"])){
                $arStore = $objCCatalogStore->get();
                $arOrder["Склад"] = $arStore["XML_ID"];
            }

            $arOrder["Товары"] = [];
            foreach($arOrderZNI["BASKET"] as $arBasket){
                $arProduct = [];
                $arProduct["Количество"] = $arBasket["QUANTITY"];
                $arChars = [];
                foreach($arBasket["OFFER"]["PROPERTIES"] as $sPropCode=>$sPropValue){
                    if(
                        !preg_match("#^PROP1C_.*#",$sPropCode) 
                        || 
                        !$sPropValue
                    )continue;
                    $arChars[] = [
                        "Наименование"  =>  $sPropCode,
                        "Значение"      =>  $sPropValue
                    ];
                }
                $arProduct["ХарактеристикиТовара"] = $arChars;
                    
                $arProduct["Ид"] = $arBasket["OFFER"]["MAIN"]["XML_ID"];
                $arProduct["Наименование"] = $arBasket["OFFER"]["MAIN"]["NAME"];
                $arProduct["Единица"] = $arBasket["OFFER"]["PRODUCT_PROPERTIES"]["QUANT"];
                $arProduct["Артикул"] = $arBasket["OFFER"]["PRODUCT_PROPERTIES"]["ARTNUMBER"];
                $arProduct["ЦенаЗаЕдиницу"] = $arBasket["PRICE"];
                
                $arOrder["Товары"][] = $arProduct;
            }
            
            
            $arResult[] = $arOrder;
        }
        
//        print_r($arOrdersZNI);
//        print_r($arResult);
        return $arResult;
    }

    /*
        Проверка блокировки выгрузки заказов в 1С
    */
    function orderQueryIsLocked(
        $lockTime = 30  // Время жизни блокировки
    ){
        if(!file_exists(QUERY_LOCK_FILE))return false;
        $arStat = stat(QUERY_LOCK_FILE);
        if(
            isset($arStat["mtime"])
            &&
            $arStat["mtime"]+$lockTime<time()
        ){
            return false;
        }
        elseif(!isset($arStat)){
            return false;
        }
        return (($arStat["mtime"]+$lockTime)-time());
    }
    
    /**
        Установка блокировки выгрузки заказов в 1С
    */
    function orderQuerySetLock(){
        $fd = fopen(QUERY_LOCK_FILE,"w");
        fwrite($fd, print_r($_SERVER, 1));
        fclose($fd);
        return true;
    }
    
    
    /**
        Сброс блокировки выгрузки заказов в 1С
    */
    function orderQueryResetLock(){
        //unlink(QUERY_LOCK_FILE);
        return true;
    }
    
    /*
        Установка сесси обмена
    */
    function orderSetSessionId($nOrderId,$sSessionId){
        
        $objCOrder = new \Order\COrder;
        $objCOrder->saveProperty("SESSION_ID", $sStatusId);
    }
    
}
