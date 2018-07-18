<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");

use AGShop;

class CCatalogWishCheck extends \AGShop\CAGShop{

    /**
        Проверка возможности пожелать продукт
        @param $nAmount - количество
        @param $nUserId - ID пользователя
        @return true в случае успеха
    */
    function checkBeforeWish($nProductId, $nUserId){
        if(!$nProductId)return $this->addError("Не указан ID товара");
        if(!$nUserId)return $this->addError("Не указан ID пользователя");
        
        if(!$arProduct = \CIBlockElement::GetList([],[
                "ID"=>$nProductId,
                "IBLOCK_ID"=>$this->IBLOCKS["CATALOG"]
            ]
            ,false,["nTopCount"=>1],["ID"]
        )->Fetch())
            return $this->addError("Товар с ID=$productId не существует");
        
        return true;
    }
}
