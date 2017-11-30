<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
echo $USER->GetID();
if($USER->GetID()!=5)die;

if(
    isset($_POST["add_balls"]) 
    && isset($_POST["balls"])
    && intval($_POST["balls"])
    && intval($_POST["balls"]<10000)
){

    require_once(
        $_SERVER["DOCUMENT_ROOT"]
            ."/.integration/classes/order.class.php"
    );
 
    $obOrder = new bxOrder();
    $resOrder = $obOrder->addEMPPoints(
        intval($_POST["balls"]),
        htmlspecialchars($_POST["reason"])
    );

    LocalRedirect("/profile/points/");
    die;
}

?>
<form method="POST" style="position:
absolute;top:50%;left:50%;width:200px;margin-left:-100px;margin-top: -50px;
height: 100px;">
    <input type="text" name="balls" value="1000">
    <input type="text" name="reason" value="За всё хорошее"/>
    <input type="submit" name="add_balls" value="Накинуть баллов">
</form>



<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
