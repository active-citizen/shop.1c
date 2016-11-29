<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = array(
    "PARAMETERS" => array(
        "ALL_TITLE"         =>  array(
            "NAME"      =>  "Заголовок для полного списка транзакций",
            "TYPE"      =>  "STRING",
            "DEFAULT"   =>  "Все начисления и списания"
        ),
        "DEBIT_TITLE"   =>  array(
            "NAME"      =>  "Заголовок для начислений",
            "TYPE"      =>  "STRING",
            "DEFAULT"   =>  "Все начисления"
        ),
        "CREDIT_TITLE"  =>  array(
            "NAME"      =>  "Заголовок для списаний",
            "TYPE"      =>  "STRING",
            "DEFAULT"   =>  "Все списания"
        ),
    )
);
?>