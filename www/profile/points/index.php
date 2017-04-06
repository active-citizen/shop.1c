<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои баллы");


if($_SERVER["REQUEST_URI"]=='/profile/points/'){
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/active-citizen-bridge.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/user.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/.integration/classes/point.class.php");

    $agBrige = new ActiveCitizenBridge;
    $bxUser = new bxUser;


    // Загружаем историю начисления баллов
    $session_id = $bxUser->getEMPSessionId();
    
    $args = array(
        "session_id"     =>  $session_id,
        "token"     =>  $EMP_TOKENS[CONTOUR]
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
        $bxPoint->updatePoints($history["result"], CUser::GetID());
   }   

}
    include(dirname(__FILE__)."/../menu.php");

?>
        <div class="ag-shop-content">
          <div class="ag-shop-content__limited-container">
            <!-- Profile {{{-->


<?$APPLICATION->IncludeComponent(
    "ag:points", 
    "",
    array(
        "ALL_TITLE"         =>  "Все начисления и списания",
        "SELF_FOLDER"       =>  "/profile/points/",
        "ALL_FOLDER"        =>  "all",
        "DEBIT_FOLDER"      =>  "debit",
        "CREDIT_FOLDER"     =>  "credit",
        "RECORDS_ON_PAGE"   =>  30
    )
);?>

            <!-- }}} Profile-->
          </div>
        </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
