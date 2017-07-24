<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки::Кабинет партнёра");
?>
<div class="partners-main">
    <h1>Настройки</h1>
    <? include("../menu.php"); ?>
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

        if(!$arBindings = $objTroyka->getBindings('0000000000')){
            print_r($objTroyka);
        }
        else{
            print_r($arBindings);
        }
 
        
    ?>
    </pre>
    <? endif ?>
    <h2>Парковки</h2>
    <?$APPLICATION->IncludeComponent("ag:settings","",array(
        "CODE"  =>  "PARKING",
        "CACHE_TIME"=>COMMON_CACHE_TIME
    ),false);?> 
</div>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
