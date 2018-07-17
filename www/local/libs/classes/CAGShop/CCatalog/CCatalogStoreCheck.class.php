<?php
namespace Catalog;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");

use AGShop;

class CCatalogStoreCheck extends \AGShop\CAGShop{

    /**
        Проверка перед движением по складу
        @param $nAmount - количество
        @param $nProductId - ID торгового предложения
        @param $nStoreId - ID склада
        @return true, если движение возможно
    */
    function checkBeforeMove($nAmount, $nProductId, $nStoreId){
        if(!$nAmount){
            return $this->addError("Не указано число изымаемого товара");
        }
        if(!$nProductId){
            return $this->addError("Не указан продукт для движения по складу");
        }
        if(!$nStoreId){
            return $this->addError("Не указан склад для движения по складу");
        }
        return true;
    }
}
