<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки::Кабинет партнёра");

require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CIntegration/CIntegrationTroyka.class.php");
use AGShop\Integration as Integration;


?>
<div class="partners-main">
    <h1>Настройки</h1>
    <? include("../menu.php"); ?>
    <form method="post" style="text-align: center;" action="#bindings">
        <input type="submit" class="btn btn-primary" value="Очистить кэш плиток"
        name="CLEAR_CUSTOM_CACHE">
    </form>
    <h2>Тройка</h2>
    <?$APPLICATION->IncludeComponent("ag:settings","",array(
        "CODE"  =>  "TROYKA",
        "CACHE_TIME"=>COMMON_CACHE_TIME
    ),false);?> 
    <form method="post" style="text-align: center;" action="#bindings">
        <input type="submit" class="btn btn-primary" value="Получить список
        доступных карт (режим симуляции игнорируется)"
        name="GET_BINDINGS">
    </form>
    <a name="bindings"></a>
    <? if($_REQUEST["GET_BINDINGS"]):?>
    <pre>
    <? 
        $objTroyka = new \Integration\CIntegrationTroyka();            
        $objTroyka->emulation = false;

        if(!$arBindings = $objTroyka->getBindings($arOrder["ADDITIONAL_INFO"])){
            print_r($objTroyka);
        }
        else{
            print_r($arBindings);
        }
 
        
    ?>
    </pre>
    <? elseif($_REQUEST["CLEAR_CUSTOM_CACHE"]):?>
    <? 
        require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/customcache.lib.php"); 
        customCacheClear();
    ?>
    <pre>Кэш плитки очищен</pre>
    <? endif ?>

    <h2>Парковки</h2>
    <?$APPLICATION->IncludeComponent("ag:settings","",array(
        "CODE"  =>  "PARKING",
        "CACHE_TIME"=>COMMON_CACHE_TIME
    ),false);?> 

    <h2>Банеры</h2>
    <?$APPLICATION->IncludeComponent("ag:settings","",array(
        "CODE"  =>  "BANNERS",
        "CACHE_TIME"=>COMMON_CACHE_TIME
    ),false);?> 

    <h2>Информационное окно</h2>
    <?$APPLICATION->IncludeComponent("ag:settings","",array(
        "CODE"  =>  "INFO",
        "CACHE_TIME"=>COMMON_CACHE_TIME
    ),false);?> 

</div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
