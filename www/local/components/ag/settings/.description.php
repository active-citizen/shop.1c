<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); 
$arComponentDescription = array(    
    "NAME" => GetMessage("Текущая дата"),
    "DESCRIPTION" => GetMessage("Выводим текущую дату"),
    "PATH" => array(
        "ID" => "dv_components",
        "CHILD" => array(
            "ID" => "curdate",
            "NAME" => "Текущая дата"
        )
    ),
    "ICON" => "/images/icon.gif",
);
?>