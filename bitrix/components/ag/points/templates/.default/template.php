<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="point-dibit-type-menu">
    <a href="<?= $arParams["SELF_FOLDER"]?><?= $arParams["ALL_FOLDER"]?>/" <?if(!$arResult["DEBIT"]){?>class="active"<?}?>>
        <?= $arParams["ALL_TITLE"]?>
    </a>
    <a href="<?= $arParams["SELF_FOLDER"]?><?= $arParams["DEBIT_FOLDER"]?>/" <?if($arResult["DEBIT"]=='Y'){?>class="active"<?}?>>
        <?= $arParams["DEBIT_TITLE"]?>
    </a>
    <a href="<?= $arParams["SELF_FOLDER"]?><?= $arParams["CREDIT_FOLDER"]?>/" <?if($arResult["DEBIT"]=='N'){?>class="active"<?}?>>
        <?= $arParams["CREDIT_TITLE"]?>
    </a>
</div>


<?if ($arParams["SHOW_TOP_PAGINATION"] && count($arResult["PAGES"])>1):?>
    <div class="points_pagination">
        Страницы: 
        <?foreach($arResult["PAGES"] as $offset=>$pagenum):?>
            <? if($arParams["PAGE"]!=$pagenum){?>
                <a href="<?= $arParams["SELF_FOLDER"]?><?
                    switch($arResult["DEBIT"]){
                        case "Y":
                            echo $arParams["DEBIT_FOLDER"];
                        break;
                        case "N":
                            echo $arParams["CREDIT_FOLDER"];
                        break;
                        default:
                            echo "all";
                        break;
                    }    
                ?>/<?= ($offset/$arParams["RECORDS_ON_PAGE"]+1)?>/"><?= $pagenum?></a>
            <? }else{?>
                <a class="active"><?= $pagenum;?></a>
            <? }?>
        <?endforeach;?>
    </div>
<?endif;?>


<div class="myPointsBox_2">
    <table>
        <tbody>
        <tr>
            <th width="184px">Дата</th>
            <th>Операция</th>
            <th>Баллы</th>
        </tr>
        <?foreach($arResult["RECORDS"] as $record):?><tr>
            <td class="date"><? echo $record["TIMESTAMP_X"];?></td>
            <td>
                <h3><? echo $record["DEBIT"]=="Y"?"Начисление":"Списание"?></h3>
                <? 
                    switch($record["DESCRIPTION"]){
                        case 'MANUAL':
                            echo "Внесено вручную";
                        break;
                        case 'ORDER_PAY':
                            echo 'Списано за заказ №<a href="/order/detail/'.$record["ORDER_ID"].'/">'.$record["ORDER_ID"]."</a>";
                        break;
                        case 'ORDER_UNPAY':
                            echo 'Отмена заказа №<a href="/order/detail/'.$record["ORDER_ID"].'/">'.$record["ORDER_ID"]."</a>";
                        break;
                    }
                
                ?>
            </td>
            <td class="balls">
                <span data-point="0" class="cr <? echo $record["DEBIT"]=="Y"?"actPoint":"spentPoints"?>">
                    <? echo ($record["DEBIT"]=="Y"?"":"-").number_format($record["AMOUNT"],0,","," ");?>
                </span>
            </td>
        </tr>
        <?endforeach?>
    </table>
</div>

<?if ($arParams["SHOW_BOTTOM_PAGINATION"] && count($arResult["PAGES"])>1):?>
    <div class="points_pagination">
        Страницы: 
        <?foreach($arResult["PAGES"] as $offset=>$pagenum):?>
            <? if($arParams["PAGE"]!=$pagenum){?>
                <a href="<?= $arParams["SELF_FOLDER"]?><?
                    switch($arResult["DEBIT"]){
                        case "Y":
                            echo $arParams["DEBIT_FOLDER"];
                        break;
                        case "N":
                            echo $arParams["CREDIT_FOLDER"];
                        break;
                        default:
                            echo "all";
                        break;
                    }    
                ?>/<?= ($offset/$arParams["RECORDS_ON_PAGE"]+1)?>/"><?= $pagenum?></a>
            <? }else{?>
                <a class="active"><?= $pagenum;?></a>
            <? }?>
        <?endforeach;?>
    </div>
<?endif;?>

