<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Отчеты::Кабинет партнёра");
?>
<div class="partners-main">
    <h1>Кабинет партнёра</h1>
    <? include("../menu.php"); ?>
    <? include("menu.php"); ?>

    <?
        switch($_REQUEST["type"]){
            case "stores":
                $type = 'stores';
            break;
            case "tags":
                $type='tags';
            break;
            case "order-doubles":
                $type = 'order-doubles';
            break;
            case "orders-export":
                $type = 'orders-export';
            break;
            default:
                $type = '';
            break;
        }
    ?>

    <? if(
        $type
    ):?>
        <? include("types/".$type.".type.php");?>
    <? else:?>
        <div class="alert alert-error">Некорректный тип отчета</div>
    <? endif?>

    <form method="POST">
    <input type="submit" name="save" value="Применить" class="btn btn-primary"
    style="margin: 10px;">
    <table class="table table-bordered" id="result">
        <tr>
            <th>
                №
            </th>
            <th>
                
            </th>
            <? foreach($arResult["COLS"] as $nColId=>$arCol):?>
            <th class="head">
                <?= $arCol["VALUE"]?>
            </th>
            <? endforeach ?>
        </tr>
        <? $nNum=0;foreach($arResult["ROWS"] as $nRowId=>$arRow):?>
        <tr>
            <th>
                <? $nNum++ ?>
                <?= $nNum ?>
            </th>
            <th class="row">
                <a href="<?= $arRow["URL"]?>" target="_blank">
                <?= $arRow["VALUE"] ?>
                </a>
            </th>
            <? foreach($arResult["COLS"] as $nColId=>$arCol):?>
            <td>
                <?= 
                    $arResult["CELLS"][$nRowId][$nColId]
                    ?
                    $arResult["CELLS"][$nRowId][$nColId]
                    :
                    0
                ?>
            </td>
            <? endforeach ?>
        </tr>
        <? endforeach ?>
    </table>
    </form>


</div>

<style>
    table#result{
        width: 90%;
    }

    table#result td{
        text-align: right;
    }
    table#result th.head {
        transform: rotate(90deg) ;
        height: 350px;
        padding: 0px !important;
    }
    table#result th.row{
    }
</style>
<script>
$( ".form-date" ).datepicker({dateFormat:"dd.mm.yy"});
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

