<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>

<?
$arResult["USER_INFO"]["UF_USER_AG_STATUS"] = 'Активный гражданин';
$stopMonLimit = 0;
if(
    $arResult["CATALOG_ITEM"]["PROPERTIES"]["MON_LIMIT"][0]["VALUE"] &&
    $arResult["MON_ORDERS"]
        >=
    $arResult["CATALOG_ITEM"]["PROPERTIES"]["MON_LIMIT"][0]["VALUE"]
)$stopMonLimit =
    $arResult["CATALOG_ITEM"]["PROPERTIES"]["MON_LIMIT"][0]["VALUE"];
?>
        <? if(
            $arResult["CATALOG_ITEM"]["ACTIVE"]=='N' 
            ||
            $arResult["HIDE_ON_DATE"]
        ):?>
            <div class="ag-shop-modal__alert">
                <i class="ag-shop-icon ag-shop-icon--attention"></i>
                <span>Поощрение недоступно (снято с реализации)
                </span>
            </div>
        <? elseif(
            !$arResult["TotalAmount"] && 
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["HIDE_IF_ABSENT"]
                [0]["VALUE_ENUM"]=='да'
        ):?>
            <div class="ag-shop-modal__alert">
                <i class="ag-shop-icon ag-shop-icon--attention"></i>
                <span>Поощрение недоступно (исчерпание остатков)
                </span>
            </div>
        <? elseif(
            isset($arResult["OFFERS"][0]) 
            && $arResult["CATALOG_ITEM"]["ACTIVE"]=='Y'
        ):?>
            <script>
                var totalOfferId = <?= $arResult["OFFERS"][0]["ID"]?>;
                var totalStoreId = <? 
                    foreach($arResult["OFFERS_JSON"] as $offer){
                        foreach($offer["STORAGES"] as $storeId=>$store){
                            echo $storeId;break;
                        };
                        break;
                    }?>;
                var accountSum=<?= 
                    round($arResult["ACCOUNT"]["CURRENT_BUDGET"])
                ?>;
                var offerCounts = <? 
                    foreach(
                        $arResult["OFFERS"][0]["STORAGES"] 
                        as 
                        $storageId=>$storageCount
                    ){
                        $arResult["OFFERS"][0]["STORAGES"];break;
                    }
                    echo $storageCount;
                ?>;
                var arOffers=<?=json_encode($arResult["OFFERS_JSON"])?>;
                var arStorages = <?= json_encode($arResult["STORAGES"])?>;
                
            </script>
        
            <div class="ag-shop-card">
            <? if($stopMonLimit):?>
              <div class="ag-shop-card__container">
                <div class="ag-shop-card__requirements">
                    Ваш месячный лимит заказов данного поощрения исчерпан.
                    Ближайшая возможная дата заказа: 
                    <b><?= $arResult["NEXT_ORDER"] ?>.</b>
                </div>
              </div>
            <? elseif(
                $arResult["ACCOUNT"]["CURRENT_BUDGET"] 
                < 
                $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
                &&
                CUser::IsAuthorized()
            ): ?>
              <div class="ag-shop-card__container">
                <div class="ag-shop-card__requirements">
                    Для заказа данного поощрения необходимо набрать 
                        <?= 
                            number_format(
                                $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,","," "
                            )
                        ?> 
                        <?= 
                            get_points(number_format(
                                $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],
                                0,","," ")
                            )
                        ?>.
                </div>
              </div>
            <? elseif(
                !trim(
                    $arResult["USER_INFO"]["UF_USER_AG_STATUS"]
                )
                &&
                !trim(
                    $arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"]
                    [0]["VALUE"]
                )
            ):?>
              <div class="ag-shop-card__container">
                <div class="ag-shop-card__requirements">
                    Обращаем Ваше внимание, что заказать данное поощрение вы
                    сможете только после получения статуса &laquo;Активный
                    гражданин&raquo;. Статус присваивается пользователям,
                    набравшим <?= $arParams["ALL_POINTS_LIMIT"]?> баллов
                </div>
              </div>
            <? elseif(
                !CUser::IsAuthorized()
            ): ?>
              <div class="ag-shop-card__container">
                <div class="ag-shop-card__requirements">
                    Для заказа данного поощрения необходимо 
                    <a href="http://ag.mos.ru/">авторизоваться</a>
                </div>
              </div>
            <? endif ?>
              <div class="grid grid--bleed">
                <div class="grid__col-12 grid__col-md-shrink">
                  <div class="ag-shop-card__left-column">
                    <div class="ag-shop-card__image-block">
                      <div class="ag-shop-card__image-wrap">
                        <!-- для темного фона добавить: ag-shop-item-card--dark -->
                        <div class="ag-shop-card__image-container" style="background-image: url(<?= 
                            $arResult["OFFERS"][0]["PROPERTIES"]["MORE_PHOTO"][0]["FILE_PATH"]
                          ?>)">
                          <div class="ag-shop-card__map" style="display:none"></div>
                          <div class="ag-shop-card__image"></div>
                          <div class="ag-shop-card__image-info">
                            <div class="ag-shop-card__image-points">
                              <div class="ag-shop-item-card__points-count"><?= number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,","," ")?></div>
                              <div class="ag-shop-item-card__points-text"><?=
                              get_points(number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,","," "))?></div>
                            </div>
                            
                            <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["NEWPRODUCT"][0]["VALUE_ENUM"]=='да'):?>
                            <div class="ag-shop-card__image-badges"><img class="ag-shop-item-card__badge" src="/local/assets/images/badge__new.png"></div>
                            <? endif ?>

                            <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["SALELEADER"][0]["VALUE_ENUM"]=='да'):?>
                            <div class="ag-shop-card__image-badges"><img class="ag-shop-item-card__badge" src="/local/assets/images/badge__hit.png"></div>
                            <? endif ?>

                            <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["SPECIALOFFER"][0]["VALUE_ENUM"]=='да'):?>
                            <div class="ag-shop-card__image-badges"><img class="ag-shop-item-card__badge" src="/local/assets/images/badge__sale.png"></div>
                            <? endif ?>
                            
                            
                          </div>
                          <button class="ag-shop-item-card__likes" type="button">
                            <div class="ag-shop-item-card__likes-icon<?if($arResult["MYWISH"]):?> wish-on<? endif ?>"
                            productId="<?= $arResult["CATALOG_ITEM"]["ID"]?>"
                            <? if($USER->IsAuthorized() && !$arResult["MARK"]):?>
                            onclick="return mywish(this)"
                            <? endif ?>
                            ></div>
                            <div class="ag-shop-item-card__likes-count" id="wishid<?= $arResult["CATALOG_ITEM"]["ID"]?>"><?= $arResult["WISHES"];?></div>
                          </button>
                        </div>
                        <div class="ag-shop-card__previews-container">
                        <? foreach($arResult["OFFERS"][0]["PROPERTIES"]["MORE_PHOTO"] as $key=>$morePhoto):?>
                          <div class="ag-shop-card__preview<?if(!$key):?> ag-shop-card__preview--active<? endif ?>" style="background-image: url(<?= 
                          $morePhoto["FILE_PATH"]
                          ?>);" rel="<?= $morePhoto["FILE_PATH"];?>"></div>
                        <? endforeach ?>
                        </div>
                      </div>
                    </div>
                    <div class="ag-shop-card__container">
                      <div class="ag-shop-card__header ag-shop-card__header--mobile">
                        <h2 class="ag-shop-card__header-title"><?= $arResult["OFFERS"][0]["NAME"]?></h2>
                        <? /* if($arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]):?>
                        <div class="ag-shop-card__header-code">Артикул: <strong><?= 
                            $arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]
                        ?></strong></div>
                        <? endif */ ?>
                      </div>
                      <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                        <div class="grid__col-12 grid__col-md-shrink">
                          <div class="ag-shop-card__rating">
                            <? if(0)for($i=0;$i<round($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING"][0]["VALUE"]);$i++):?>
                            <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
                            <? endfor ?>
                            <? if(0)for($j=0;$j<5-round($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING"][0]["VALUE"]);$j++):?>
                            <div class="ag-shop-slider-card__rating-item"></div>
                            <? endfor ?>
                              
                          </div>
                        </div>
                        <div class="grid__col-12 grid__col-md-shrink">
                          <div class="ag-shop-card__actions">
                            <!--
                            <div class="ag-shop-card__action"><a class="js-share-trigger" href="#"><i class="ag-shop-card__icon ag-shop-card__icon--write"></i><span>поделиться</span></a></div>
                            <div class="ag-shop-card__share">
                              <div class="ag-shop-card__share-container js-share-popup">
                                <div class="ag-shop-card__share-item"><a class="ag-shop-social-link ag-shop-social-link--vk" href="#"></a></div>
                                <div class="ag-shop-card__share-item"><a class="ag-shop-social-link ag-shop-social-link--ok" href="#"></a></div>
                                <div class="ag-shop-card__share-item"><a class="ag-shop-social-link ag-shop-social-link--fb" href="#"></a></div>
                                <div class="ag-shop-card__share-item"><a class="ag-shop-social-link ag-shop-social-link--tw" href="#"></a></div>
                                <div class="ag-shop-card__share-item"><a class="ag-shop-social-link ag-shop-social-link--inst" href="#"></a></div>
                                <div class="ag-shop-card__share-item"><a class="ag-shop-social-link ag-shop-social-link--yt" href="#"></a></div>
                                <div class="ag-shop-card__share-item">
                                  <form class="ag-shop-card__share-input-container">
                                    <input class="ag-shop-card__share-input" type="text" placeholder="Отправить на почту">
                                    <button class="ag-shop-card__share-submit" type="button">Отправить</button>
                                  </form>
                                </div>
                              </div>
                            </div>
                            -->
                            <? if(
                                0
                                &&
                                $USER->IsAuthorized() && !$arResult["MARK"]):?>
                                <div class="ag-shop-card__action">
                                    <a href="#review" onclick="$('.ag-shop-card__review-form-input').focus();">
                                        <i class="ag-shop-card__icon ag-shop-card__icon--write"></i>
                                        <span>оставить отзыв</span>
                                    </a>
                               </div>
                            <? endif ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="grid__col-12 grid__col-md-auto">
                  <div class="ag-shop-card__right-column">
                    <div class="ag-shop-card__container">
                      <div class="ag-shop-card__header">
                        <h2 class="ag-shop-card__header-title"><?= $arResult["OFFERS"][0]["NAME"]?></h2>
                        
                        <? /* if($arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]):?>
                        <div class="ag-shop-card__header-code">Артикул: <strong><?= 
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]
                        ?></strong></div>
                        <? elseif($arResult["CATALOG_ITEM"]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]):?>
                        <div class="ag-shop-card__header-code">Артикул: <strong><?= 
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]
                        ?></strong></div>
                        <? endif */ ?>

                        <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["PERFOMANCE_DATE"][0]["VALUE"]):?>
                        <div class="ag-shop-card__header-code">Дата мероприятия: <strong><?= 
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["PERFOMANCE_DATE"][0]["VALUE"]
                        ?></strong></div>
                        <? endif ?>
                        
                        <? if(
                            !$arResult["CATALOG_ITEM"]["PROPERTIES"]["USE_BEFORE_DATE"][0]["VALUE"]
                            && 
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]
                        ):
                        $date = date("d.m.Y",time()+$arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]*24*60*60)
                        ?>
                        <div class="ag-shop-card__header-code">Использовать до: <strong><?= 
                            $date
                        ?></strong></div>
                        <? endif ?>

                        <? if(
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["USE_BEFORE_DATE"][0]["VALUE"]
                            && 
                            !$arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]
                        ):?>
                        <div class="ag-shop-card__header-code">Использовать до: <strong><?= 
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["USE_BEFORE_DATE"][0]["VALUE"]
                        ?></strong></div>
                        <? endif ?>

                        <? if(
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["USE_BEFORE_DATE"][0]["VALUE"]
                            && 
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]
                        ):
                        $tmp = date_parse($arResult["CATALOG_ITEM"]["PROPERTIES"]["USE_BEFORE_DATE"][0]["VALUE"]);
                        $date1 = date("d.m.Y",$ts1 = mktime(0,0,0,$tmp["month"],$tmp["day"],$tmp["year"]));
                        $ts2 = time()+$arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]*24*60*60;
                        $date2 = date("d.m.Y",$ts2)
                        ?>
                        <div class="ag-shop-card__header-code">Использовать до: <strong><?= 
                            $ts1<$ts2?$date1:$date2
                        ?></strong></div>
                        <? if($ts1+24*60*60<time()):?>
                            <div class="ag-shop-card__requirements"
                            style="margin-left: -12px;">
                                Мероприятие завершено. Поощрение недоступно для
                                заказа.
                            </div>
                        <? endif ?>
                        <? endif ?>


                        
                      </div>
                      <?
                      if(
                        $arResult["CATALOG_ITEM"]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]=='troyka'
                        &&
                        $USER->IsAuthorized()
                      ):?>
                      <div class="ag-shop-card__field">
                        <div class="ag-shop-card__fieldname">Введите номер карты Тройка:</div>
                        <div class="ag-shop-card__card-number">
                          <select class="ag-shop-modal__select" id="troyka-card-number">
                            <option value="">Добавить карту</option>
                          </select>
                          <input class="ag-shop-card__card-number-input"
                          type="tel" placeholder="0000000000" value=""
                          id="newcardnum"
                          >
                          <div class="ag-shop-card__card-number-tooltip">
                            <div
                            class="ag-shop-card__card-number-tooltip-content"><img
                            src="/local/assets/images/troyka.png">
                              <p>Пример: <br>0004456789 (10цифр)</p>
                            </div>
                          </div>
                        </div>
                      </div>
                      <script>
                        $.get(
                            "/.integration/troyka.getcards.ajax.php",
                            function(data){
                                try{
                                    var answer = JSON.parse(data);
                                }
                                catch(e){
                                    riseError('Не могу получить список карт');                                    
                                    return false;
                                }

                                if(answer.error){
                                    riseError('Не могу получить список карт:'+answer.error);
                                }

                                for(i in answer.cards){
                                    $('#troyka-card-number').append(
                                        '<option value="'+answer.cards[i]+'">'
                                        + answer.cards[i]
                                        +'</option>'
                                    );   
                                }
                                
                                // Прячем око
                                if(answer.cards.length){
                                    $('#troyka-card-number option').last()
                                        .prop('selected', true);
                                    $('#newcardnum').hide();
                                    $('.ag-shop-card__card-number-tooltip').hide();
                                }

                                // Выводим окошко для ввода новой карты при
                                // выборе "добавить карту"
                                $('#troyka-card-number').change(function(){
                                    if(!$('#troyka-card-number').val()){
                                        $('#newcardnum').show();
                                        $('.ag-shop-card__card-number-tooltip').show();
                                    }else{
                                        $('#newcardnum').hide();
                                        $('.ag-shop-card__card-number-tooltip').hide();
                                    }
                                });
                                check_filling_troika();

                            }

                        );

                      </script>
                      <? endif ?>
                      <? if(
                        $arResult['CATALOG_ITEM']["PROPERTIES"]['ARTNUMBER'][0]["VALUE"]!='troyka'
                        &&
                        $arResult['CATALOG_ITEM']["PROPERTIES"]['ARTNUMBER'][0]["VALUE"]!='parking'
                        &&
                        !$stopMonLimit
                        &&
                        // Если дата мероприятия ещё не вышла
                        (
                            !$ts1
                            ||
                            $ts1+24*60*60>time()
                        )
                        &&
                        // Если есть на складе
                        count($arResult["OFFERS"][0]["STORAGES"]) 
                        // И у тебя достаточно баллов
                        &&  
                        $arResult["ACCOUNT"]["CURRENT_BUDGET"] >= $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
                        // И либо ты активный гражданин, либо на товар
                        // установлен рейтинг
                        && 
                        (
                            trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
                            ||
                            trim($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"][0]["VALUE"])
                        )
                      ):?>
                      <div class="grid grid--bleed amounter amounter<? if(count($arResult["OFFERS"][0]["STORAGES"])==1):
                      ?>--on<? else: ?>--off<? endif ?>">

                        <div class="grid__col-shrink">
                          <div class="ag-shop-card__field ag-shop-card__field--align-right">
                            <div class="ag-shop-card__fieldname">Единица:</div>
                            <div class="ag-shop-card__total-points"><?= $arResult["CATALOG_ITEM"]["PROPERTIES"]["QUANT"][0]["VALUE"] ?></div>
                          </div>
                        </div>
                          
                        <div class="grid__col-auto">
                          <div class="ag-shop-card__field">
                            <div class="ag-shop-card__fieldname">Количество:</div>
                            <div class="ag-shop-card__count">
                              <button class="ag-shop-card__count-button ag-shop-card__count-button--sub" type="button"></button>
                              <div style="padding-top: 3px;" class="ag-shop-card__count-number">1</div>
                              <button class="ag-shop-card__count-button ag-shop-card__count-button--add" type="button"></button>
                            </div>
                          </div>
                        </div>
                        
                        <div class="grid__col-shrink">
                          <div class="ag-shop-card__field ag-shop-card__field--align-right">
                            <div class="ag-shop-card__fieldname">Итого:</div>
                            <div id="ag-shop-card__total-points" class="ag-shop-card__total-points"><?= number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,",","")?></div>
                          </div>
                        </div>
                      </div>
                      <? endif ?>
                      
                      <? foreach($arResult["PROP1C"] as $code1c=>$props): ?>
                      <? if(!$props["VALUES"])continue;?>
                      <div class="ag-shop-card__field">
                        <div class="ag-shop-card__fieldname"><?= $props["NAME"]?>:</div>
                        <div class="ag-shop-card__sizes">
                          <? foreach($props["VALUES"] as $id=>$value):?>
                          <label>
                            <input type="radio" name="<?= $code1c?>" <?
                            if($id==$arResult["OFFERS"][0]["PROPERTIES"][$code1c][0]["VALUE"])echo "checked";
                            ?> value="<?= $id?>">
                            <div class="ag-shop-card__sizes-item"><?= $value?></div>
                          </label>
                          <? endforeach ?>
                        </div>
                      </div>
                      <? endforeach ?>
                      <div class="ag-shop-card__field ag-shop-card__field--error">
                        <div class="ag-shop-card__fieldname">Где получить?</div>
                        <!-- 
                        <div class="ag-shop-card__places-tabs">
                          <div class="ag-shop-card__places-tabs-item ag-shop-card__places-tabs-item--active">списком</div>
                          <div class="ag-shop-card__places-tabs-item">на карте</div>
                        </div>
                        -->
                        <div class="ag-shop-card__places">
                          <? $count=0;foreach($arResult["OFFERS"][0]["STORAGES"] as $id=>$ammount): $count++;?>
                          <label>
                            <input  onclick="return selectStorage('<?= $id;?>');"type="radio" name="place" value="<?= $id ?>" <? 
                                if(count($arResult["OFFERS"][0]["STORAGES"])==1)echo " checked ";
                                /*
                                if($count==count($arResult["OFFERS"][0]["STORAGES"]))echo
                                    " checked ";
                                */
                            ?>>
                            <div class="ag-shop-card__places-item"><?= $arResult["STORAGES"][$id]["TITLE"] ?></div>
                          </label>
                          <? endforeach ?>
                        </div>
                        <div class="ag-shop-card__selected-place">
                          <div class="ag-shop-card__selected-place-header">
                            <div class="grid grid--bleed grid--justify-space-between">
                              <div class="grid__col-xs-12 grid__col-sm-shrink">
                                <div class="ag-shop-card__selected-place-station">
                                    <i class="ag-shop-icon ag-shop-icon--metro"></i>
                                    <span>
                                    <? if(count($arResult["OFFERS"][0]["STORAGES"])==1):?>
                                    <?= $arResult["STORAGES"][$id]["TITLE"] ?>
                                    <? endif?>
                                    </span>
                                </div>
                              </div>
                              <div class="grid__col-xs-12 grid__col-sm-shrink">
                                  <? foreach(array(
                                    array(0,0,"отсутствует"),
                                    array(1,10,"мало"),
                                    array(11,100,"достаточно"),
                                    array(101,1000000000,"много")
                                  ) as $arAmmount):?>
                                    <div class="ag-shop-card__remaining-count" 
                                    fromAmmount="<?= $arAmmount[0]?>"
                                    toAmmount="<?= $arAmmount[1]?>"
                                    style="display: <?
                                      if(
                                        count($arResult["OFFERS"][0]["STORAGES"])==1
                                        &&
                                        (
                                            $ammount>=$arAmmount[0] 
                                            &&
                                            $ammount<=$arAmmount[1]
                                        )
                                      ): ?>inline-block;<? else:?>none;<? endif ?>"
                                    >
                                      <span class="ag-shop-card__remaining-count-title">
                                        осталось:
                                      </span>
                                      <span class="ag-shop-card__remaining-count-text">
                                        <?= $arAmmount[2]?>
                                      </span>
                                    </div>
                                  <? endforeach ?>
                              </div>
                            </div>
                          </div>
                          <table class="ag-shop-card__selected-place-table">
                          <? if(count($arResult["OFFERS"][0]["STORAGES"])==1):?>
                                <? if(trim($arResult["STORAGES"][$id]["ADDRESS"])):?>
                                <tr>
                                  <td>Адрес:</td>
                                  <td><?= $arResult["STORAGES"][$id]["ADDRESS"] ?></td>
                                </tr>
                                <? endif ?>
                                <? if(trim($arResult["STORAGES"][$id]["PHONE"])):?>
                                <tr>
                                  <td>Телефон:</td>
                                  <td><?= $arResult["STORAGES"][$id]["PHONE"] ?></td>
                                </tr>
                                <? endif ?>
                                <? if(trim($arResult["STORAGES"][$id]["SCHEDULE"])):?>
                                <tr>
                                  <td>Режим:</td>
                                  <td><?= $arResult["STORAGES"][$id]["SCHEDULE"] ?></td>
                                </tr>
                                <? endif ?>
                                <? if($arResult["STORAGES"][$id]["EMAIL"]):?>
                                <tr>
                                  <td>Сайт:</td>
                                  <td><a href="<?=
                                  $arResult["STORAGES"][$id]["EMAIL"]
                                  ?>" target="_blank"><?=
                                  linkTruncate($arResult["STORAGES"][$id]["EMAIL"]) 
                                  ?></a></td>
                                </tr>
                                <? endif ?>
                          <? endif ?>
                          </table>
                          <? if(0 && trim($arResult["STORAGES"][$id]["DESCRIPTION"])):?>
                          <p class="ag-shop-card__selected-place-description"><?= $arResult["STORAGES"][$id]["DESCRIPTION"] ?></p>
                          <? endif ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="ag-shop-card__container">
                <div class="ag-shop-card__field ag-shop-card__field--no-gaps">
                <? if(trim($arResult["CATALOG_ITEM"]["DETAIL_TEXT"])):?>
                  <h4>Описание:</h4>
                  <p class="ag-shop-card__description"><?= 
                    $arResult["CATALOG_ITEM"]["DETAIL_TEXT"]
                  ?></p>
                <? endif ?>
                  <? if(
                      $arResult["CATALOG_ITEM"]["PROPERTIES"]
                        ["RECEIVE_RULES"][0]["~VALUE"]["TEXT"]
                  ):?>
                  <h4>Правила получения:</h4>
                  <p>
                  <?=
                  $arResult["CATALOG_ITEM"]["PROPERTIES"]
                    ["RECEIVE_RULES"][0]["~VALUE"]["TEXT"]
                  ?>
                  </p>
                  <? endif ?>

                  <? if(
                      $arResult["CATALOG_ITEM"]["PROPERTIES"]
                        ["CANCEL_RULES"][0]["~VALUE"]["TEXT"]
                  ):?>
                  <h4>Правила отмены:</h4>
                  <p>
                  <?=
                  $arResult["CATALOG_ITEM"]["PROPERTIES"]
                    ["CANCEL_RULES"][0]["~VALUE"]["TEXT"]
                  ?>
                  </p>
                  <? endif ?>

                </div>
                <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]):?>
                <div class="ag-shop-card__warning">
                    <i class="ag-shop-icon ag-shop-icon--attention"></i><span>Срок действия вашего заказа <?= 
                        $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]
                        ?> <?= 
                        get_days($arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]);
                        ?> с момента оформления.</span>
                </div>
                <? endif ?>

                <? if(
                    !$stopMonLimit
                    &&
                    // Если дата мероприятия не вышла
                    (
                        !$ts1    // НЕ определена дата мероприятия
                        ||
                        $ts1+24*60*60>time()    // Дата мероприятия не вышла
                    )
                    &&
                    // Если есть на складе
                    count($arResult["OFFERS"][0]["STORAGES"]) 
                    &&  
                    // Если достаточно средств
                    $arResult["ACCOUNT"]["CURRENT_BUDGET"] >= $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]
                    &&  
                    (
                        trim($arResult["USER_INFO"]["UF_USER_AG_STATUS"])
                        ||
                        trim($arResult["CATALOG_ITEM"]["PROPERTIES"]["RATING_LIMIT"][0]["VALUE"])
                    )
                ):?>
                <button class="ag-shop-card__submit-button" onclick="return productConfirm();" 
                    type="button">Заказать за <strong><?= 
                        number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,","," ")
                    ?></strong> <?= get_points($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"])?></button>
                <? endif ?>
                <div class="ag-shop-card__additional-info">
                <? /*
                  <div class="ag-shop-card__tabs">
                    <!-- 
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link" href="#">Полное описание</a></div>
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link" href="#">Правило отмены</a></div>
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link" href="#">Как получить</a></div>
                    -->
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link ag-shop-menu__link--active" href="#">Отзывы (<?= $arResult["MESSAGES"]?>)</a></div>
                  </div>
                  <div class="ag-shop-card__tabs-content">
                    <a name="review"><h1></h1></a>
                    <form class="ag-shop-card__review-form" prodictid=<?= $arResult["CATALOG_ITEM"]["ID"] ?>>
                    <? if($USER->IsAuthorized() && !$arResult["MARK"]):?>
                      <div class="grid grid--bleed">
                        <div class="grid__col-12 grid__col-md-shrink">
                          <label class="ag-shop-card__review-form-container"><span class="ag-shop-card__review-form-label">Ваш отзыв:</span>
                            <textarea class="ag-shop-card__review-form-input" placeholder="Текст отзыва"></textarea>
                          </label>
                        </div>
                        <div class="grid__col-12 grid__col-md-shrink">
                          <div class="ag-shop-card__review-form-container"><span class="ag-shop-card__review-form-label">Ваша оценка:</span>
                            <div class="ag-shop-card__rating ag-shop-card__rating--interactive">
                              <div class="ag-shop-item-card__rating-item" onclick="return setMark(this);" rel="1"></div>
                              <div class="ag-shop-item-card__rating-item" onclick="return setMark(this);" rel="2"></div>
                              <div class="ag-shop-item-card__rating-item" onclick="return setMark(this);" rel="3"></div>
                              <div class="ag-shop-item-card__rating-item" onclick="return setMark(this);" rel="4"></div>
                              <div class="ag-shop-item-card__rating-item" onclick="return setMark(this);" rel="5"></div>
                            </div>
                          </div>
                        </div>
                        <div class="grid__col-12">
                          <div class="ag-shop-card__review-form-container">
                            <div class="ag-shop-card__review-form-buttons">
                              <button class="ag-shop-card__review-form-button" type="button" onclick="return addComment();">Оставить отзыв</button>
                              <!-- <button class="ag-shop-card__review-form-button ag-shop-card__review-form-button--cancel" type="button">Отмена</button> -->
                            </div>
                          </div>
                        </div>
                      </div>
                    <? endif ?>
                    </form>
                    <div class="ag-shop-card__reviews">
                    </div>
                    */?>
                  </div>
                </div>
              </div>
            </div>
    <div class="ag-shop-modal-wrap" style="display:none" id="card-order-confirm">
      <div class="ag-shop-modal">
        <div class="ag-shop-modal__container">
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Подтверждение заказа</div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Заказ:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-name">Сумка городская</div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Цена:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked"
            id="confirm-price"><span>415</span> <span class="balls">баллов</span></div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Единица:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked"
            id="confirm-unit"><?=
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["QUANT"][0]["VALUE"]
            ?></div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Количество:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked"
            id="confirm-amount">1</div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Получение:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-store"><span>415</span> баллов</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-store-id" style="display:none;"></div>
          </div>
          <?
          if(
              $arResult["CATALOG_ITEM"]["PROPERTIES"]["CANCEL_ABILITY"]
              [0]["VALUE_ENUM"] != 'да'
          ):?>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__alert">
                <i class="ag-shop-icon ag-shop-icon--attention"></i>
                <span>
                    При нажатии кнопки «Оформить заказ» баллы, потраченные на 
                    данное поощрение, не возвращаются.
                </span>
            </div>
          </div>
          <? endif?>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__buttons-wrap">
              <button class="ag-shop-modal__button" id="card-order-confirm-button" type="button" onclick="return productConfirmNext();">Оформить заказ</button>
              <button class="ag-shop-modal__button ag-shop-modal__button--cancel" type="button" onclick="$('.ag-shop-modal-wrap').fadeOut();">Отмена</button>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="ag-shop-modal-wrap" style="display:none"
    id="card-order-confirm-troika">
      <div class="ag-shop-modal">
        <div class="ag-shop-modal__container">
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Подтверждение заказа</div>
          </div>
          <div class="ag-shop-modal__row" style="display:none;">
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" >
            <input class="ag-shop-card__card-number-input" id="confirm-code" type="tel"
            placeholder="00000" value="" id="confirm-code">
            </div>
            <div class="ag-shop-modal__label"><br>На ваш мобильный телефон <b><?=
            str_replace("u","",$arResult["USER_INFO"]['LOGIN'])?></b> выслан код подтверждения для
            пополнения карты Тройка <span id="troykanum"></span>.<br>
            Вам необходимо указать код для осуществления операции.<br>
            Если вы не получили код в течение 5 минут, пожалуйста, запросите
            новый код<br/>
            </div>
          </div>
           <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Заказ:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-name">Сумка городская</div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Номер карты:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked"
            id="confirm-card"></div>
          </div>
           <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Цена:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-price"><span>415</span> баллов</div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">Получение:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked"
            id="troyka-confirm-store"><span>415</span> баллов</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked"
            id="troyka-confirm-store-id" style="display:none;"></div>
          </div>
          <div class="ag-shop-modal__row">
            <?
            if(
                $arResult["CATALOG_ITEM"]["PROPERTIES"]["CANCEL_ABILITY"][0]["VALUE_ENUM"]
                !=
                'да'
            ):?>
            <div class="ag-shop-modal__alert"><i class="ag-shop-icon ag-shop-icon--attention"></i><span>При нажатии кнопки «Оформить заказ» баллы, потраченные на данное поощрение, не возвращаются.</span></div>
          </div>
          <? endif?>
          <div class="ag-shop-modal__row" style="display:none" id="troyka-error">

          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__buttons-wrap">
              <button class="ag-shop-modal__button"
              id="card-order-confirm-button-troyka" type="button" onclick="return productConfirmNext();">Оформить заказ</button>
              <button class="ag-shop-modal__button ag-shop-modal__button--cancel" type="button" onclick="$('.ag-shop-modal-wrap').fadeOut();">Отмена</button>
            </div>
          </div>
        </div>
      </div>
    </div>

        <? else: ?>
            <h3>Нет доступных предложений</h3>
        <? endif ?>
