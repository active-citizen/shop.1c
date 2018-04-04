<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/includes/datafilter.lib.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/order.lib.php");

// Количество заказов, выгружаемых за квант
define("ORDERS_QUANT",1000);

$isPartnerOperator = ( 
    in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
    ||
    in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())
);

CModule::IncludeModule('catalog');

// Если пользователь - не оператор и не партнёр и не админ - выкидываем его
if(
    !in_array(PARTNERS_GROUP_ID, $USER->GetUserGroupArray())
    && !in_array(OPERATORS_GROUP_ID, $USER->GetUserGroupArray())
    && !in_array(SHOP_ADMIN, $USER->GetUserGroupArray())
    && !$USER->IsAdmin()
   
){
    echo "Access denied";
    die;
}

// Если поступила команда на формирование файла
if(isset($_REQUEST["download"])){
    
    // Формируем имя файла, в который будем писать CSV
    $sFilename = $_SERVER["DOCUMENT_ROOT"].
        "/upload/user".$USER->GetID()."_".time()."_".rand().".csv";
    $sDownloadFilename = 'orders_'.date("Y_m_d_H_i_s").".csv";
    $arOrder    = array();
    $arFilter   = array();



    $arUser = CUser::GetList(
        ($by="personal_country"), ($order="desc"),
        array("ID"=>CUser::GetId()),
        array(
            "SELECT"=>array(
                "UF_USER_ALL_POINTS",
                "UF_USER_STORAGE_ALL",
                "UF_USER_STORAGE_ID",
                "UF_USER_MAN_ALL",
                "UF_USER_MAN_ID"
            ),
            "NAV_PARAMS"=>array("nTopCount"=>1)
        )
        
    )->getNext();

    // Список доступных пользователю произвдителей
    $arManFilter = array();
    if(!$arUser["UF_USER_MAN_ALL"] && count($arUser["UF_USER_MAN_ID"]))
        $arManFilter["ID"] = $arUser["UF_USER_MAN_ID"];
    elseif(!$arUser["UF_USER_MAN_ALL"] && !count($arUser["UF_USER_MAN_ID"]))
        $arManFilter["ID"] = 0;

    $arManFilter["IBLOCK_ID"] = MANUFACTURER_IB_ID;

    $resMans = CIBlockElement::GetList(
        array("ID"=>"ASC"),
        $arManFilter,
        false,
        false,
        array("ID")
    );
    $arParams["MY_MANS_IDS"] = array();
    while($arMan = $resMans->GetNext()){
        $arParams["MY_MANS_IDS"][] = $arMan["ID"];
    }

    $arStoreFilter = array();
    if(!$arUser["UF_USER_STORAGE_ALL"] && count($arUser["UF_USER_STORAGE_ID"]))
        $arStoreFilter["ID"] = $arUser["UF_USER_STORAGE_ID"];
    elseif(!$arUser["UF_USER_STORAGE_ALL"] && !count($arUser["UF_USER_STORAGE_ID"]))
        $arStoreFilter["ID"] = 0;
        

    $resStores = CCatalogStore::GetList(
        array("ID"=>"ASC"),
        $arStoreFilter,
        false,
        false,
        array("ID")
    );
        
    $arParams["MY_STORES_IDS"] = array();
    while($arStore = $resStores->GetNext()){
        $arParams["MY_STORES_IDS"][] = $arStore["ID"];
    }

    if(isset($_REQUEST["filter_phone"]) && $_REQUEST["filter_phone"])
        $arFilter["%USER_LOGIN"] = $_REQUEST["filter_phone"];
    if(isset($_REQUEST["filter_fio"]) && $_REQUEST["filter_fio"])
        $arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"] =
        $_REQUEST["filter_fio"];
    if(isset($_REQUEST["filter_status"]) && $_REQUEST["filter_status"])
        $arFilter["STATUS_ID"] =
        $_REQUEST["filter_status"];


    if(
        isset($_REQUEST["filter_man"])
        && 
        $_REQUEST["filter_man"]
        && 
        $_REQUEST["filter_man"]!='all'
    )
        $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] =
        $_REQUEST["filter_man"];
    elseif(
        isset($_REQUEST["filter_man"])
        && 
        $_REQUEST["filter_man"]
        && 
        $_REQUEST["filter_man"]=='all'
    )
        $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] =
        $arParams["MY_MANS_IDS"];

    if(
        isset($_REQUEST["filter_store"])
        && 
        $_REQUEST["filter_store"]
        && 
        $_REQUEST["filter_store"]!='all'
    )
        $arFilter["STORE_ID"] =
        $_REQUEST["filter_store"];
    elseif(
        isset($_REQUEST["filter_store"])
        && 
        $_REQUEST["filter_store"]
        && 
        $_REQUEST["filter_store"]=='all'
    )
        $arFilter["STORE_ID"] = $arParams["MY_STORES_IDS"];




    if(
        isset($_REQUEST["filter_adddate_from"]) 
        && $_REQUEST["filter_adddate_from"]
        && isset($_REQUEST["filter_adddate_to"]) 
        && $_REQUEST["filter_adddate_to"]
    )
        $arFilter["><DATE_INSERT"] = array(
            $_REQUEST["filter_adddate_from"]." 00:00:00",
            $_REQUEST["filter_adddate_to"]." 23:59:59"
        );
    elseif(
        isset($_REQUEST["filter_adddate_from"]) 
        && $_REQUEST["filter_adddate_from"]
        && (
            isset($_REQUEST["filter_adddate_to"]) 
            || 
            !$_REQUEST["filter_adddate_to"]
        )
     )
        $arFilter[">DATE_INSERT"] = $_REQUEST["filter_adddate_from"]." 00:00:00";
    elseif(
        isset($_REQUEST["filter_adddate_to"]) 
        && $_REQUEST["filter_adddate_to"]
        && (!
            isset($_REQUEST["filter_adddate_from"]) 
            || !$_REQUEST["filter_adddate_from"]
        )
     )
        $arFilter["<DATE_INSERT"] = $_REQUEST["filter_adddate_to"]." 23:59:59";
 


    if(
        isset($_REQUEST["filter_update_from"]) 
        && $_REQUEST["filter_update_from"]
        && isset($_REQUEST["filter_update_to"]) 
        && $_REQUEST["filter_update_to"]
    )
        $arFilter["><DATE_UPDATE"] = array(
            $_REQUEST["filter_update_from"]." 00:00:00",
            $_REQUEST["filter_update_to"]." 23:59:59"
        );
    elseif(
        isset($_REQUEST["filter_update_from"]) 
        && $_REQUEST["filter_update_from"]
        && (!
            isset($_REQUEST["filter_update_to"]) 
            || !$_REQUEST["filter_update_to"]
        )
     )
        $arFilter[">DATE_UPDATE"] = $_REQUEST["filter_update_from"]." 00:00:00";
    elseif(
        isset($_REQUEST["filter_update_to"]) 
        && $_REQUEST["filter_update_to"]
        && (!
            isset($_REQUEST["filter_update_from"]) 
            || !$_REQUEST["filter_update_from"]
        )
     )
        $arFilter["<DATE_UPDATE"] = $_REQUEST["filter_update_to"]." 23:59:59";
 
    if(
        isset($_REQUEST["filter_lockdate_from"]) 
        && $_REQUEST["filter_lockdate_from"]
        && isset($_REQUEST["filter_lockdate_to"]) 
        && $_REQUEST["filter_lockdate_to"]
    )
        $arFilter["><PROPERTY_VAL_BY_CODE_CLOSE_DATE"] = array(
            $_REQUEST["filter_lockdate_from"]." 00:00:00",
            $_REQUEST["filter_lockdate_to"]." 23:59:59"
        );
    elseif(
        isset($_REQUEST["filter_lockdate_from"]) 
        && $_REQUEST["filter_lockdate_from"]
        && (!
            isset($_REQUEST["filter_lockdate_to"]) 
            || !$_REQUEST["filter_lockdate_to"]
        )
     )
        $arFilter[">PROPERTY_VAL_BY_CODE_CLOSE_DATE"] = 
            $_REQUEST["filter_lockdate_from"]." 00:00:00";
    elseif(
        isset($_REQUEST["filter_lockdate_to"]) 
        && $_REQUEST["filter_lockdate_to"]
        && (!
            isset($_REQUEST["filter_lockdate_from"]) 
            || !$_REQUEST["filter_lockdate_from"]
        )
     )
        $arFilter["<PROPERTY_VAL_BY_CODE_CLOSE_DATE"] = 
            $_REQUEST["filter_lockdate_to"]." 23:59:59";


    if(
        isset($_REQUEST["filter_done_from"]) 
        && $_REQUEST["filter_done_from"]
        && isset($_REQUEST["filter_done_to"]) 
        && $_REQUEST["filter_done_to"]
    ){
        $arFilter[">=DATE_STATUS"] = $_REQUEST["filter_done_from"]
            ." 00:00:00";
        $arFilter["<=DATE_STATUS"] = $_REQUEST["filter_done_to"]
            ." 23:59:59";
        $arFilter["STATUS_ID"] = 'F';
    }
    elseif(
        isset($_REQUEST["filter_done_from"]) 
        && $_REQUEST["filter_done_from"]
    ){
        $arFilter[">=DATE_STATUS"] = $_REQUEST["filter_done_from"]
            ." 00:00:00";
        $arFilter["STATUS_ID"] = 'F';
    }
    elseif(
        isset($_REQUEST["filter_done_to"]) 
        && $_REQUEST["filter_done_to"]
    ){
        $arFilter["<=DATE_STATUS"] = $_REQUEST["filter_done_to"]
            ." 23:59:59";
        $arFilter["STATUS_ID"] = 'F';
    }



    if($_REQUEST["filter_sort"]=='order_id')
        $arOrder["ADDITIONAL_INFO"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='customer')
        $arOrder["PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='email')
        $arOrder["USER_EMAIL"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='status')
        $arOrder["STATUS_ID"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='date_added')
        $arOrder["DATE_INSERT"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='date_modified')
        $arOrder["DATE_UPDATE"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='telephone')
        $arOrder["USER_LOGIN"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='storage_name')
        $arOrder["STORAGE_ID"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='expire_date')
        $arOrder["PROPERTY_VAL_BY_CODE_CLOSE_DATE"] = $_REQUEST["filter_order"];
    if($_REQUEST["filter_sort"]=='category_name')
        $arOrder["PROPERTY_VAL_BY_CODE_SECTION_NAME"] = $_REQUEST["filter_order"];
    if($_request["filter_sort"]=='manufacturer_name')
        $arOrder["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] = $_request["filter_order"];
    if($_request["filter_sort"]=='price')
        $arOrder["PRICE"] = $_request["filter_order"];


    //echo "<pre>";
    //print_r($arFilter);
    //die;

    // Получаем общее число записей
    $nNumRows = getDownloadOrders($arFilter,$arOrder, true);

    // Сохраняем параметры в сессию
    $_SESSION["ORDER_DOWNLOAD"] = array(
        "FILENAME"=>$sFilename,
        "DOWNLOAD_FILENAME"=>trim($sDownloadFilename),
        "FILTER"=>$arFilter,
        "ORDER"=>$arOrder,
        "NUM_ROWS"=>$nNumRows
    );



    $fd = fopen($sFilename,"w");
    $row = mb_convert_encoding( 
        '"№"'
        .(
            !$isPartnerOperator
            ?
            ";".'"ФИО покупателя"'.";".'"Email"'   
            :
            ""
        )
        .";".'"Статус"'  
        .";".'"История статусов"'    
        .";".'"Дата добавления"'
        .";".'"Дата последнего изменения"'   
        .";".'"Дата выполнения"' 
        .";".'"Уникальный штрих-код заказа"'
        .";".'"Уникальный номер товара"' 
        .";".'"Телефон"' 
        .";".'"Тип товара"'  
        .";".'"Товар"'   
        .";".'"Модель"'  
        .";".'"Центр выдачи"'
        .";".'"Дата истечения бронирования"' 
        .";".'"Категория"'   
        .";".'"Производитель"'   
        .";".'"Стоимость в баллах"'  
        .";".'"Стоимость в рублях"'
        .";".'"Количество"'  
        .";".'"Цена в баллах"'
        ."\r\n",
        "cp1251",
        "utf-8"
    );
    fwrite($fd,$row);
    fclose($fd);

    header("Location: ".$_SERVER["SCRIPT_NAME"]."?continue=1&page=1");
    die;
}

// Отмена
if(isset($_REQUEST["cancel"])){
    unlink($_SESSION["ORDER_DOWNLOAD"]["FILENAME"]);
    $_SESSION["ORDER_DOWNLOAD"] = array();
    header("Location: ".$_SERVER["SCRIPT_NAME"]."?empty=1");
    die;
}

// Пустой экран
if(isset($_REQUEST["empty"])){
?>
<script>
parent.document.getElementById('download_submit').style.display = '';
</script>
<?
    die;
}

// Шаг формирования файла
if(isset($_REQUEST["continue"])){
    $nextPage = intval($_REQUEST["page"])+1;
    $arOrder    = $_SESSION["ORDER_DOWNLOAD"]["ORDER"];
    $arFilter   = $_SESSION["ORDER_DOWNLOAD"]["FILTER"];
    $nLimit = ORDERS_QUANT;
    if(
        !isset($_GET["page"])
        ||
        !intval($_GET["page"])
    )
        $nOffset = 0;
    else
        $nOffset = (intval($_GET["page"])-1)*ORDERS_QUANT;

    $arOrders = getDownloadOrders(
        $arFilter,$arOrder, false, $nLimit, $nOffset
    );    

    $fd = fopen($_SESSION["ORDER_DOWNLOAD"]["FILENAME"],"a");
    $nNum = 0;

    foreach($arOrders as $arOrder){
        // Получаем историю заказа
        $nNum++;
        $row = mb_convert_encoding( 
            '"'.$arOrder["ORDER_NUM"].'"'
            .(
                !$isPartnerOperator
                ?(
                    ";".'"' .$isPartnerOperator.dataNormalize($arOrder["USER_LAST_NAME"])
                    ." " .dataNormalize($arOrder["USER_NAME"])
                    .'"'

                    .";".'"'.$arOrder["USER_EMAIL"].'"'   
                )
                :
                ""
            )
            .";".'"'.$arOrder["STATUS_NAME"].'"'  
            .";".''/*.'"История статусов"'*/
            .";".$arOrder["DATE_INSERT"]
            .";".$arOrder["DATE_UPDATE"]
            .";".''.(
                $arOrder["STATUS_ID"]=='F' && isset($arOrder["SHIPDATE"])
                ?
                date(
                    "d.m.Y H:i:s",
                    MakeTimeStamp($arOrder["SHIPDATE"],"YYYY-MM-DD HH:MI:SS")
                )
                :
                ($arOrder["STATUS_ID"]=='F'?$arOrder["DATE_UPDATE"]:"")
            ).''
            .";".'" "'//'"Уникальный штрих-код заказа"'
            .";".'" "'//'"Уникальный номер товара"' 
            .";".'"'.str_replace("u","",$arOrder["USER_LOGIN"]).'"' 
            .";".'" "'//'"Тип товара"'  
            .";".'"'.
                html_entity_decode($arOrder["PRODUCT_NAME"],ENT_QUOTES|ENT_HTML401)
            .'"'   
            .";".'""'//'"Модель"'  
            .";".'"'.($arOrder["STORE_NAME"]).'"'
            .";".get_date($arOrder["CLOSE_DATE"],false)
            .";".'"'.($arOrder["SECTION_NAME"]).'"'
            .";".'"'.($arOrder["MANUFACTURER_NAME"]).'"'   
            .";".round($arOrder["COST"])
            .";".'""'//'"Стоимость в рублях"'
            .";".round($arOrder["QUANTITY"])
            .";".round($arOrder["PRICE"])
            ."\r\n",
            "cp1251",
            "utf-8"
        );
        fwrite( $fd, $row);
    }
    fclose($fd);
}
// Шаг скачивания файла
if(isset($_REQUEST["getfile"])){
    header('Content-disposition: attachment;filename="'.trim($_SESSION["ORDER_DOWNLOAD"]["DOWNLOAD_FILENAME"]).'"');
    header("Content-type: text/csv; charset=windows-1251");
    $fd = fopen($_SESSION["ORDER_DOWNLOAD"]["FILENAME"],'r');
    while(!feof($fd)){
        $buffer = fread($fd,1000);
        if(!trim($buffer))continue;
        echo $buffer; 
    }
    fclose($fd);
    die;
    unlink($_SESSION["ORDER_DOWNLOAD"]["FILENAME"]);
    die;
}


?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8"/>
<link href="/local/assets/bootstrap/css/bootstrap.min.css" 
type="text/css"  rel="stylesheet" />
</head>
<body id="body">

<?
    $percent =
    round(100*(($nextPage*ORDERS_QUANT)/$_SESSION["ORDER_DOWNLOAD"]["NUM_ROWS"]),1);
    if($percent>100)$percent = 100;
?>
<div class="progress" style="float: left; width:60%;margin-right:10px;">
    <div class="progress-bar" role="progressbar" aria-valuenow="60"
    aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent;?>%;">
    <?= $percent?>%
    </div>
</div>
<a href="?cancel=1">Прервать</a>
<script>
<? if(
    (
        (intval($_REQUEST['page']))*ORDERS_QUANT
    )<=$_SESSION["ORDER_DOWNLOAD"]["NUM_ROWS"]
):
?>
document.location.href="<?= $_SERVER["SCRIPT_NAME"]."?continue=1&page=".$nextPage?>";
<? else:?>
parent.document.getElementById('download_submit').style.display = '';
document.getElementById('body').innerHTML = '';
document.location.href="<?= $_SERVER["SCRIPT_NAME"]."?getfile=1"?>";
<? endif ?>
</script>
</body>




