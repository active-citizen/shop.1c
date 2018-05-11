<?php

namespace app\shop\catalog;

use app\shop\CAGShop;

class CCatalogStore extends CAGShop
{

    private $arStoreInfo = [];

    function __construct()
    {
        parent::__construct();
        \CModule::IncludeModule('sale');
    }

    function getAllActive()
    {
        $CDB = new \DB\CDB;
        return $CDB->searchAll(\AGShop\CAGShop::t_catalog_store);
    }

    function getAnyExists()
    {
        $CDB = new \DB\CDB;

        $sQuery = "
                SELECT
                    `PRODUCT_ID`,
                    `AMOUNT`,
                    `STORE_ID`
                FROM
                    `" . \AGShop\CAGShop::t_catalog_store_product . "`
                WHERE
                    `AMOUNT`>0
                ORDER BY
                    `AMOUNT` DESC
            ";
        $arResult = $CDB->sqlSelect($sQuery);
        return array_pop($arResult);
    }

    function fetch($nId)
    {
        $nId = intval($nId);

        $objCache = new \Cache\CCache("storeInfo", $nId);
        if ($sCacheData = $objCache->get()) {
            $this->arStoreInfo = $sCacheData;
            return true;
        }

        $CDB = new \DB\CDB;
        $arResult = $CDB->searchOne(\AGShop\CAGShop::t_catalog_store, [
            "ID" => $nId
        ]);
        if (!$arResult) {
            return false;
        }
        $this->arStoreInfo = $arResult;
        $objCache->set($arResult);
        return true;
    }

    function get()
    {
        return $this->arStoreInfo;
    }

    function getById($nId)
    {
        $this->fetch($nId);
        return $this->arStoreInfo;
    }

    function getTitleById($nId)
    {
        $this->fetch($nId);
        return $this->arStoreInfo["TITLE"];
    }

    /**
     * Получение количества товара на нужном складе
     */
    function getProductAmount($nProductId, $nStoreId)
    {
        $CDB = new \DB\CDB;
        $arResult = $CDB->searchOne(
            \AGShop\CAGShop::t_catalog_store_product, [
            "PRODUCT_ID" => $nProductId,
            "STORE_ID" => $nStoreId
        ], ["AMOUNT"]
        );
        $fd = fopen(
            $_SERVER["DOCUMENT_ROOT"] . "/../logs/store" . date("Y-m-d") . ".txt", "a"
        );
        global $USER;
        $nUserId = $USER->GetID();
        fwrite($fd,
            "get  " . date("Y-m-d H:i:s") . "\t$nUserId\t$nProductId\t$nStoreId\t" . $arResult["AMOUNT"] . "\n");
        fclose($fd);
        return $arResult["AMOUNT"];
    }

    /*
        Движение продукта на складе
    */
    function move($nProductId, $nStoreId, $nAmount)
    {
        $nProductId = intval($nProductId);
        $nStoreId = intval($nStoreId);
        $nAmount = intval($nAmount);
        // Запрет на возврат товара
        //if($nAmount>=0)return false;

        if (!$nAmount) {
            $this->addError("Не указано число изымаемого товара");
            return false;
        }
        if (!$nProductId) {
            $this->addError("Не указан продукт для движения по складу");
            return false;
        }
        if (!$nStoreId) {
            $this->addError("Не указан склад для движения по складу");
            return false;
        }
        $CDB = new \DB\CDB;
        $sQuery = "
                UPDATE
                    `" . \AGShop\CAGShop::t_catalog_store_product . "`
                SET 
                    `AMOUNT`=`AMOUNT`+$nAmount
                WHERE
                    `PRODUCT_ID` = $nProductId
                    AND `STORE_ID` = $nStoreId
                LIMIT
                    1
            ";
        $fd = fopen(
            $_SERVER["DOCUMENT_ROOT"] . "/../logs/store" . date("Y-m-d") . ".txt", "a"
        );
        global $USER;
        $nUserId = $USER->GetID();
        fwrite($fd, "move " . date("Y-m-d H:i:s") . "\t$nUserId\t$nProductId\t$nStoreId\t$nAmount\n");
        fclose($fd);
        $CDB->sqlQuery($sQuery);
        return true;
    }

    /**
     * Наличие торгового предложения на складах
     */
    function exists($nProductId)
    {
        $nProductId = intval($nProductId);
        if (!$nProductId) {
            $this->addError("Не указан продукт для отображения наличия на складах");
            return false;
        }
        $arStores = [];
        $CDB = new \DB\CDB;
        $sQuery = "
                SELECT
                    `store_product`.`STORE_ID`,
                    `store_product`.`AMOUNT`
                FROM
                    `" . \AGShop\CAGShop::t_catalog_store_product . "` as `store_product`
                WHERE
                    `store_product`.`PRODUCT_ID`=" . $nProductId . "
            ";
        $arResult = $CDB->sqlSelect($sQuery);
        foreach ($arResult as $arItem) {
            $arStores[$arItem["STORE_ID"]] = $arItem["AMOUNT"];
        }

        return $arStores;
    }

    /**
     * Получение списка сладов для отображения на сайте
     *
     * @param $bWithoutAddress - выводить без складов с пустым адресом
     */
    function getForSite($bWithoutAddress = true)
    {
        $objCache = new \Cache\CCache("Allstores", 0);
        if ($sCacheData = $objCache->get()) {
            return $sCacheData;
        }

        \CModule::IncludeModule('catalog');
        $arFilter = [];
        if ($bWithoutAddress) {
            $arFilter["!ADDRESS"] = '';
        }

        $resStores = \CCatalogStore::GetList(
            array("TITLE" => "asc"),
            $arFilter,
            false,
            false,
            array("TITLE", "ADDRESS", "ID", "PHONE", "SCHEDULE", "EMAIL", "DESCRIPTION")
        );

        $arResult["stores"] = array();
        while ($arStore = $resStores->GetNext()) {
            // Вычисляем остатки на складе
            //    $res = CCatalogStoreProduct::GetList(array(),array("STORE_ID"=>$arStore["ID"]));
            //    if(!$res->result->num_rows)continue;
            $arResult["stores"][] = $arStore;
        }
        $objCache->set($arResult);
        return $arResult;

    }

}
