<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки::Кабинет партнёра");
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
        require_once(
            $_SERVER["DOCUMENT_ROOT"]
                ."/.integration/classes/troyka.class.php"
        );
        $objTroyka = new CTroyka();            
        $objTroyka->emulation = false;

        $arOrder = CSaleOrder::GetList(
            array("ID"=>"ASC"),
            array("!ADDITIONAL_INFO"=>false),
            false,
            array("nTopCount"=>1),
            array("ADDITIONAL_INFO")
        )->Fetch();


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
</div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
