<?php
namespace Auction;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogProduct.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogOffer.class.php");
require_once(realpath(__DIR__."/..")."/CCatalog/CCatalogStore.class.php");
require_once(realpath(__DIR__."/..")."/CCache/CCache.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");
require_once(realpath(__DIR__."/..")."/CUtils/CPagination.class.php");
require_once(realpath(__DIR__."/..")."/CSSAG/CSSAGAccount.class.php");

use AGShop;
use AGShop\Catalog as Catalog;
use AGShop\DB as DB;
use AGShop\Utils as Utils;
use AGShop\CCache as CCache;
use AGShop\SSAG as SSAG;

class CAuction extends \AGShop\CAGShop{
    
    function __construct(){
        parent::__construct();
    }

    /**
        Возвращает список статусов ставки

        @return 
        \code
        [
            "new"=>"Ставка сделана",
            "win"=>"Победитель",
            "lose"=>"Проигравший"
        ]
        \endcode
    */
    function getStatuses(){
        return [
            "new"   =>  "Ставка сделана",
            "error" =>  "Ошибка оплаты",
            "win"   =>  "Победитель",
            "lose"  =>  "Проигравший"
        ];
    }

    /**
        Возвращает список ставок по ID предложения и дате закрытия

        @param $nOfferId - ID торгового предложения
        @param $sOffDate - дата закрытия аукциона
    */
    function getAuctionBets($nOfferId, $sOffDate){

        $nOfferId = intval($nOfferId);

        $sQuery = "
            SELECT
                `bet`.`ID` as `BET_ID`,
                DATE_FORMAT(`bet`.`CTIME`,'%d.%m.%Y %H:%i:%s') as `CTIME`,
                DATE_FORMAT(`bet`.`OFF_TIME`,'%d.%m.%Y %H:%i:%s') as `OFF_DATE`,
                REPLACE(`user`.`LOGIN`,'u','') as `PHONE`,
                CONCAT(`user`.`LAST_NAME`,' ',`user`.`NAME`) as `FIO`,
                `bet`.`PRICE` as `PRICE`,
                `bet`.`AMOUNT` as `AMOUNT`,
                `bet`.`STORE_ID` as `STORE_ID`,
                `store`.`TITLE` as `STORE_TITLE`,
                `bet`.`STATUS` as `STATUS`,
                `store_product`.`AMOUNT` as `STORE_AMOUNT`,
                `product`.`NAME` as `PRODUCT_NAME`,
                `product`.`ID` as `PRODUCT_ID`
            FROM
                `".parent::t_bets."` as `bet`
                    LEFT JOIN
                `b_user` as `user`
                    ON
                    `bet`.`USER_ID`=`user`.`ID`
                    LEFT JOIN
                `".parent::t_catalog_store."` as `store`
                    ON
                    `store`.`ID`=`bet`.`STORE_ID`
                    LEFT JOIN
                `".parent::t_catalog_store_product."` as `store_product`
                    ON
                    `store`.`ID`=`store_product`.`STORE_ID`
                    AND
                    `store_product`.`PRODUCT_ID`=$nOfferId
                    LEFT JOIN
                `".parent::t_iblock_element."` as `product`
                    ON
                    `bet`.`OFFER_ID`=`product`.`ID`
            WHERE
                `OFFER_ID` = $nOfferId
            ORDER BY
                `bet`.`PRICE` DESC,
                `bet`.`CTIME` ASC
            
        ";

        $CDB = new \DB\CDB;
        $arRows = $CDB->sqlSelect($sQuery);

        $arResult = [];
        foreach($arRows as $arRow){
            if(!isset($arResult[$arRow["STORE_ID"]]))
                $arResult[$arRow["STORE_ID"]] = [
                    "PRODUCT"=>[
                        "NAME"=>$arRow["PRODUCT_NAME"],
                        "ID"=>$arRow["PRODUCT_ID"]
                    ],
                    "STORE"=>[
                        "TITLE" =>$arRow["STORE_TITLE"],
                        "AMOUNT"=>$arRow["STORE_AMOUNT"]
                    ],
                    "BETS" => []
                ];
            if(!isset($arResult[$arRow["STORE_ID"]]["BETS"][$arRow["BET_ID"]]))
                $arResult[$arRow["STORE_ID"]]["BETS"][$arRow["BET_ID"]] =
                $arRow;
        }

        // Вычисляем победителей
        foreach($arResult as $nStoreId=>$arStoreBets){
            $nStoreAmount = $arStoreBets["STORE"]["AMOUNT"];
            foreach($arStoreBets["BETS"] as $nBetId=>$arBet){
                $arResult[$nStoreId]["BETS"][$nBetId]["ODD"] = $nStoreAmount;
                if($arBet["AMOUNT"]<=$nStoreAmount){

                    $arResult[$nStoreId]["BETS"][$nBetId]["TRADE_STATUS"] =
                       'win';
                    $nStoreAmount-=$arBet["AMOUNT"];
                }
                else{
                    $arResult[$nStoreId]["BETS"][$nBetId]["TRADE_STATUS"] =
                    'lose';
                }
            }
        }

        return $arResult;
    }
   
