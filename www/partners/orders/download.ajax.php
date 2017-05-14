<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Количество заказов, выгружаемых за квант
define("ORDERS_QUANT",100);

CModule::IncludeModule('catalog');

// Если пользователь - не оператор и не партнёр и не админ - выкидываем его
if(
    !in_array(9, $USER->GetUserGroupArray())
    && !in_array(10, $USER->GetUserGroupArray())
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
        $arorder["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"] = $_request["filter_order"];
    if($_request["filter_sort"]=='price')
        $arorder["PRICE"] = $_request["filter_order"];


    // Запрашиваем
    $resOrders = CSaleOrder::GetList(
        $arOrder?$arOrder:array("ID"=>"DESC"),
        $arFilter,
        false,
        array(
           "nPageSize"  =>  1,
           "iNumPage"   =>  1
        ),
        $arSelect
    );

    $nNumRows = $resOrders->SelectedRowsCount();

    // Сохраняем параметры в сессию
    $_SESSION["ORDER_DOWNLOAD"] = array(
        "FILENAME"=>$sFilename,
        "DOWNLOAD_FILENAME"=>$sDownloadFilename,
        "FILTER"=>$arFilter,
        "ORDER"=>$arOrder,
        "NUM_ROWS"=>$nNumRows
    );

    $fd = fopen($sFilename,"w");
    fwrite($fd,
        '"№"'
        .";".'"ФИО покупателя"'
        .";".'"Email"'   
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
        ."\r\n"
    );
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
    $arSelect   = array(
        "ID",
        "STATUS_ID",
        "ADDITIONAL_INFO",
        "USER_LAST_NAME",
        "USER_NAME",
        "DATE_INSERT",
        "USER_EMAIL",
        "USER_LOGIN",
        "STORE_ID",
        "PRICE"
    );

    // Запрашиваем
    $resOrders = CSaleOrder::GetList(
        $arOrder?$arOrder:array("ID"=>"DESC"),
        $arFilter,
        false,
        array(
           "nPageSize"  => ORDERS_QUANT,
           "iNumPage"   => intval($_REQUEST["page"]) 
        ),
        $arSelect
    );

    $fd = fopen($_SESSION["ORDER_DOWNLOAD"]["FILENAME"],"a");
    $nNum = 0;

    // Справочник статусов
    $resStatuses = CSaleStatus::GetList();
    $arResult["STATUSES"] = array();
    while($arStatus = $resStatuses->Fetch())
        $arResult["STATUSES"][$arStatus["ID"]] = $arStatus;

    // Справочник центров выдачи
    $resStores  = CCatalogStore::GetList(
        array(),
        array(),
        false,false
    );
    $arStores = array();
    while($arStore = $resStores->GetNext()){
        $arStores[$arStore["ID"]] = $arStore;
    }


    // ID группы свойств
    $arPropGroup = CSaleOrderPropsGroup::GetList(
        array(),
        $arPropGroupFilter = array("NAME"=>"Индексы для фильтров"),
        false,
        array("nTopCount"=>1)
    )->GetNext();
    $nPropGroup = $arPropGroup["ID"];


    while($arOrder = $resOrders->Fetch()){

        // Получаем историю заказа
        $arHistory = array();
        $resHistory = CSaleOrderChange::GetList(
            array("ID"=>"DESC"),
            array(
                "ORDER_ID"=>$arOrder["ID"]
            ),
            false,
            array("nTopCount"=>10)

        );
        while($arHistoryItem = $resHistory->Fetch()){
            $arHistory[] = $arHistoryItem;
        }

        // Получаем свойства 
        $resPropValues = CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            array(
                    "ORDER_ID"       => $arOrder["ID"],
                    "PERSON_TYPE_ID" => 1,
                    "PROPS_GROUP_ID" => $nPropGroup,
                ),
            false,
            false,
            array("ID","CODE")
        );


        $arOrder["PROPERTIES"] = array();
        while($arProp = $resPropValues->GetNext()){
            
            $arOrder["PROPERTIES"][$arProp["CODE"]] = 
                CSaleOrderPropsValue::GetList(
                    array(),
                    $arFilterProp = array(
                        "ORDER_ID"=>$arOrder["ID"],
                        "ORDER_PROPS_ID"=>$arProp["ID"]
                    )
                )->GetNext();
        }
   
        $nNum++;
        fwrite($fd,
            '"'.$arOrder["ADDITIONAL_INFO"].'"'
            .";".'"'
                .$arOrder["USER_LAST_NAME"]
                ." "
                .$arOrder["USER_NAME"]
                .'"'
            .";".'"'.$arOrder["USER_EMAIL"].'"'   
            .";".'"'.$arResult["STATUSES"][$arOrder["STATUS_ID"]]["NAME"].'"'  
            .";".''/*.'"История статусов"'*/
            .";".$arOrder["DATE_INSERT"]
            .";".$arOrder["DATE_INSERT"]
            .";".''.(
                $arOrder["STATUS_ID"]=='F' && isset($arHistory[0]["DATE_CREATE"])
                ?
                $arHistory[0]["DATE_CREATE"]
                :
                ""
            ).''
            .";".'" "'//'"Уникальный штрих-код заказа"'
            .";".'" "'//'"Уникальный номер товара"' 
            .";".'"'.str_replace("u","8",$arOrder["USER_LOGIN"]).'"' 
            .";".'" "'//'"Тип товара"'  
            .";".'"'.
                    $arOrder["PROPERTIES"]["PRODUCT_NAME"]["VALUE"]
            .'"'   
            .";".'""'//'"Модель"'  
            .";".'"'.$arStores[$arOrder["STORE_ID"]]["TITLE"].' "'
            .";".get_date($arOrder["PROPERTIES"]["CLOSE_DATE"]["VALUE"],false)
            .";".'"'.$arOrder["PROPERTIES"]["SECTION_NAME"]["VALUE"].'"'
            .";".'"'.$arOrder["PROPERTIES"]["MANUFACTURER_NAME"]["VALUE"].
                '"'   
            .";".round($arOrder["PRICE"])
            .";".'""'//'"Стоимость в рублях"'
            ."\r\n"
        );
    }
    fclose($fd);
}

// Шаг скачивания файла
if(isset($_REQUEST["getfile"])){
    header("Content-type: text/csv");
    header('Content-disposition: attachment;filename="'.
        $_SESSION["ORDER_DOWNLOAD"]["DOWNLOAD_FILENAME"]
    .'"');
    $fd = fopen($_SESSION["ORDER_DOWNLOAD"]["FILENAME"],'r');
    while(!feof($fd)){
        echo fread($fd,1000);
    }
    fclose($fd);
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
    round(100*(($nextPage*ORDERS_QUANT)/$_SESSION["ORDER_DOWNLOAD"]["NUM_ROWS"]),0);
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
