<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/includes/datafilter.lib.php");

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
        $nOffset = intval($_GET["PAGE"])*ORDERS_QUANT;

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
                $arOrder["STATUS_ID"]=='F' && isset($arOrder["DATE_STATUS"])
                ?
                $arOrder["DATE_STATUS"]
                :
                ""
            ).''
            .";".'" "'//'"Уникальный штрих-код заказа"'
            .";".'" "'//'"Уникальный номер товара"' 
            .";".'"'.str_replace("u","8",$arOrder["USER_LOGIN"]).'"' 
            .";".'" "'//'"Тип товара"'  
            .";".'"'.
                ($arOrder["PRODUCT_NAME"])
            .'"'   
            .";".'""'//'"Модель"'  
            .";".'"'.($arOrder["STORE_NAME"]).'"'
            .";".get_date($arOrder["PROPERTIES"]["CLOSE_DATE"]["VALUE"],false)
            .";".'"'.($arOrder["SECTION_NAME"]).'"'
            .";".'"'.($arOrder["MANUFACTURER_NAME"]).'"'   
            .";".round($arOrder["PRICE"])
            .";".'""'//'"Стоимость в рублях"'
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
        (intval($_REQUEST['page'])-1)*ORDERS_QUANT
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
<?
    /**
        Получение списка заказов по фильтру
    */
    function getDownloadOrders(
        $arFilter,
        $arOrder,
        $bOnlyCount=true, 
        $nLimit=0,
        $nOffset=0
    ){
        global $DB;

        // Справочник статусов
        $resStatuses = CSaleStatus::GetList();
        $arStatuses = [];
        while($arStatus = $resStatuses->Fetch())
            $arStatuses[$arStatus["ID"]] = $arStatus;

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


        // Составляем справочник свойств
        $sQuery = "SELECT `ID`,`CODE` FROM `b_sale_order_props`";        
        $res = $DB->Query($sQuery);
        $arProps = [];
        while($arProp = $res->Fetch())$arProps[$arProp["CODE"]]=$arProp["ID"];

        $sFrom = "
            `b_sale_order` as `order`";
        $sFrom .= "
            LEFT JOIN
        `b_user` as `user`
            ON
                `user`.`ID`=`order`.`USER_ID` ";
        $sFrom .= "
                LEFT JOIN
            `b_sale_order_props_value` as `man`
                ON
                    `man`.`ORDER_PROPS_ID`=".$arProps["MANUFACTURER_ID"]."
                    AND `man`.`ORDER_ID`=`order`.`ID`";
        $sFrom .= "
            LEFT JOIN
        `b_sale_order_props_value` as `close`
            ON
                `close`.`ORDER_PROPS_ID`=".$arProps["CLOSE_DATE"]."
                AND `close`.`ORDER_ID`=`order`.`ID`";
        $sFrom .= "
            LEFT JOIN
        `b_sale_order_props_value` as `product`
            ON
                `product`.`ORDER_PROPS_ID`=".$arProps["PRODUCT_NAME"]."
                AND `product`.`ORDER_ID`=`order`.`ID`";
        $sFrom .= "
            LEFT JOIN
        `b_sale_order_props_value` as `section`
            ON
                `section`.`ORDER_PROPS_ID`=".$arProps["SECTION_NAME"]."
                AND `section`.`ORDER_ID`=`order`.`ID`";
        $sFrom .= "
            LEFT JOIN
        `b_sale_order_props_value` as `man_name`
            ON
                `man_name`.`ORDER_PROPS_ID`=".$arProps["MANUFACTURER_NAME"]."
                AND `man_name`.`ORDER_ID`=`order`.`ID`";
        $sFrom .= "
            LEFT JOIN
        `b_sale_basket` as `basket`
            ON
                `basket`.`ORDER_ID`=`order`.`ID`
            LEFT JOIN
        `b_iblock_element_property` as `product_link`
            ON 
                `product_link`.`IBLOCK_PROPERTY_ID`= ".CML2_LINK_PROPERTY_ID."
                AND `basket`.`PRODUCT_ID`=`product_link`.`IBLOCK_ELEMENT_ID`
            LEFT JOIN
        `b_iblock_element_property` as `price`
            ON
                `price`.`IBLOCK_PROPERTY_ID`=".PRICE_PROPERTY_ID."
                AND `product_link`.`VALUE_NUM`=`price`.`IBLOCK_ELEMENT_ID`";


        $sWhere = "
            1";



        if(isset($arFilter["STATUS_ID"]))
            $sWhere .= " 
            AND `order`.`STATUS_ID`='".$DB->ForSql($arFilter["STATUS_ID"])."'";

        if(isset($arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"])){
             if(is_array($arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"]))
                $sWhere .= "
                    AND `man`.`VALUE` IN ("
                        .$DB->ForSql(
                            implode(",",
                                $arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"]
                            )
                        )
                    .")";
             else
                $sWhere .= "
                    AND `man`.`VALUE`= '"
                        .$DB->ForSql($arFilter["PROPERTY_VAL_BY_CODE_MANUFACTURER_ID"])
                    ."'";
                
        }



        if(isset($arFilter["><PROPERTY_VAL_BY_CODE_CLOSE_DATE"])){
            $sWhere .= "
            AND `close`.`ORDER_ID` IS NOT NULL ";
            $sWhere .= "
                AND `close`.`VALUE`>= '"
                    .ConvertDateTime(
                        $arFilter["><PROPERTY_VAL_BY_CODE_CLOSE_DATE"][0],
                        "YYYY-MM-DD 00:00:00",
                        "DD.MM.YYYY HH:MI:SS"
                    )
                ."' 
                AND `close`.`VALUE`<= '"
                    .ConvertDateTime(
                        $arFilter["><PROPERTY_VAL_BY_CODE_CLOSE_DATE"][1],
                        "YYYY-MM-DD 23:59:59",
                        "DD.MM.YYYY HH:MI:SS"
                    )
                ."'";
        }
        if(isset($arFilter[">PROPERTY_VAL_BY_CODE_CLOSE_DATE"])){
            $sWhere .= "
            AND `close`.`ORDER_ID` IS NOT NULL ";
            $sWhere .= "
                    AND `close`.`ORDER_ID`=`order`.`ID`
                    AND `close`.`VALUE`>= '"
                        .ConvertDateTime(
                            $arFilter[">PROPERTY_VAL_BY_CODE_CLOSE_DATE"],
                            "YYYY-MM-DD 00:00:00",
                            "DD.MM.YYYY HH:MI:SS"
                        )
                    ."'";

        }
        if(isset($arFilter["<PROPERTY_VAL_BY_CODE_CLOSE_DATE"])){
            $sWhere .= "
            AND `close`.`ORDER_ID` IS NOT NULL ";
            $sWhere .= "
                    AND `close`.`ORDER_ID`=`order`.`ID`
                    AND `close`.`VALUE`<='"
                        .ConvertDateTime(
                            $arFilter["<PROPERTY_VAL_BY_CODE_CLOSE_DATE"],
                            "YYYY-MM-DD 23:59:59",
                            "DD.MM.YYYY HH:MI:SS"
                        )
                    ."'";
        }


        if(isset($arFilter["STORE_ID"])){
            if(is_array($arFilter["STORE_ID"]))
                $sWhere .= "
                AND `order`.`STORE_ID` IN ("
                    .$DB->ForSql(implode(",",$arFilter["STORE_ID"]))
                .")";
            else
                $sWhere .= "
                AND `order`.`STORE_ID`=".intval($arFilter["STORE_ID"])."";
        }

        if(isset($arFilter["><DATE_INSERT"])){
            $sWhere .= "
            AND `order`.`DATE_INSERT`>='"
                .ConvertDateTime(
                    $arFilter["><DATE_INSERT"][0],
                    "YYYY-MM-DD 00:00:00",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'
            AND `order`.`DATE_INSERT`<='"
                .ConvertDateTime(
                    $arFilter["><DATE_INSERT"][1],
                    "YYYY-MM-DD 23:59:59",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."' ";
        }
        if(isset($arFilter[">DATE_INSERT"])){
            $sWhere .= "
            AND `order`.`DATE_INSERT`>='"
                .ConvertDateTime(
                    $arFilter[">DATE_INSERT"],
                    "YYYY-MM-DD 00:00:00",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'";
        }
        if(isset($arFilter["<DATE_INSERT"])){
            $sWhere .= "
            AND `order`.`DATE_INSERT`<='"
                .ConvertDateTime(
                    $arFilter["<DATE_INSERT"],
                    "YYYY-MM-DD 23:59:59",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'";
        }


        if(isset($arFilter["><DATE_UPDATE"])){
            $sWhere .= "
            AND `order`.`DATE_UPDATE`>='"
                .ConvertDateTime(
                    $arFilter["><DATE_UPDATE"][0],
                    "YYYY-MM-DD 00:00:00",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'
            AND `order`.`DATE_UPDATE`<='"
                .ConvertDateTime(
                    $arFilter["><DATE_UPDATE"][1],
                    "YYYY-MM-DD 23:59:59",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."' ";
        }
        if(isset($arFilter[">DATE_UPDATE"])){
            $sWhere .= "
            AND `order`.`DATE_UPDATE`>='"
                .ConvertDateTime(
                    $arFilter[">DATE_UPDATE"],
                    "YYYY-MM-DD 00:00:00",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'";
        }
        if(isset($arFilter["<DATE_UPDATE"])){
            $sWhere .= "
            AND `order`.`DATE_UPDATE`<='"
                .ConvertDateTime(
                    $arFilter["<DATE_UPDATE"],
                    "YYYY-MM-DD 23:59:59",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'";
        }

        if(isset($arFilter[">=DATE_STATUS"])){
            $sWhere .= "
            AND `order`.`DATE_STATUS`>='"
                .ConvertDateTime(
                    $arFilter[">=DATE_STATUS"],
                    "YYYY-MM-DD 00:00:00",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'";
        }

        if(isset($arFilter["<=DATE_STATUS"])){
            $sWhere .= "
            AND `order`.`DATE_STATUS`<='"
                .ConvertDateTime(
                    $arFilter["<=DATE_STATUS"],
                    "YYYY-MM-DD 23:59:59",
                    "DD.MM.YYYY HH:MI:SS"
                )
            ."'";
        }

        if(isset($arFilter["%USER_LOGIN"])){
            $sWhere .= "
            AND `user`.`LOGIN` LIKE '%".$arFilter["%USER_LOGIN"]."%' ";
        }

        if(isset($arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"])){
            $sWhere .="
            AND
            (
                `user`.`NAME` LIKE '%"
                    .$DB->ForSql($arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"])
                    ."%'
                OR
                `user`.`LAST_NAME` LIKE '%"
                    .$DB->ForSql($arFilter["%PROPERTY_VAL_BY_CODE_NAME_LAST_NAME"])
                ."%'
            ) ";
        }

        $sGroupBy = "";

        $sQuery = "
            SELECT
                COUNT(DISTINCT `order`.`ID`) as `COUNT`
            FROM
                $sFrom
            WHERE
                $sWhere
        ";
        $resOrder = $DB->Query($sQuery);
        if($bOnlyCount){
            $arOrder = $resOrder->Fetch();
            return $arOrder["COUNT"];
        }

        if($nOffset && $nLimit)
            $sLimit = "LIMIT $nOffset, $nLimit";
        elseif($nLimit)
            $sLimit = "LIMIT $nLimit";
        else
            $sLimit = "LIMIT 10";

        if(isset($arOrder)){
            $sOrder = '';
            foreach($arOrder as $sField=>$sDirection)
                $sOrder .= $sField." ".$sDirection.",";
            $sOrder .= " `order`.`ID` ASC";
        }
        else{
            $sOrder = "`order`.`ID` ASC";
        }

        $sQuery = "
            SELECT
                `order`.`ID` as `ORDER_ID`,
                `order`.`ADDITIONAL_INFO` as `ORDER_NUM`,
                `user`.`NAME` as `USER_NAME`,
                `user`.`LAST_NAME` as `USER_LAST_NAME`,
                `user`.`EMAIL` as `USER_EMAIL`,
                `order`.`STATUS_ID` as `STATUS_ID`,
                DATE_FORMAT(`order`.`DATE_INSERT`,'%d.%m.%Y %H:%i:%s') as `DATE_INSERT`,
                DATE_FORMAT(`order`.`DATE_UPDATE`,'%d.%m.%Y %H:%i:%s') as `DATE_UPDATE`,
                DATE_FORMAT(`order`.`DATE_STATUS`,'%d.%m.%Y %H:%i:%s') as `DATE_STATUS`,
                `user`.`LOGIN` as `USER_LOGIN`,
                `product`.`VALUE` as `PRODUCT_NAME`,
                `order`.`STORE_ID` as `STORE_ID`,
                `section`.`VALUE` as `SECTION_NAME`,
                `man_name`.`VALUE` as `MANUFACTURER_NAME`,
                `price`.`VALUE_NUM` as `PRICE`
            FROM
                $sFrom
            WHERE
                $sWhere
            $sGroupBy
            ORDER BY
                $sOrder
            $sLimit
        ";
        $res = $DB->Query($sQuery);


//        echo "<pre>";
//        echo $sQuery;
//        die;

        $arOrders = [];
        while($arOrder = $res->Fetch()){
            $arOrder["STORE_NAME"] = $arStores[$arOrder["STORE_ID"]]["TITLE"];
            $arOrder["STATUS_NAME"] = $arStatuses[$arOrder["STATUS_ID"]]["NAME"];
            $arOrders[] = $arOrder;
        }

        return $arOrders;
    }

