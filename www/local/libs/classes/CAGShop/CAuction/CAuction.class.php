<?php
namespace Auction;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogProduct.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogOffer.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogStore.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop;
use AGShop\Catalog as Catalog;
use AGShop\DB as DB;
use AGShop\CCache as CCache;

class CAuction extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
    }

    
    /**
        Получение последней активной ставки

        @param $nProductId - ID товара по которому ищем ставку
        @param $nUserId - ID пользователя, который ищем ставку

        @return массив параметров ставки
        \code
        [
            "ID"=>"ID ставки(число)"
            "CTIME"=>"Дата совершения ставки",
            "AMOUNT"=>"Количество единиц в ставке",
            "PRICE"=>"Цена за единицу",
            "STORE"=>"Информация о складе",
            "STATUS"=>"Статус"
        ]
        \endcode
    */
    function getActiveBet($nOfferId, $nUserId){
        if(!$nUserId)return $this->addError("Не указан пользователь");
        if(!$nOfferId)return $this->addError("Не указан ID предложения");
        $CDB = new \DB\CDB;
        $arResult = $CDB->SearchOne("int_bets",[
            "USER_ID"=>intval($nUserId),
            "OFFER_ID"=>intval($nOfferId)
        ],[
            "ID","CTIME","AMOUNT","PRICE","STORE_ID","STATUS"
        ]);
        if(!$arResult)return [];
        $objStore = new \Catalog\CCatalogStore;
        
        $arResult = [
            "ID"=>$arResult["ID"],
            "CTIME"=>date("d.m.Y H:i:s",MakeTimeStamp(
                $arResult["CTIME"],"YYYY-MM-DD HH:MI:SS"
            )),
            "AMOUNT"=>$arResult["AMOUNT"],
            "STATUS"=>$arResult["STATUS"],
            "STORE_ID"=>$arResult["STORE_ID"],
            "PRICE"=>round($arResult["PRICE"]),
            "STORE"=>$objStore->getById($arResult["STORE_ID"])
        ];

        return $arResult;
    }

    /**
        Сделать ставку

        @param $nOfferId - ID предложения по которому делаем ставку
        @param $nUserId - ID пользователя, который делает ставку
        @param $nPrice - цена предложения
        @param $nStoreId - ID склада
        @param $nAmount - количество единиц в заявке (по умолчанию=1)

        @return ID записи ставки
    */
    function pushBet($nOfferId, $nUserId, $nPrice, $nStoreId, $nAmount = 1){
        if(!$nOfferId = intval($nOfferId))
            return $this->addError('Не указан ID предложения');
        if(!$nUserId = intval($nUserId))
            return $this->addError('Не указан пользователь');
        if(!$nPrice = intval($nPrice))
            return $this->addError('Не указана цена');
        if(!$nAmount = intval($nAmount))
            return $this->addError('Не указано количество');
        if(!$nStoreId = intval($nStoreId))
            return $this->addError('Не указан склад');
        $nTotalSum = $nAmount*$nPrice;

        // Проверяем сумму на счёте
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $arAccount = \CSaleUserAccount::GetByUserID($nUserId, 'BAL');
        if($arAccount["CURRENT_BUDGET"]<$nTotalSum)
            return $this->addError("Недостаточно баллов на счёте");

        // Проверяем, не делал ли человек ставки по этому аукциону
        // FIXME
        if($this->getActiveBet($nOfferId, $nUserId)){
            return $this->addError('Пользователь уже делал ставку по аукциону');
        }

        $CDB = new \DB\CDB;

        // Делаем ставку
        if(!$nId = $CDB->insert("int_bets",[
            "CTIME"     =>  date("Y-m-d H:i:s"),
            "USER_ID"   =>  $nUserId,
            "OFFER_ID"  =>  $nOfferId,
            "STORE_ID"  =>  $nStoreId,
            "AMOUNT"    =>  $nAmount,
            "PRICE"     =>  $nPrice
        ])){
            return false;
        }

        // Узнаём что за товар
        $objOffer = new \Catalog\CCatalogOffer;
        $arOffer = $objOffer->getById($nOfferId);

        // Пытаемся снять баллы
        require_once(
            $_SERVER["DOCUMENT_ROOT"]
                ."/local/libs/rus.lib.php"
        );
        require_once(
            $_SERVER["DOCUMENT_ROOT"]
                ."/.integration/classes/order.class.php"
        );
        $obOrder = new \bxOrder();
        if(!$bPointsSuccess = $obOrder->addEMPPoints(
            -$nTotalSum,
            "Оплата ставки на аукционе. "
            ."Поощрение '".$arOffer["MAIN"]["NAME"]."'. "
            ."Количество ".$nAmount.". "
            ."Предложенная цена ".$nPrice." ".get_points($nPrice)
        ))$this->addError($obOrder->error);

        ///////////
        if($bPointsSuccess){
            $CDB->update("b_sale_user_account",[
                "USER_ID"=>$this->$nUserId,
                "CURRENCY"=>'BAL'
            ],[
                "CURRENT_BUDGET"=>$arAccount["CURRENT_BUDGET"]-$nTotalSum
            ]);
        }
        else{
        // Переводим ставку в статус "ошибка", если проблема с оплатой
            $this->setBetStatus($nId,'error');
            return $this->addError('Ошибка оплаты ставки');
        }

        return true;
    }

    /**
        Установка статуса ставки
        @param $nId - ID ставки
        @param $sStatus - новый статус ставки
    */
    function setBetStatus($nId,$sStatus){
        $CDB = new \DB\CDB;
        $CDB->update("int_bets",["ID"=>$nId],["STATUS"=>$sStatus]);
    }

    /**
        Удаление ставки

        @param $nId - ID ставки
    */
    function removeBet($nId){
        $CDB = new \DB\CDB;
        $CDB->delete("int_bets",["ID"=>$nId]);
    }

    /**
        Является ли товар аукционом

        @param $nProductId - ID продукта
        @return false если товар не является аукционом или, если это аукцион,
        массив вида 
        \code
        [
            "START_DATE"=>"Дата начала (дата)",
            "END_DATE"=>"Дата завершения (дата)",
            "START_PRICE"=>"Стартовая цена (число)",
            "IS_CURRENT"=>"Проходит ли аукцион в данный момент (true/false)",
            "IS_FINISHED"=>"Завершен ли аукцион в данный момент (true/false)",
        ]
        \endcode
    */
    function isAuction($nProductId){
        if(!$nProductId = intval($nProductId))
            return $this->addError("Не указан ID продукта");

        $objCache = new \Cache\CCache("isAuction",$nProductId,1);
        if($sCacheData = $objCache->get()){
            return $sCacheData;
        }
        
        $arResult = true;

        $objCatalog = new \Catalog\CCatalogProduct;
        $arProperties = $objCatalog->getProperties($nProductId);
        $arEnum = \CIBlockPropertyEnum::GetByID($arProperties["AUCTION_IS"]);

        if($arEnum["VALUE"]!='да')$arResult = false;
        if($arResult){
            $nStartTimestamp = MakeTimeStamp(
                $arProperties["AUCTION_START_DATE"],
                "YYYY-MM-DD HH:MI:SS"
            );
            $nStopTimestamp = MakeTimeStamp(
                $arProperties["AUCTION_STOP_DATE"],
                "YYYY-MM-DD HH:MI:SS"
            );
            $nNowTimestamp = time(); 
            $arResult = [
                "START_DATE"=>date("d.m.Y H:i:s",$nStartTimestamp),
                "END_DATE"=>date("d.m.Y H:i:s",$nStopTimestamp),
                "START_PRICE"=>$arProperties["MINIMUM_PRICE"],
                "IS_CURRENT"=>
                    $nStartTimestamp <= $nNowTimestamp
                    &&
                    $nStopTimestamp >= $nNowTimestamp
                    ?
                    true
                    :
                    false,
                "IS_FINISHES"=>
                    $nStopTimestamp <= $nNowTimestamp
                    ?
                    true
                    :
                    false,
            ]; 
        }
        $objCache->set($arResult);
        return $arResult;

    }
    
}
