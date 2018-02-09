<?php
namespace Auction;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogProduct.class.php");
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
            "CTIME"=>"Дата совершения ставки",
            "AMOUNT"=>"Количество единиц в ставке",
            "PRICE"=>"Цена за единицу",
        ]
        \endcode
    */
    function getActiveBet($nProductId, $nUserId){
        $CDB = new \DB\CDB;
        $arResult = $CDB->SearchOne("int_bets",[
            "USER_ID"=>intval($nUserId),
            "PRODUCT_ID"=>intval($nProductId)
        ],[
            "CTIME","AMOUNT","PRICE"
        ]);
        if(!$arResul)return [];
        
        $arResult = [
            "CTIME"=>date("d.m.Y H:i:s",MakeTimeStamp(
                $arResult["CTIME"],"YYYY-MM-DD HH:MI:SS"
            )),
            "AMOUNT"=>$arResult["AMOUNT"],
            "PRICE"=>round($arResult["AMOUNT"])
        ];

        return $arResult;
    }

    /**
        Сделать ставку

        @param $nOfferId - ID предложения по которому делаем ставку
        @param $nUserId - ID пользователя, который делает ставку
        @param $nPrice - цена предложения
        @param $nAmount - количество единиц в заявке

        @return ID записи ставки
    */
    function pushBet($nOfferId, $nUserId, $nPrice, $nAmount = 1){
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
