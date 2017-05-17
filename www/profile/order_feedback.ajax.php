<?
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$answer = array("error"=>"");

CModule::IncludeModule("form");
$arForm = CForm::GetBySID("order_support_feedback")->GetNext();
CEvent::CheckEvents();

$arFieldIssueOrderNum = CFormField::GetBySID("ISSUE_ORDER_NUM")->GetNext();
$arAnswerIssueOrderNum = CFormAnswer::GetList($arFieldIssueOrderNum["ID"])->GetNext();

$arFieldIssueType = CFormField::GetBySID("ISSUE_ORDER_TYPE")->GetNext();
$arAnswerIssueType = CFormAnswer::GetList($arFieldIssueType["ID"])->GetNext();

$arFieldIssueAuthor = CFormField::GetBySID("ISSUE_ORDER_AUTHOR")->GetNext();
$arAnswerIssueAuthor = CFormAnswer::GetList($arFieldIssueAuthor["ID"])->GetNext();

$arFieldIssueText = CFormField::GetBySID("ISSUE_ORDER_TEXT")->GetNext();
$arAnswerIssueText = CFormAnswer::GetList($arFieldIssueText["ID"])->GetNext();

$CFormResult = new CFormResult;
if(!$nResultId = $CFormResult->Add($arForm["ID"],$arFormData = array(
    "form_".$arAnswerIssueType["FIELD_TYPE"]."_".$arAnswerIssueType["ID"]      
        =>$DB->ForSql($_REQUEST['type']),
    "form_".$arAnswerIssueAuthor["FIELD_TYPE"]."_".$arAnswerIssueAuthor["ID"]      
        =>$DB->ForSql($_REQUEST['name']),
    "form_".$arAnswerIssueText["FIELD_TYPE"]."_".$arAnswerIssueText["ID"]      
        =>$DB->ForSql($_REQUEST['text']),
    "form_".$arAnswerIssueOrderNum["FIELD_TYPE"]."_".$arAnswerIssueOrderNum["ID"]      
        =>$DB->ForSql($_REQUEST['order']),
))){
    $answer['error'] = print_r($CFormResult,1).print_r($arFormData, 1).print_r($arForm["ID"], 1);;
}
else{
    if(!$nEvent = CFormResult::Mail($nResultId)){
        global $strError;
        $answer["error"] = $strError;
    }
    else{
        $answer["mail_event_id"] = $nEvent;
    }
}


echo json_encode($answer);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
