<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$RU = $_SERVER["REQUEST_URI"];
// Значения по умолчанию

if(!isset($arParams["ALL_TITLE"]))$arParams["ALL_TITLE"] = "Все начисления и списания";
if(!isset($arParams["DEBIT_TITLE"]))$arParams["DEBIT_TITLE"] = "Все начисления";
if(!isset($arParams["CREDIT_TITLE"]))$arParams["CREDIT_TITLE"] = "Все списания";

if(!isset($arParams["SHOW_TOP_PAGINATION"]))$arParams["SHOW_TOP_PAGINATION"] = 1;
if(!isset($arParams["SHOW_BOTTOM_PAGINATION"]))$arParams["SHOW_BOTTOM_PAGINATION"] = 1;

if(!isset($arParams["ALL_FOLDER"]))$arParams["ALL_FOLDER"] = "all";
if(!isset($arParams["DEBIT_FOLDER"]))$arParams["DEBIT_FOLDER"] = "debit";
if(!isset($arParams["CREDIT_FOLDER"]))$arParams["CREDIT_FOLDER"] = "credit";

if(!isset($arParams["SELF_FOLDER"]))$arParams["CREDIT_FOLDER"] = "/points/";
if(!isset($arParams["USER_ID"]))$arParams["USER_ID"] = CUser::GetID();
if(!isset($arParams["SORT"]))$arParams["SORT"] = array("TIMESTAMP_X"=>"DESC");
if(!isset($arParams["RECORDS_ON_PAGE"]))$arParams["RECORDS_ON_PAGE"] = 10;
if(!isset($arParams["PAGE"]))$arParams["PAGE"] = 1;
if(!isset($arParams["PAGE_BLOCK_SIZE"]))$arParams["PAGE_BLOCK_SIZE"] = 10;

$arResult["DEBIT"] = '';
if(preg_match("#^".$arParams["SELF_FOLDER"].$arParams["DEBIT_FOLDER"]."/#",$RU))
    $arResult["DEBIT"] = 'Y';
if(preg_match("#^".$arParams["SELF_FOLDER"].$arParams["CREDIT_FOLDER"]."/#",$RU))
    $arResult["DEBIT"] = 'N';

if(preg_match("#^".$arParams["SELF_FOLDER"].".*?/(\d+)/#",$RU,$m))
    $arParams["PAGE"] = $m[1];

CModule::IncludeModule("sale");
$arFilter = array();
$arFilter["USER_ID"] = $arParams["USER_ID"];
$arPages = array("nPageSize"=>$arParams["RECORDS_ON_PAGE"], "iNumPage"=>$arParams["PAGE"]);
if($arResult["DEBIT"])$arFilter["DEBIT"] = $arResult["DEBIT"];

// Получение общего числа результатов
$res = CSaleUserTransact::GetList($arParams["SORT"],$arFilter,false);
$total_pages = $res->SelectedRowsCount();

// Получение записей с текущей страницы
$res = CSaleUserTransact::GetList($arParams["SORT"],$arFilter,false,$arPages);

// Получение массива пагинации
$arResult["PAGES"] = get_pages_list(
    $total_pages,
    ($arParams["PAGE"]-1)*$arParams["RECORDS_ON_PAGE"],
    $arParams["RECORDS_ON_PAGE"],
    $arParams["PAGE_BLOCK_SIZE"]
);


$arResult["RECORDS"] = array();
while($arResult["RECORDS"][] = $res->GetNext());
    
$this->IncludeComponentTemplate();


/**
    Формирование массива страниц
    возвращает массив страниц вида
*/
function get_pages_list(
    $total,             //!< общее число записей
    $offset=0,          //!< номер рекущей страницы(начиная с 1)
    $perpage=10,        //!< число записей на страницу
    $blocksize = 10     //!< размер блока сраниц
){
    if(!intval($perpage))$perpage = 10;
    
    $page = floor($offset/$perpage)+1;
    $page = intval($page) && $page>0?$page:1;
    $total = intval($total) && $total>0?$total:1;
    $perpage = intval($perpage) && $perpage>0?$perpage:10;
    $blocksize = intval($blocksize) && $blocksize>0?$blocksize:10;
    
    // Номер блока страниц
    $blocknum = floor(($page-1)/$blocksize + 1);
    // Определение общего количества страниц
    $total_pages = floor(($total-1)/$perpage + 1);
    // Определение общего количества блоков
    $total_blocks = floor(($total_pages-1)/$blocksize + 1);
    
    $result = array();
    if($blocknum>1){
        $result[0] = '1';
        $result[($blocknum-2)*$blocksize*$perpage] = '..';
    }
    for($i=($blocknum-1)*$blocksize+1;$i<=$blocknum*$blocksize && $i<=$total_pages;$i++){
        $result[($i-1)*$perpage] = $i;
    }
    if($blocknum*$blocksize<$total_pages)$result[($blocknum*$blocksize)*$perpage] = '..';
    if($blocknum*$blocksize<$total_pages)$result[($total_pages-1)*$perpage] = $total_pages;
    
    
    return $result;
    
}
