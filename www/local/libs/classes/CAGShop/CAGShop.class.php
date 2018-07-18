<?
namespace AGShop;

require_once("CCache/CCache.class.php");
//require_once(realpath(__DIR__."/../..")."/vendor/autoload.php");
use App\Cache as Cache;

class CAGShop{


    private $arErrors = []; //!< Массив последних ошибок
    var $IBLOCKS = [];      //!< ID инфоблоко
    var $PROPERTIES = [];   //!< ID свойств инфоблоков

    // Таблицы БД битрикса
    const t_iblock_element  =  'b_iblock_element';
    const t_iblock_element_property = 'b_iblock_element_property';
    const t_iblock_section = 'b_iblock_section';
    const t_file = 'b_file';
    const t_catalog_store = 'b_catalog_store';
    const t_catalog_store_product = 'b_catalog_store_product';
    const t_iblock_property = 'b_iblock_property';
    const t_sale_order_props = 'b_sale_order_props';
    const t_sale_order_props_value = 'b_sale_order_props_value';
    const t_sale_basket = 'b_sale_basket';
    const t_sale_order = 'b_sale_order';
    const t_index_order = 'index_order';
    const t_bets = 'int_bets';

    function __construct(){
        if(defined("CATALOG_IB_ID"))
            $this->IBLOCKS["CATALOG"] = CATALOG_IB_ID;
        if(defined("OFFER_IB_ID"))
            $this->IBLOCKS["OFFER"] = OFFER_IB_ID;
        if(defined("WISHES_IB_ID"))
            $this->IBLOCKS["WISHES"] = WISHES_IB_ID;
            
        if(defined("INTEREST_PROPERTY_ID"))
            $this->PROPERTIES["INTEREST"] = INTEREST_PROPERTY_ID;
        if(defined("CML2_LINK_PROPERTY_ID"))
            $this->PROPERTIES["CML2_LINK"] = CML2_LINK_PROPERTY_ID;
            
        if(defined("PRICE_PROPERTY_ID"))
            $this->PROPERTIES["PRICE"] = PRICE_PROPERTY_ID;
        
        if(defined("HIDE_DATE_PROPERTY_ID"))
            $this->PROPERTIES["HIDE_DATE"] = HIDE_DATE_PROPERTY_ID;
            
        if(defined("AUCTION_START_DATE_PROPERTY_ID"))
            $this->PROPERTIES["AUCTION_START_DATE_PROPERTY_ID"] =
                AUCTION_START_DATE_PROPERTY_ID;

        if(defined("AUCTION_END_DATE_PROPERTY_ID"))
            $this->PROPERTIES["AUCTION_END_DATE_PROPERTY_ID"] =
                AUCTION_END_DATE_PROPERTY_ID;
    }


    /**
        Конвертация даты из любого допустимого формата в YYYY-MM-DD HH:ii:SS
    */
    function getDateISO($sDate, $bShort = false){
        $tmp = date_parse($sDate);
        if($tmp["error_count"]){
            $this->addError("Некорректный формат даты ".htmlspecialchars($sDate));
            return false;
        }
        
        foreach(["month","day","hour","minute","second"] as $key)
            $tmp[$key] = sprintf("%02d", $tmp[$key]);
        
        return $tmp["year"]."-"
            .$tmp["month"]
            ."-".$tmp["day"]
            .(!$bShort?" ".$tmp["hour"]:"")
            .(!$bShort?":".$tmp["minute"]:"")
            .(!$bShort?":".$tmp["second"]:"");
    }

    /**
        Конвертация даты из любого допустимого формата в DD.MM.YYYY HH:ii:SS
    */
    function getDateHum($sDate, $bShort = false){
        $tmp = date_parse($sDate);
        if($tmp["error_count"]){
            $this->addError("Некорректный формат даты ".htmlspecialchars($sDate));
            return false;
        }
        
        foreach(["month","day","hour","minute","second"] as $key)
            $tmp[$key] = sprintf("%02d", $tmp[$key]);
        
        return $tmp["day"]."."
            .$tmp["month"]
            .".".$tmp["year"]
            .(!$bShort?" ".$tmp["hour"]:"")
            .(!$bShort?":".$tmp["minute"]:"")
            .(!$bShort?":".$tmp["second"]:"");
    }

    /**
        Конвертация даты из любого допустимого формата в HH:ii:SS
    */
    function getTime($sDate){
        $bShort = false;
        $tmp = date_parse($sDate);
        if($tmp["error_count"]){
            $this->addError("Некорректный формат даты ".htmlspecialchars($sDate));
            return false;
        }
        
        foreach(["month","day","hour","minute","second"] as $key)
            $tmp[$key] = sprintf("%02d", $tmp[$key]);
        
        return (!$bShort?"".$tmp["hour"]:"")
            .(!$bShort?":".$tmp["minute"]:"")
            .(!$bShort?":".$tmp["second"]:"");
    }

    //получение ID инфоблока
    function getIblockId($sType="references",$sCode="regions"){
        $objCache = new \Cache\CCache("iblockId",$sType.$sCode,1000000);
        if($sCacheData = $objCache->get())return $sCacheData;
        
        if(!$arRegion = \CIBLock::GetList([],[
            "TYPE"=>$sType,
            "CODE"=>$sCode
        ])->Fetch())return $this->addError(\Lang\CLang::gt(
            "Инфоблок $sType:$sCode не существует"
        ));
        return $objCache->set($arRegion["ID"]);
    }

    
    function getPropId(
        $sType="references",
        $sCode="regions",
        $sPropertyCode='NEWPRODUCT'
    ){
        $objCache = new \Cache\CCache("propId",$sType.$sCode.$sPropertyCode,1000000);
        if($sCacheData = $objCache->get())return $sCacheData;
        
        $nIblockId = $this->getIblockId($sType,$sCode);
        if(!$arProp = \CIBlockProperty::GetList([],[
            "IBLOCK_ID"=>$nIblockId,
            "CODE"=>$sPropertyCode,
        ],false)->Fetch())return false;
        return $objCache->set($arProp["ID"]);
    }

    function addError($error){
        if(is_array($error)){  
            foreach($error as $err)$this->arErrors[] = $err;
        }
        else{
            $this->arErrors[] = $error;
        }
        return false;
    }

    function getErrors(){
        return $this->arErrors;
    }

    function clearError(){
        $this->arErrors = [];
    }
}
