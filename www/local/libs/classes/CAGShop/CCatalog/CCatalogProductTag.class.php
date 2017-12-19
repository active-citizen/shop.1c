<?php
    namespace Catalog;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    require_once(realpath(__DIR__."/..")."/CDB/CDB.class.php");

    use AGShop;
    use AGShop\DB as DB;
    
    class CCatalogProductTag extends \AGShop\CAGShop{
        
        var $nProductPropertyId = '';
        
        function __construct($nProductPropertyId){
            parent::__construct();
            $this->nProductPropertyId = $nProductPropertyId;
            \CModule::IncludeModule('iblock');
        }
        
        /**
            Получение полного списка тэгов
            
            @paran $nPropId - ID свойства инфоблока, к которому привязан инфоблок-справочник
        */
        function getAllTags(){
            $CDB = new \DB\CDB;
            // Получаем ID инфоблока из ктоорого надо достать значения
            $arProp = $CDB->searchOne(\AGShop\CAGShop::t_iblock_property,[
                "ID"=>$this->nProductPropertyId
            ],["LINK_IBLOCK_ID"]);
            // Получаем все элементы инфоблока справочника
            return $CDB->searchAll(\AGShop\CAGShop::t_iblock_element,[
                "IBLOCK_ID"=>$arProp["LINK_IBLOCK_ID"],
                "ACTIVE"=>"Y"
            ]);
        }
        
        
    }
