<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>



            <!-- Profile {{{-->
            <div class="ag-shop-profile-tabs">
              <div class="ag-shop-profile-tabs__link<? if($arParams["TAB"]=='use'): ?> ag-shop-profile-tabs__link--active<? endif ?>">
                  <a href="?tab=use">Могу использовать</a>
              </div>
              <div class="ag-shop-profile-tabs__link<? if($arParams["TAB"]=='unuse'): ?> ag-shop-profile-tabs__link--active<? endif ?>">
                <a href="?tab=unuse">Уже использовал</a>
              </div>
              <div class="ag-shop-profile-tabs__link<? if($arParams["TAB"]=='all'): ?> ag-shop-profile-tabs__link--active<? endif ?>">
                <a href="?tab=all">Все заказы</a>
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
            <a href="<?= $arParams["SELF_FOLDER"]?>?tab=<?
                switch($arParams["TAB"]){
                    case "unuse":
                        echo 'unuse';
                    break;
                    case "use":
                        echo 'use';
                    break;
                    default:
                        echo "all";
                    break;
                }    
            ?>&page=<?= $pagenum?>"><?= $pagenum?></a>
            </div>
        <? }else{?>
            <div class="ag-shop-profile-tabs__link  ag-shop-profile-tabs__link--active">
                <a class="active"><?= $pagenum;?></a>
            </div>
        <? }?>
    <?endforeach;?>
  </div>
<?endif;?>

            <div class="ag-shop-profile__orders">
              <? foreach($arResult["ORDERS"] as $arOrder):?>
              <div class="ag-shop-profile-order ag-shop-profile-order--<?
              switch($arOrder["STATUS_ID"]){
                case 'N':
                    echo "active";
                break;
                case 'F':
                    echo "done";
                break;
                case 'AG':
                    echo "canceled";
                break;
                case 'AI':
                    echo "annuled";
                break;
              }
              ?>">
                <div class="ag-shop-profile-order__container">
                  <div class="grid grid--bleed grid--justify-space-between">
                    <div class="grid__col-auto">
                      <div class="ag-shop-profile-order__info">
                        <div class="ag-shop-profile-order__status"><?=
                        $arResult["STATUSES"][$arOrder["STATUS_ID"]]["NAME"]
                        ?><? if($arOrder["IN_WORK"] && $arOrder["STATUS_ID"]=='N'):?>(<?= $arOrder["IN_WORK"] ?> <?= get_days($arOrder["IN_WORK"])?>)<? endif ?></div>
                        <div class="ag-shop-profile-order__number">Заказ №<?= $arOrder["ID"]?></div>
                        <div class="ag-shop-profile-order__date">от <?= $arOrder["DATE_SHORT"]?></div>
                      </div>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-profile-order__desktop-controls"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a><a class="ag-shop-profile-order__control" href="#"><span>Отменить заказ</span><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--close"></i></a></div>
                    </div>
                  </div>
                  <? foreach($arOrder["PRODUCTS"] as $arProduct):?>
                  <div class="ag-shop-profile-order__content">
                    <div class="ag-shop-profile-order__image-container"><img class="ag-shop-profile-order__image" src="http://placehold.it/60x60"></div>
                    <div class="ag-shop-profile-order__points"><?= number_format($arProduct["PRICE"]*$arProduct["QUANTITY"],0,',',' ')?> <?= get_points(round($arProduct["PRICE"]*$arProduct["QUANTITY"])) ?></div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__name"><?= $arProduct["NAME"]?></div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__review">
                          <!-- <a href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>оставить отзыв</span></a> -->
                        </div>
                      </div>
                    </div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-end">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__place"><span>Забирать здесь:</span><br class="hide-on-desktop"><a href="#">МФЦ Академический (Москва, Нижний сусальный переулок, 5с5)</a></a></div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__count"><span>количество: <?= $arProduct["QUANTITY"]?>; <?= number_format($arProduct["PRICE"],0,',',' ')?> <?= get_points(round($arProduct["PRICE"])) ?></span></div>
                      </div>
                    </div>
                  </div>
                  <? endforeach ?>
                </div>
                <div class="ag-shop-profile-order__mobile-controls">
                  <div class="grid grid--bleed grid--justify-space-around grid--align-center">
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>Оставить отзыв</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--close"></i><span>Отменить заказ</span></a></div>
                  </div>
                </div>
                <?
                //echo "<pre>";
                //print_r($arOrder);
                //echo "</pre>";
                ?>
              </div>
              <? endforeach ?>
              
              
<?if ($arParams["SHOW_TOP_PAGINATION"] && count($arResult["PAGES"])>1):?>
  <div class="ag-shop-profile-tabs points_pagination">
    <div class="ag-shop-profile-tabs__link  ag-shop-profile-tabs__link--active">
        Страницы: 
    </div>
    <?foreach($arResult["PAGES"] as $offset=>$pagenum):?>
        <? if($arParams["PAGE"]!=$pagenum){?>
            <div class="ag-shop-profile-tabs__link">
            <a href="<?= $arParams["SELF_FOLDER"]?>?tab=<?
                switch($arParams["TAB"]){
                    case "unuse":
                        echo 'unuse';
                    break;
                    case "use":
                        echo 'use';
                    break;
                    default:
                        echo "all";
                    break;
                }    
            ?>&page=<?= $pagenum?>"><?= $pagenum?></a>
            </div>
        <? }else{?>
            <div class="ag-shop-profile-tabs__link  ag-shop-profile-tabs__link--active">
                <a class="active"><?= $pagenum;?></a>
            </div>
        <? }?>
    <?endforeach;?>
  </div>
<?endif;?>
              <hr/>
              <div class="ag-shop-profile-order ag-shop-profile-order--active">
                <div class="ag-shop-profile-order__container">
                  <div class="grid grid--bleed grid--justify-space-between">
                    <div class="grid__col-auto">
                      <div class="ag-shop-profile-order__info">
                        <div class="ag-shop-profile-order__status">Активный (12 дней)</div>
                        <div class="ag-shop-profile-order__number">Заказ №1112</div>
                        <div class="ag-shop-profile-order__date">от 23.11.16</div>
                      </div>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-profile-order__desktop-controls"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a>
                        <div class="ag-shop-profile-order__control"><span>Невозвратный билет</span><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--attention"></i></div>
                      </div>
                    </div>
                  </div>
                  <div class="ag-shop-profile-order__content">
                    <div class="ag-shop-profile-order__image-container"><img class="ag-shop-profile-order__image" src="http://placehold.it/60x60"></div>
                    <div class="ag-shop-profile-order__points">3510 баллов</div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__name">Посещение Центра Современного Искусства МАРС Посещение Центра Современного Искусства МАРС</div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__review"><a href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>оставить отзыв</span></a></div>
                      </div>
                    </div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-end">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__place"><span>Забирать здесь:</span><br class="hide-on-desktop"><a href="#">МФЦ Академический (Москва, Нижний сусальный переулок, 5с5)</a></a></div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__count"><span>количество пар: 2, 1170 баллов</span></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ag-shop-profile-order__mobile-controls">
                  <div class="grid grid--bleed grid--justify-space-around grid--align-center">
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>Оставить отзыв</span></a></div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-profile-order__control"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--attention"></i><span>Невозвратный билет</span></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="ag-shop-profile-order ag-shop-profile-order--canceled">
                <div class="ag-shop-profile-order__container">
                  <div class="grid grid--bleed grid--justify-space-between">
                    <div class="grid__col-auto">
                      <div class="ag-shop-profile-order__info">
                        <div class="ag-shop-profile-order__status">Отменён</div>
                        <div class="ag-shop-profile-order__number">Заказ №1112</div>
                        <div class="ag-shop-profile-order__date">от 23.11.16</div>
                      </div>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-profile-order__desktop-controls"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    </div>
                  </div>
                  <div class="ag-shop-profile-order__content">
                    <div class="ag-shop-profile-order__image-container"><img class="ag-shop-profile-order__image" src="http://placehold.it/60x60"></div>
                    <div class="ag-shop-profile-order__points">3510 баллов</div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__name">Посещение Центра Современного Искусства МАРС Посещение Центра Современного Искусства МАРС</div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__review"><a href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>оставить отзыв</span></a></div>
                      </div>
                    </div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-end">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__place"><span>Забирать здесь:</span><br class="hide-on-desktop"><a href="#">МФЦ Академический (Москва, Нижний сусальный переулок, 5с5)</a></a></div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__count"><span>количество пар: 2, 1170 баллов</span></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ag-shop-profile-order__mobile-controls">
                  <div class="grid grid--bleed grid--justify-space-around grid--align-center">
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>Оставить отзыв</span></a></div>
                  </div>
                </div>
              </div>
              <div class="ag-shop-profile-order ag-shop-profile-order--annuled">
                <div class="ag-shop-profile-order__container">
                  <div class="grid grid--bleed grid--justify-space-between">
                    <div class="grid__col-auto">
                      <div class="ag-shop-profile-order__info">
                        <div class="ag-shop-profile-order__status">Аннулирован (истек срок)</div>
                        <div class="ag-shop-profile-order__number">Заказ №1112</div>
                        <div class="ag-shop-profile-order__date">от 23.11.16</div>
                      </div>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-profile-order__desktop-controls"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    </div>
                  </div>
                  <div class="ag-shop-profile-order__content">
                    <div class="ag-shop-profile-order__image-container"><img class="ag-shop-profile-order__image" src="http://placehold.it/60x60"></div>
                    <div class="ag-shop-profile-order__points">3510 баллов</div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__name">Посещение Центра Современного Искусства МАРС Посещение Центра Современного Искусства МАРС</div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__review"><a href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>оставить отзыв</span></a></div>
                      </div>
                    </div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-end">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__place"><span>Забирать здесь:</span><br class="hide-on-desktop"><a href="#">МФЦ Академический (Москва, Нижний сусальный переулок, 5с5)</a></a></div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__count"><span>количество пар: 2, 1170 баллов</span></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ag-shop-profile-order__mobile-controls">
                  <div class="grid grid--bleed grid--justify-space-around grid--align-center">
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>Оставить отзыв</span></a></div>
                  </div>
                </div>
              </div>
              <div class="ag-shop-profile-order ag-shop-profile-order--done">
                <div class="ag-shop-profile-order__container">
                  <div class="grid grid--bleed grid--justify-space-between">
                    <div class="grid__col-auto">
                      <div class="ag-shop-profile-order__info">
                        <div class="ag-shop-profile-order__status">Выполнен</div>
                        <div class="ag-shop-profile-order__number">Заказ №1112</div>
                        <div class="ag-shop-profile-order__date">от 23.11.16</div>
                      </div>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-profile-order__desktop-controls"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    </div>
                  </div>
                  <div class="ag-shop-profile-order__content">
                    <div class="ag-shop-profile-order__image-container"><img class="ag-shop-profile-order__image" src="http://placehold.it/60x60"></div>
                    <div class="ag-shop-profile-order__points">3510 баллов</div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__name">Посещение Центра Современного Искусства МАРС Посещение Центра Современного Искусства МАРС</div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__review"><a href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>оставить отзыв</span></a></div>
                      </div>
                    </div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-end">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__place"><span>Забирать здесь:</span><br class="hide-on-desktop"><a href="#">МФЦ Академический (Москва, Нижний сусальный переулок, 5с5)</a></a></div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__count"><span>количество пар: 2, 1170 баллов</span></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ag-shop-profile-order__mobile-controls">
                  <div class="grid grid--bleed grid--justify-space-around grid--align-center">
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a></div>
                    <div class="grid__col-shrink"><a class="ag-shop-profile-order__control" href="#"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>Оставить отзыв</span></a></div>
                  </div>
                </div>
              </div>
            </div>
            <!-- }}} Profile-->
