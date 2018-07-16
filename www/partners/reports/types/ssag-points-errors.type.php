<?
/**
    Формирование данных по складским остаткам разных товаров
*/
require_once(
    $_SERVER["DOCUMENT_ROOT"]
    ."/local/libs/classes/CAGShop/CDB/CDB.class.php"
);
use AGShop\DB as DB;
$CDB = new \DB\CDB;
$sQuery = "
    SELECT
        DATE_FORMAT(`CTIME`,'%d.%m.%Y %H:%i:%s') as `CTIME`,
        `REQUEST` as `REQUEST`,
        `ANSWER` as `ANSWER`
    FROM
        `int_ssag_errorlog`
    ORDER BY
        `ID` DESC
    LIMIT
        30;
";

$resResult = $CDB->sqlQuery($sQuery);

$arLogs = [];
$arCols = [
    "1"=>["VALUE"=>"Заказ"],
    "2"=>["VALUE"=>"Код ошибки"],
    "3"=>["VALUE"=>"Текст ошибки"],
    "4"=>["VALUE"=>"AGID"],
    "5"=>["VALUE"=>"Подробности"]
];

$arLogs = [];
$nNum = 1;
while($arLog = $resResult->Fetch()){
    $objRequest = json_decode(stripslashes($arLog["REQUEST"]));
    $arRequest = json_decode(json_encode((array)$objRequest), TRUE);
    $objAnswer = json_decode(stripslashes($arLog["ANSWER"]));
    $arAnswer = json_decode(json_encode((array)$objAnswer), TRUE);
    $nOrder = 0;
    if(preg_match("#\-(\d+)#",$arRequest["title"],$m)){
        $nOrder = $m[1];
    }
    
    $arRows[$nNum] = [
        "VALUE"=>$arLog["CTIME"],
        "URL"=>"/partner/orders/$nOrder/"
    ];
   
    $arLogs[$nNum] = [
        "1"=>'<a target="_blank" href="/partners/orders/'.$nOrder.'/">Б-'
            .$nOrder.'</a>',
        "2"=>$arAnswer["errorCode"],
        "3"=>$arAnswer["errorMessage"],
        "4"=>$arRequest["ag_id"],
        "5"=>"<pre>".print_r($arRequest,1).print_r($arAnswer,1)."</pre>"
    ];
    
    $nNum++;
}

// Результат для рисования таблицы
$arResult = array(
    "ROWS"=>$arRows,
    "COLS"=>$arCols,
    "CELLS"=>$arLogs
);

