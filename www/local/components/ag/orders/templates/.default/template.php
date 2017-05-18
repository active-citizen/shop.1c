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
                case 'AW':
                    echo "canceled";
                break;
                // Брак стилистически аналогичен отмене
                case 'AC':
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
                        ?><? if($arOrder["IN_WORK"] && $arOrder["STATUS_ID"]=='N'):?>(<?= ceil($arOrder["IN_WORK"]) ?> <?= get_days(ceil($arOrder["IN_WORK"]))?>)<? endif ?></div>
                        <div class="ag-shop-profile-order__number">Заказ <?= $arOrder["ADDITIONAL_INFO"]?></div>
                        <div class="ag-shop-profile-order__date">от <?= $arOrder["DATE_SHORT"]?></div>
                      </div>
                    </div>
                    <div class="grid__col-shrink">
                      <div class="ag-shop-profile-order__desktop-controls">
                          <a class="ag-shop-profile-order__control" href="#" onclick="return printOrder(<?= $arOrder["ID"]?>);">
                              <i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--print"></i><span>Распечатать</span>
                          </a>
                          <a class="ag-shop-profile-order__control" href="#"
                          onclick="return showOrdersFeedbackForm('<?=
                          $arOrder["ADDITIONAL_INFO"]?>');"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--letter"></i><span>Связаться с администрацией</span></a>
                          <? if(
                            $arOrder["STATUS_ID"]=='AG'
                            ||
                            preg_match("#^\d+$#",$arOrder["ADDITIONAL_INFO"])
                          ): ?>
                          <? elseif(
                            $arOrder["STATUS_ID"]=='N' 
                            && $arOrder["PRODUCTS"][0]["CANCEL_ABILITY"]
                          ):?>
                          <a class="ag-shop-profile-order__control" onclick="return orderCancel(<?= $arOrder["ID"]?>,this);" href="#"><span>Отменить заказ</span><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--close"></i></a>
                          <? else:?>
                          <div class="ag-shop-profile-order__control"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--attention"></i><span>Отмена невозможна</span></div>
                            <? endif ?>
                      </div>
                    </div>
                  </div>
                  <? foreach($arOrder["PRODUCTS"] as $arProduct):?>
                  <div class="ag-shop-profile-order__content">
                    <div class="ag-shop-profile-order__image-container" style="background-image: url('<?= $arProduct["PIC_PATH"];?>')">
                    </div>
                    <div class="ag-shop-profile-order__points"><?= number_format($arProduct["PRICE"]*$arProduct["QUANTITY"],0,',',' ')?> <?= get_points(round($arProduct["PRICE"]*$arProduct["QUANTITY"])) ?></div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__name"><?= html_entity_decode($arProduct["NAME"])?></div>
                      </div>
                      <div class="grid__col-shrink">
                        <div class="ag-shop-profile-order__review">
                          <a href="<?= $arProduct["CATALOG_URL"]?>#review"><i class="ag-shop-profile-order__icon ag-shop-profile-order__icon--write"></i><span>оставить отзыв</span></a>
                        </div>
                      </div>
                    </div>
                    <div class="grid grid--bleed grid--justify-space-between grid--align-end">
                      <div class="grid__col-auto">
                        <div class="ag-shop-profile-order__place">
                            <? if(trim($arOrder["STORE_INFO"]["TITLE"])){?>
                                <span>Забирать здесь:</span>
                                <br class="hide-on-desktop">
                                <? if(trim($arOrder["STORE_INFO"]["ADDRESS"])){?>
                                    <a href="/rules/stores/#<?= $arOrder["STORE_INFO"]["ID"] ?>">
                                        <?= $arOrder["STORE_INFO"]["TITLE"]?>
                                        <? if(trim($arOrder["STORE_INFO"]["ADDRESS"])){?>
                                        (<?= $arOrder["STORE_INFO"]["ADDRESS"]?>)
                                        <? }?>
                                    </a>
                                <? }else{ ?>
                                    <?= $arOrder["STORE_INFO"]["TITLE"]?>
                                <? } ?>
                            <? } ?>
                        </div>
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
              </div>
              <? endforeach ?>
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


    <div class="ag-shop-modal-wrap" id="orders-feedback-form" style="display:none">
      <div class="ag-shop-modal">
        <div class="ag-shop-modal__container">
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__select-wrap">
              <select class="ag-shop-modal__select" id="order-feedback-form-type">
                <option disabled selected>Тип обращения</option>
                <option>Тип 1</option>
                <option>Тип 2</option>
                <option>Тип 3</option>
              </select>
            </div>
          </div>
          <div class="ag-shop-modal__row">
            <label>
              <div class="ag-shop-modal__label">Номер заказа:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="order-feedback-form-ordernum">
            </div>
            </label>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">От:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="order-feedback-form-fio"><?
                $arUser = CUser::GetById(CUSER::GEtID())->GetNext();
                echo ($arUser["NAME"] || $arUser["LAST_NAME"]?$arUser["NAME"]." ".$arUser["LAST_NAME"]:$arUser["LOGIN"]);
            ?></div>
          </div>
          <div class="ag-shop-modal__row">
            <label>
              <div class="ag-shop-modal__label">Сообщение:</div>
              <textarea class="ag-shop-modal__textinput" placeholder="Что вас волнует?"  id="order-feedback-form-text"></textarea>
            </label>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__buttons-wrap">
              <button class="ag-shop-modal__button" type="button" onclick="return sendOrdersFeedbackForm();">Отправить</button>
              <button class="ag-shop-modal__button ag-shop-modal__button--cancel" type="button" onclick="return hideOrdersFeedbackForm();">Отмена</button>
            </div>
          </div>
        </div>
      </div>
    </div>



            <!-- }}} Profile-->
