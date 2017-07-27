<?
    define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
    define('BX_SECURITY_SESSION_READONLY', true);
    require_once($_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php");
    global $USER;


    if(isset($orderInfo["ORDER"]["ID"]) && $orderInfo["ORDER"]["ID"]){
        $orderId = $orderInfo["ORDER"]["ID"];
    }
    elseif(isset($_GET['id']) && intval($_GET['id'])){
        $orderId = intval($_GET['id']);
    }

    if(!isset($orderInfo["ORDER"]))$orderInfo = initOrderGetInfo($orderId);

    CModule::IncludeModule('sale');
    CModule::IncludeModule('iblock');

    $arOrder = CSaleOrder::GetByID($orderId);


    // Проверяем чтобы заказ принадлежал пользователю
    if(
        !$USER->isAdmin() 
        && $arOrder["USER_ID"]!=$USER->GetId()
        && !in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
        && !in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())
    ){
        echo "Access denied";
        die;
    }


    $nOrderId = intval($_REQUEST["id"]);

    $sPanthomJsPath = dirname(__FILE__)."/phantomjs";
    $sTemplateJSFile = realpath(dirname(__FILE__))."/print.js";
    $sJSFile = $_SERVER["DOCUMENT_ROOT"]."/../"
        ."renders/js/"
        .$_COOKIE["PHPSESSID"]
        ."_".$nOrderId.".js"; 
    $sPngFile = $_SERVER["DOCUMENT_ROOT"]."/../"
        ."renders/png/"
        .$nOrderId.".png"; 

    if(isset($_REQUEST["generate"])){
        $sJs = file_get_contents($sTemplateJSFile);
        $sJs = str_replace("{PHPSESSID}",$_COOKIE["PHPSESSID"],$sJs);
        $sJs = str_replace("{ORDER_ID}",$nOrderId,$sJs);
        $sJs = str_replace("{CERT_PATH}",$sPngFile,$sJs);
        $sJs = str_replace("{PHPSESSID}",$_COOKIE["PHPSESSID"],$sJs);
//        echo "<pre>";
//        echo $sJs;
//        die;
        file_put_contents($sJSFile,$sJs);
        $sJSFile = realpath($sJSFile);
        $output=array();
        $sys = exec("$sPanthomJsPath '$sJSFile'", $output);
        unlink($sJSFile);
    }

    $stat = stat($sPngFile);


    if($_REQUEST["act"]=='download'){
        header("Content-type: image/png");
        header("Content-length: ".$stat["size"]);
        header("Content-disposition: attachment; filename=\"".$nOrderId.".png\"");

        echo file_get_contents($sPngFile);
    }
    elseif($_REQUEST["act"]=='get'){
        header("Content-type: image/png");
        header("Content-length: ".$stat["size"]);

        echo file_get_contents($sPngFile);
    }
    elseif($_REQUEST["act"]=='print'){
        ?>
        <html>
            <head>
            </head>
            <body>
            <img src="data:image/png;base64,<?=  
                base64_encode(file_get_contents($sPngFile))
            ?>" style="width:100%">
            <script>
                window.print();                
            </script>
            </body>
        </html>
        <?
    }




