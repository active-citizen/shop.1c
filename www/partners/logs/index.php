<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы::Логи обмена");
require("../group_access.php");

$sQuery = $_REQUEST["query"];
if(!preg_match("#^[\s\d+]+$#",$sQuery))$sQuery= '';
?>
<div class="partners-main">
    <h1>Просмотр логов обмена</h1>
    <? include("../menu.php"); ?>
    <div class="log-palette">
        <div class="left-menu">
           <iframe src="/partners/logs/tree.frame.php<? 
            if(isset($_REQUEST["query"]) && $_REQUEST["query"])
                echo "?query=".$sQuery;
           ?>"> 
           </iframe>
        </div>
        <div class="request-win">
            <iframe name="request_win"></iframe>
        </div>
    </div>
</div>


<style>
div.log-palette{
    position: relative;
    min-height: 500px;
    bottom: 0px;
}

div.left-menu{
    float: left;
    width: 400px;
    border-right: 1px #DDD solid;
    padding: 10px;
    position: absolute;
    bottom: 0px;
    top: 0px;
}

.left-menu iframe{
    position: absolute;
    top: 10px;
    bottom: 10px;
    left: 10px;
    right: 10px;
    height: 100%;
    width: 95%;
    border: 1px transparent solid;
}

div.request-win{
    position: absolute;
    margin-left:410px;
    bottom: 0px;
    left:0px;
    right: 10px;
    top: 0px;
}

div.request-win iframe{
    width: 100%;
    height: 100%;
    border: 1pc transparent solid;
}

</style>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
