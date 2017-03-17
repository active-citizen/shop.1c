<?
/**
 * Получение профиля текущего пользователя, может 
 */
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('forum');
CModule::IncludeModule('iblock');

$productId = isset($_GET["productid"])?intval($_GET["productid"]):0;

$arIBlock = CIBlock::GetList(array(),array("CODE"=>"clothes"))->GetNext();
$iblockId = $arIBlock["ID"];
$topicId = 0;
if($arProp = CIBlockElement::GetProperty($iblockId, $productId, array(), array("CODE"=>"FORUM_TOPIC_ID"))->GetNext())
    $topicId = $arProp["VALUE"];
    
$resComments = CForumMessage::GetList(array("POST_DATE"=>"DESC"),array("TOPIC_ID"=>$topicId));
$arIBlock = CIBlock::GetList(array(),array("CODE"=>"marks"))->GetNext();
$iblockId = $arIBlock["ID"];
?>

<? while($arComment = $resComments->GetNext()):?>
<?
// Проверяем наличие оценки

?>
  <div class="ag-shop-card__reviews-item">
    <div class="ag-shop-card__reviews-item-name"><?= $arComment["AUTHOR_NAME"]?></div>
    <?  if($arMark = CIBlockElement::GetList(
            array(), 
            $arField = array(
                "IBLOCK_ID"=>$iblockId,
                "PROPERTY_MARK_USER"=>$arComment["AUTHOR_ID"],
                "PROPERTY_MARK_PRODUCT"=>$productId
            ),
            false,
            array(),
            array("PROPERTY_MARK")
        )->GetNext()):?>
    <div class="ag-shop-card__rating">
        <? for($i=0;$i<round($arMark["PROPERTY_MARK_VALUE"]);$i++):?>
        <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
        <? endfor ?>
        <? for($j=0;$j<5-round($arMark["PROPERTY_MARK_VALUE"]);$j++):?>
        <div class="ag-shop-slider-card__rating-item"></div>
        <? endfor ?>
    </div>
    <? endif ?>
    <p class="ag-shop-card__addititonal-info-text"><?= $arComment["POST_MESSAGE"]?></p>
    <div class="ag-shop-card__reviews-item-date"><?= $arComment["POST_DATE"]?></div>
  </div>
<? endwhile ?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
