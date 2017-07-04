<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Запросы изменения статуса");


$nRecordsOnPage = 30;

$nUserId = 0;
if(isset($_REQUEST["USER_ID"]) && intval($_REQUEST["USER_ID"]))
    $nUserId = intval($_REQUEST["USER_ID"]);


$arFilter = array(
    "GROUPS_ID"=>array(
        PARTNERS_GROUP_ID,
        OPERATORS_GROUP_ID
    )
);

$sOrder = "last_name";
$sSort = 'asc';

$resUsers = CUser::GetList(
    ($by=$sOrder), ($order=$sSort),
    $arFilter
);
$arUsers = array();
while($arUser = $resUsers->Fetch()){
    $arUsers[$arUser["ID"]] = $arUser;
}

$resStatuses = CSaleStatus::GetList();
$arStatuses = array();
while($arStatus = $resStatuses->Fetch()){
    $arStatuses[$arStatus["ID"]] = $arStatus;
}

$arFilter = array();
$arFilter["TYPE"] = "ORDER_ZNI";
if($nUserId) $arFilter["USER_ID"] = $nUserId;

$resHistory = CSaleOrderChange::GetList(
    array("ID"=>"DESC"),
    $arFilter,
    false,
    array(
        "iNumPage"=>
            $_REQUEST["PAGEN_1"] && intval($_REQUEST["PAGEN_1"])>1
            ?
            intval($_REQUEST["PAGEN_1"])
            :
            1,
        "nPageSize" =>  $nRecordsOnPage
    )

);
$arHistory = array();
while($arHistoryItem = $resHistory->Fetch()){
    $arOrder = CSaleOrder::GetList(
        array(),
        array("ID"=>$arHistoryItem["ORDER_ID"]),
        false,
        array("nTopCount"=>1),
        array("ADDITIONAL_INFO","USER_ID","STATUS_ID")
    )->Fetch();

    $arUser = CUser::GetByID($arOrder["USER_ID"])->Fetch();

    $arHistoryItem["USER"] = $arUser;

    $arInfo = unserialize($arHistoryItem["DATA"]);
    $arHistoryItem["OLD_STATUS"] = $arInfo["OLD_STATUS_ID"];
    $arHistoryItem["NEW_STATUS"] = $arInfo["STATUS_ID"];
    $arHistoryItem["STATUS_ID"] = $arOrder["STATUS_ID"];
    $arHistoryItem["ORDER_NUM"] = $arOrder['ADDITIONAL_INFO'];
    $arHistory[] = $arHistoryItem;
}

?>
<div class="partners-main">
    <h1>Запросы на изменение статусов</h1>
    <? include("../menu.php"); ?>

    <form name="users">
        <select class="form-control" name="USER_ID"
        onchange="document.forms.users.submit();">
            <option value="">Все</option>
            <? foreach($arUsers as $arUser):?>
                <option value="<?= $arUser["ID"]?>" <? 
                    if($arUser["ID"]==$nUserId)echo "selected";
                ?>>
                    <?= $arUser["LAST_NAME"]?> 
                    <?= $arUser["NAME"]?>
                </option>
            <? endforeach ?>
        </select>
    </form>

    <table class="table">
        <tr>
            <th style="width: 150px;">
                Дата
            </th>
            <th style="width: 100px">
                Заказ
            </th>
            <th>
                Оператор
            </th>
            <th>
                Гражданин
            </th>
            <th style="width:100px;">
                Телефон
            </th>
            <th style="width: 100px">
                Новый статус
            </th>
            <th style="width: 100px">
                Старый статус
            </th>
            <th style="width: 100px">
                Текущий статус
            </th>
            <th style="width: 150px">
                Действия
            </th>
        </tr>
        <? foreach($arHistory as $arHistoryItem):?>
        <tr 
        <? if($arHistoryItem["NEW_STATUS"]!=$arHistoryItem["STATUS_ID"]):?>
            style="background-color: red;"
        <? endif ?>>
            <td class="date">
                <?= $arHistoryItem["DATE_CREATE"]?>
            </td>
            <td class="number">
                <a href="/partners/orders/<?= $arHistoryItem["ORDER_ID"]?>/"
                target="_blank"
                >
                    <?= $arHistoryItem["ORDER_NUM"]?>
                </a>
            </td>
            <td class="user">
                <?= $arUsers[$arHistoryItem["USER_ID"]]["LAST_NAME"]?> 
                <?= $arUsers[$arHistoryItem["USER_ID"]]["NAME"]?> 
            </td>
            <td class="user">
                <?= $arHistoryItem["USER"]["LAST_NAME"]?> 
                <?= $arHistoryItem["USER"]["NAME"]?> 
            </td>
            <td class="number">
                <?= $arHistoryItem["USER"]["PERSONAL_PHONE"]?> 
            </td>
            <td class="status" style="color:<?= 
                $arStatuses[$arHistoryItem["NEW_STATUS"]]["COLOR"]?>">
                <?= $arStatuses[$arHistoryItem["NEW_STATUS"]]["NAME"]?> 
            </td>
            <td class="status" style="color:<?= 
                $arStatuses[$arHistoryItem["OLD_STATUS"]]["COLOR"]?>">
                <?= $arStatuses[$arHistoryItem["OLD_STATUS"]]["NAME"]?> 
            </td>
            <td style="color:<?= 
                $arStatuses[$arHistoryItem["STATUS_ID"]]["COLOR"]?>">
                <?= $arStatuses[$arHistoryItem["STATUS_ID"]]["NAME"]?> 
            </td>
            <td class="actions">
                <a href="/partners/logs/?query=<?= $arHistoryItem["ORDER_NUM"]?>"
                target="_blank"
                >
                    <span class="glyphicon glyphicon-eye-open"></span>Логи
                </a>
            </td>
        </tr>
        <? endforeach ?>
    </table>
<?
    echo  $resHistory->GetPageNavStringEx($navComponentObject,
    'Заказы', '', 'Y');
?>

</div>

<style>
.number{
    text-align: right;
}
.date{
    text-align: right;
}

</style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