    /**
        Возвращает список товаров, по которым делались аукционные ставки

        @param $arParams = 
        \code
        [
            "OFF_DATE"  =>  "Дата закрытия аукциона",
            "PRODUCT_ID"=>  "ID товарного предложения по котором аукцион",
            "STORE_ID   =>  "ID склада по которому проводился аукцион",
            "OFFSET"    =>  "Смещение страницы(default 1)",
            "ONPAGE"    =>  "Количество записей на страницу(default 30)",
        ]
        \endcode
        @return массив вида
        \code
        [
            "result"=>[
                "OFF_DATE"  =>  "Дата закрытия аукциона",
                "START_DATE"=>  "Дата начала торгов",
                "END_DATE"  =>  "Дата завершения торгов",
                "CURRENT"   =>  "Аукцион идёт(Y/N)",
                "FINISHED"  =>  "Аукцион завершен(Y/N)"
                "OFFER_ID"=>  "ID товарного предложения по котором аукцион",
                "PRODUCT_NAME"=>  "Название продукта
            ],
            "pages"=>[
                // Смещение => титл страницы
                "0"=>"1",
                "30"=>"2"
            ]
        ]
        \endcode
    */
    function getAuctionProducts($arParams = []){
        if(!intval($arParams["OFFSET"]))
            $arParams["OFFSET"] = 0;
        else 
            $arParams["OFFSET"] = intval($arParams["OFFSET"]);

        if(!intval($arParams["ONPAGE"]))
            $arParams["ONPAGE"] = 30;
        else
            $arParams["ONPAGE"] = intval($arParams["ONPAGE"]);

        if(isset($arParams["STORE_ID"]) && intval($arParams["STORE_ID"]))
            $arParams["STORE_ID"] = intval($arParams["STORE_ID"]);
        elseif(isset($arParams["STORE_ID"]) && !intval($arParams["STORE_ID"]))
            unset($arParams["STORE_ID"]);

        if(isset($arParams["PRODUCT_ID"]) && intval($arParams["PRODUCT_ID"]))
            $arParams["PRODUCT_ID"] = intval($arParams["PRODUCT_ID"]);
        elseif(isset($arParams["PRODUCT_ID"]) && !intval($arParams["PRODUCT_ID"]))
            unset($arParams["PRODUCT_ID"]);

        $sNowDate = date("Y-m-d H:i:s");
        $sSelectPart = "
            `offer_link`.`IBLOCK_ELEMENT_ID` as `OFFER_ID`,
            `product`.`NAME` as `PRODUCT_NAME`,
            DATE_FORMAT(`start_date`.`VALUE`,'%d.%m.%Y %H:%i') as `START_DATE`,
            DATE_FORMAT(`end_date`.`VALUE`,'%d.%m.%Y %H:%i')  as `END_DATE`,
            DATE_FORMAT(`bet`.`OFF_TIME`,'%d.%m.%Y %H:%i') as `OFF_DATE`,
            IF(`start_date`.`VALUE`<='$sNowDate' AND
            `end_date`.`VALUE`>='$sNowDate','Y','N') as `CURRENT`,
            IF(`end_date`.`VALUE`<='$sNowDate','Y','N') as `FINESHED`
        ";

        $sFromPart = "
            `".parent::t_bets."` as `bet`
                LEFT JOIN
            `".parent::t_iblock_element_property."` as `offer_link`
                ON
                `offer_link`.`IBLOCK_PROPERTY_ID`=".$this->PROPERTIES["CML2_LINK"]."
                AND `offer_link`.`IBLOCK_ELEMENT_ID`=`bet`.`OFFER_ID`
                LEFT JOIN
            `".parent::t_iblock_element."` as `product`
                ON
                `product`.`IBLOCK_ID`=".$this->IBLOCKS["CATALOG"]."
                AND `offer_link`.`VALUE_NUM`=`product`.`ID`
                LEFT JOIN
            `".parent::t_iblock_element_property."` as `start_date`
                ON
                `start_date`.`IBLOCK_PROPERTY_ID`=".
                    $this->PROPERTIES["AUCTION_START_DATE_PROPERTY_ID"]
                    ."
                AND `start_date`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
                LEFT JOIN
            `".parent::t_iblock_element_property."` as `end_date`
                ON
                `end_date`.`IBLOCK_PROPERTY_ID`=".
                    $this->PROPERTIES["AUCTION_END_DATE_PROPERTY_ID"]
                    ."
                AND `end_date`.`IBLOCK_ELEMENT_ID`=`product`.`ID`
        ";

        $sWherePart = "1";

        $nOffset = ($arParams["PAGE"]-1)*$arParams["ONPAGE"];
        $sLimitPart = $arParams["OFFSET"].",".$arParams["ONPAGE"];

        $sQuery = "
            SELECT
                $sSelectPart
            FROM
                $sFromPart
            WHERE
                $sWherePart
            GROUP BY
                `product`.`ID`,
                `bet`.`OFF_TIME`
            LIMIT
                $sLimitPart
        ";

        $CDB = new \DB\CDB;

        $arRows = $CDB->sqlSelect($sQuery);

        $sQuery = "
            SELECT
                COUNT(*) as `count`
            FROM
                $sFromPart
            WHERE
                $sWherePart
            GROUP BY
                `product`.`ID`,
                `bet`.`OFF_TIME`
        ";
        
        $arTotals = $CDB->sqlSelect($sQuery);

        $nTotal = $arTotals['count'];
        $arPages = \Utils\CPagination::getPages(
            $nTotal,
            $arParams["OFFSET"],
            $arParams["ONPAGE"]
        );
       
        return [
            "result"=>$arRows,
            "pages"=>$arPages
        ];
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
        !!! Переписать
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
        !!!! переписать
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
                "IS_FINISHED"=>
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
