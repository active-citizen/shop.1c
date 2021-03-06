<?
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$answer = array("error"=>"");

CModule::IncludeModule("form");

// Узнаём ID нужной формы
$arForm = CForm::GetList(
    $by = "s_sort", 
    $order = 'desc', 
    array("SID" =>  "common_support_feedback")
)->GetNext();


$arForm = CForm::GetBySID("common_support_feedback")->GetNext();

$arFieldIssueType = CFormField::GetBySID("ISSUE_TYPE")->GetNext();
$arAnswerIssueType = CFormAnswer::GetList($arFieldIssueType["ID"])->GetNext();

$arFieldIssueAuthor = CFormField::GetBySID("ISSUE_AUTHOR")->GetNext();
$arAnswerIssueAuthor = CFormAnswer::GetList($arFieldIssueAuthor["ID"])->GetNext();

$arFieldIssueText = CFormField::GetBySID("ISSUE_TEXT")->GetNext();
$arAnswerIssueText = CFormAnswer::GetList($arFieldIssueText["ID"])->GetNext();


$CFormResult = new CFormResult;
if(!$CFormResult->Add($arForm["ID"],array(
    "form_".$arAnswerIssueType["FIELD_TYPE"]."_".$arAnswerIssueType["ID"]      
        =>$DB->ForSql($_REQUEST['type']),
    "form_".$arAnswerIssueAuthor["FIELD_TYPE"]."_".$arAnswerIssueAuthor["ID"]      
        =>$DB->ForSql($_REQUEST['name']),
    "form_".$arAnswerIssueText["FIELD_TYPE"]."_".$arAnswerIssueText["ID"]      
        =>$DB->ForSql($_REQUEST['text']),
))){
    $answer['error'] = $CFormResult->LAST_ERROR;
}
else{
}


echo json_encode($answer);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
