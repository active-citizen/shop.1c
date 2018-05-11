<?php

namespace app\shop\catalog;

use app\shop\CAGShop;

class CCatalogWishes extends CAGShop
{

    function __construct()
    {
        parent::__construct();
        \CModule::IncludeModule('iblock');
    }

    /**
     * Получение чмсла желающих для товара с указанным ID
     * @param $nId - ID элемента каталога
     */
    function getCountByCatalogId($nId)
    {
        $objCache = new \Cache\CCache("card_wishes", $nId);
        if ($sCacheData = $objCache->get()) {
            return $sCacheData;
        }

        $res1 = \CIBlockElement::GetList([], $arFilter = [
            "IBLOCK_ID" => $this->IBLOCKS["WISHES"],
            "PROPERTY_WISH_PRODUCT" => $nId
        ], false, false);
        $arResult = $res1->SelectedRowsCount();
        $objCache->set($arResult);

        return $arResult;
    }

    /**
     * Является ли указанный товар желаемым для указанного пользователя
     * @param $nCatalogId - ID элемента каталога
     * @param $nUserId    - ID элемента каталога
     */
    function isWished($nCatalogId, $nUserId)
    {
        $res1 = \CIBlockElement::GetList([], $arFilter = [
            "IBLOCK_ID" => $this->IBLOCKS["WISHES"],
            "PROPERTY_WISH_USER" => $nUserId,
            "PROPERTY_WISH_PRODUCT" => $nCatalogId
        ], false, array("nTopCount" => 1));
        return $res1->SelectedRowsCount();
    }

}
