<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои баллы");
?>
<div class="myPointsBox_2">
    <table>
        <tbody>
        <tr>
            <th width="184px">Дата</th>
            <th>Операция</th>
            <th>Баллы</th>
        </tr>
    <?
        CModule::IncludeModule("sale");
        $res = CSaleUserTransact::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
        while($arResult=$res->GetNext()){
            /*
            echo "<pre>";
            print_r($arResult);
            echo "</pre>";
            */
       ?><tr>
            <td><? echo $arResult["TIMESTAMP_X"];?></td>
            <td>
                <h3><? echo $arResult["DEBIT"]=="Y"?"Начисление":"Списание"?></h3>
                <? 
                    switch($arResult["DESCRIPTION"]){
                        case 'MANUAL':
                            echo "Внесено вручную";
                        break;
                        case 'ORDER_PAY':
                            echo 'Списано за заказ №<a href="/order/detail/'.$arResult["ORDER_ID"].'/">'.$arResult["ORDER_ID"]."</a>";
                        break;
                        case 'ORDER_UNPAY':
                            echo 'Отмена заказа №<a href="/order/detail/'.$arResult["ORDER_ID"].'/">'.$arResult["ORDER_ID"]."</a>";
                        break;
                    }
                
                ?>
            </td>
            <td>
                <span data-point="0" class="cr <? echo $arResult["DEBIT"]=="Y"?"actPoint":"spentPoints"?>">
                    <? echo ($arResult["DEBIT"]=="Y"?"":"-").round($arResult["AMOUNT"]);?>
                </span>
            </td>
        </tr><?
        }
    ?>
    </table>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>