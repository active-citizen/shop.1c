<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>



            <!-- Profile {{{-->
            <div class="ag-shop-profile-tabs">
              <div class="ag-shop-profile-tabs__link<?if(!$arResult["DEBIT"]){?>  ag-shop-profile-tabs__link--active<?}?>">
                <a href="<?= $arParams["SELF_FOLDER"]?><?= $arParams["ALL_FOLDER"]?>/">Все</a>
              </div>
              <div class="ag-shop-profile-tabs__link<?if($arResult["DEBIT"]=='Y'){?>  ag-shop-profile-tabs__link--active<?}?>">
                <a href="<?= $arParams["SELF_FOLDER"]?><?= $arParams["DEBIT_FOLDER"]?>/">Начисления</a>
              </div>
              <div class="ag-shop-profile-tabs__link<?if($arResult["DEBIT"]=='N'){?>  ag-shop-profile-tabs__link--active<?}?>">
                <a href="<?= $arParams["SELF_FOLDER"]?><?= $arParams["CREDIT_FOLDER"]?>/">Списания</a>
              </div>
            </div>
                
<?if ($arParams["SHOW_TOP_PAGINATION"] && count($arResult["PAGES"])>1):?>
  <div class="ag-shop-profile-tabs points_pagination">
    <div class="ag-shop-profile-tabs__link  ag-shop-profile-tabs__link--active">
        Страницы: 
    </div>
    <?foreach($arResult["PAGES"] as $offset=>$pagenum):?>
        <? if($arParams["PAGE"]!=$pagenum){?>
            <div class="ag-shop-profile-tabs__link">
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
            </div>
        <? }else{?>
            <div class="ag-shop-profile-tabs__link  ag-shop-profile-tabs__link--active">
                <a class="active"><?= $pagenum;?></a>
            </div>
        <? }?>
    <?endforeach;?>
  </div>
<?endif;?>

            <div class="ag-shop-profile-points">
              <div class="ag-shop-profile-points__header">
                <table>
                  <tr>
                    <td>Дата</td>
                    <td>Операция</td>
                    <td>Баллы</td>
                  </tr>
                </table>
              </div>
              <div class="ag-shop-profile-points__table">
                <table>
                <?foreach($arResult["RECORDS"] as $record):?><tr>
                  <tr class="ag-shop-profile-points__row<? if($record["DEBIT"]=="Y"){?> ag-shop-profile-points__row--add<? }else{?> ag-shop-profile-points__row--sub<?}?>">
                    <td data-label="Дата">
                      <?
                        
                        $tmp = date_parse($record["TRANSACT_DATE"]);
                        $timestamp = mktime(
                            $tmp["hour"],$tmp["minute"],$tmp["second"],
                            $tmp["month"],$tmp["day"],$tmp["year"]
                        );
                        $date = date("d.m.Y",$timestamp);
                        $time = date("H:i",$timestamp);
                      ?>
                      <div class="ag-shop-profile-points__data"><?= $date;?>
                        <div class="ag-shop-profile-points__time"><?= $time;?></div>
                      </div>
                    </td>
                    <td data-label="Операция">
                      <div class="ag-shop-profile-points__data">
                        <?
                            switch($record["DESCRIPTION"]){
                                case 'MANUAL':
                                    echo "Внесено вручную";
                                break;
                                case 'ORDER_PAY':
                                    echo 'Списано за заказ Б-'.$record["ORDER_ID"]."";
                                break;
                                case 'ORDER_UNPAY':
                                    echo 'Отмена заказа Б-'.$record["ORDER_ID"]."";
                                break;
                                default:
                                    echo $record["DESCRIPTION"];
                                break;
                            }
                        ?>
                      </div>
                    </td>
                    <td data-label="Баллы">
                      <div class="ag-shop-profile-points__data">
                        <? echo ($record["DEBIT"]=="Y"?"":"-").number_format($record["AMOUNT"],0,","," ");?>
                      </div>
                    </td>
                  </tr>
                <?endforeach?>


                </table>
              </div>


            </div>
<?if ($arParams["SHOW_TOP_PAGINATION"] && count($arResult["PAGES"])>1):?>
  <div class="ag-shop-profile-tabs points_pagination">
    <div class="ag-shop-profile-tabs__link  ag-shop-profile-tabs__link--active">
        Страницы: 
    </div>
    <?foreach($arResult["PAGES"] as $offset=>$pagenum):?>
        <? if($arParams["PAGE"]!=$pagenum){?>
            <div class="ag-shop-profile-tabs__link">
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
            </div>
        <? }else{?>
            <div class="ag-shop-profile-tabs__link  ag-shop-profile-tabs__link--active">
                <a class="active"><?= $pagenum;?></a>
            </div>
        <? }?>
    <?endforeach;?>
  </div>
<?endif;?>

