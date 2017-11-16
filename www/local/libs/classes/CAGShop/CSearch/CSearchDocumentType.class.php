<?php
namespace Search;
require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

use AGShop as AGShop;
use AGShop\DB as DB;

/**
    Типы документов
*/
class CSearchDocumentType extends \AGShop\CAGShop{

    private $arDocTypes = [
        "PRODUCT"=>1,
        "ARTICLE"=>2
    ];

    function __construct(){
    }

    /**
        Получение ID типа документа по его имени
    */
    function getId($sType){
        if(!trim($sType)){
            $this->addError("Document type is undefined");
            return false;
        }
        if(isset($this->arDocTypes[$sType]))return $this->arDocTypes[$sType];
        return false;
        
    }
    
    function getSearchableContent($nId, $sType='PRODUCT'){
        
    }
    
}

