<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои баллы");


if($_SERVER["REQUEST_URI"]=='/points/'){
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/active-citizen-bridge.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/point.class.php");


    $agBrige = new ActiveCitizenBridge;
    $bxUser = new bxUser;


    // Загружаем историю начисления баллов
    $session_id = $bxUser->getEMPSessionId();
    
    $args = array(
        "session_id"     =>  $session_id,
        "token"     =>  "ag_uat_token3"
    );
    $agBrige->setMethod('pointsHistory');
    $agBrige->setMode('emp');
    $agBrige->setArguments($args);
    $answer["errors"] = $agBrige->getErrors();
    $profile = array();
    if(!$answer["errors"] && !$history = $agBrige->exec()){
        $answer["errors"] = array_merge($answer["errors"],$agBrige->getErrors());
    }
        
    if(isset($history["errorMessage"]) && $history["errorMessage"]){
        $answer["errors"][] = $history["errorMessage"];
    }else{
        $bxPoint = new bxPoint;
        $bxPoint->updatePoints($history["result"]['history'], CUser::GetID());
    }   
    LocalRedirect("/points/all/");
    die;
}

?>

<?$APPLICATION->IncludeComponent(
    "ag:points", 
    "",
    array(
        "ALL_TITLE"         =>  "Все начисления и списания",
        "SELF_FOLDER"       =>  "/points/",
        "ALL_FOLDER"        =>  "all",
        "DEBIT_FOLDER"      =>  "debit",
        "CREDIT_FOLDER"     =>  "credit",
        "RECORDS_ON_PAGE"   =>  30
    )
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
