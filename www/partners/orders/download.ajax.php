<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Количество заказов, выгружаемых за квант
define("ORDERS_QUANT",1000);


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
    while($arOrder = $resOrders->Fetch()){
        $nNum++;
        fwrite($fd,
            '"'.$arOrder["ADDITIONAL_INFO"].'"'
            .";".'"'
                .$arOrder["USER_LAST_NAME"]
                ." "
                .$arOrder["USER_NAME"]
                .'"'
            .";".'"'.$arOrder["USER_EMAIL"].'"'   
            .";".'"'.$arOrder["STATUS_ID"].'"'  
            .";".'"История статусов"'    
            .";".$arOrder["DATE_INSERT"]
            .";".$arOrder["DATE_INSERT"]
            .";".'"Дата выполнения"' 
            .";".'"Уникальный штрих-код заказа"'
            .";".'"Уникальный номер товара"' 
            .";".'"'.str_replace("u","8",$arOrder["USER_LOGIN"]).'"' 
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
<body>

<div class="progress" style="float: left; width:60%;margin-right:10px;">
    <div class="progress-bar" role="progressbar" aria-valuenow="60"
    aria-valuemin="0" aria-valuemax="100" style="width: <?=
        round(100*(($nextPage*ORDERS_QUANT)/$_SESSION["ORDER_DOWNLOAD"]["NUM_ROWS"]),1);
    ?>%;">
    <?= 
        round(100*(($nextPage*ORDERS_QUANT)/$_SESSION["ORDER_DOWNLOAD"]["NUM_ROWS"]),0) 
    ?>%
    </div>
</div>
<a href="?cancel=1">Прервать</a>
<script>
<? if(((intval($_REQUEST['page'])+1)*ORDERS_QUANT)<=$_SESSION["ORDER_DOWNLOAD"]["NUM_ROWS"]):?>
document.location.href="<?= $_SERVER["SCRIPT_NAME"]."?continue=1&page=".$nextPage?>";
<? else:?>
document.location.href="<?= $_SERVER["SCRIPT_NAME"]."?getfile=1"?>";
<? endif ?>
</script>
</body>
