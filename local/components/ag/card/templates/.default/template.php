<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


        <? if(isset($arResult["OFFERS"][0])):?>
            <div class="ag-shop-card">
            <? if($arResult["ACCOUNT"]["CURRENT_BUDGET"] < $arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]): ?>
              <div class="ag-shop-card__container">
                <div class="ag-shop-card__requirements">Для заказа данного поощрения необходимо набрать <?= number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0)?> <?= get_points(number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0))?>.</div>
              </div>
            <? endif ?>
              <div class="grid grid--bleed">
                <div class="grid__col-12 grid__col-md-shrink">
                  <div class="ag-shop-card__left-column">
                    <div class="ag-shop-card__image-block">
                      <div class="ag-shop-card__image-wrap">
                        <!-- для темного фона добавить: ag-shop-item-card--dark-->
                        <div class="ag-shop-card__image-container" style="background-image: url(<?= 
                            $arResult["OFFERS"][0]["PROPERTIES"]["MORE_PHOTO"][0]["FILE_PATH"]
                          ?>)">
                          <div class="ag-shop-card__map" style="display:none"></div>
                          <div class="ag-shop-card__image"></div>
                          <div class="ag-shop-card__image-info">
                            <div class="ag-shop-card__image-points">
                              <div class="ag-shop-item-card__points-count"><?= number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0)?></div>
                              <div class="ag-shop-item-card__points-text"><?= get_points(number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0))?></div>
                            </div>
                            <div class="ag-shop-card__image-badges"><img class="ag-shop-item-card__badge" src="/local/assets/images/badge__new.png"></div>
                          </div>
                          <button class="ag-shop-item-card__likes" type="button">
                            <div class="ag-shop-item-card__likes-icon<?if($arResult["MYWISH"]):?> wish-on<? endif ?>"
                            productId="<?= $arResult["CATALOG_ITEM"]["ID"]?>"
                            onclick="return mywish(this)"
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
                        <? if($arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]):?>
                        <div class="ag-shop-card__header-code">Артикул: <strong><?= 
                            $arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]
                        ?></strong></div>
                        <? endif ?>
                      </div>
                      <div class="grid grid--bleed grid--justify-space-between grid--align-center">
                        <div class="grid__col-12 grid__col-md-shrink">
                          <div class="ag-shop-card__rating">
                            <? for($i=0;$i<round($product["RATING"]);$i++):?>
                            <div class="ag-shop-slider-card__rating-item ag-shop-slider-card__rating-item--active"></div>
                            <? endfor ?>
                            <? for($j=0;$j<5-round($product["RATING"]);$j++):?>
                            <div class="ag-shop-slider-card__rating-item"></div>
                            <? endfor ?>
                              
                          </div>
                        </div>
                        <div class="grid__col-12 grid__col-md-shrink">
                          <div class="ag-shop-card__actions">
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
                            <div class="ag-shop-card__action"><a href="#"><i class="ag-shop-card__icon ag-shop-card__icon--write"></i><span>оставить отзыв</span></a></div>
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
                        <? if($arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]):?>
                        <div class="ag-shop-card__header-code">Артикул: <strong><?= 
                            $arResult["CATALOG_ITEM"]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]
                        ?></strong></div>
                        <? endif ?>
                      </div>
                      <div class="ag-shop-card__field">
                        <div class="ag-shop-card__fieldname">Введите номер карты Тройка:</div>
                        <div class="ag-shop-card__card-number">
                          <input class="ag-shop-card__card-number-input" type="tel" placeholder="0000 000 000">
                          <div class="ag-shop-card__card-number-tooltip">
                            <div class="ag-shop-card__card-number-tooltip-content"><img src="http://placehold.it/124x94">
                              <p>Пример: <br>0004 456 789 (10цифр)</p>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="grid grid--bleed">

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
                              <div class="ag-shop-card__count-number">1</div>
                              <button class="ag-shop-card__count-button ag-shop-card__count-button--add" type="button"></button>
                            </div>
                          </div>
                        </div>
                        
                        <div class="grid__col-shrink">
                          <div class="ag-shop-card__field ag-shop-card__field--align-right">
                            <div class="ag-shop-card__fieldname">Итого:</div>
                            <div class="ag-shop-card__total-points"><?= number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0)?></div>
                          </div>
                        </div>
                      </div>
                      <div class="ag-shop-card__field">
                        <div class="ag-shop-card__fieldname">Цвет:</div>
                        <div class="ag-shop-card__colors">
                          <label>
                            <input type="radio" name="color" checked>
                            <div class="ag-shop-card__colors-item" style="background-color:#ffffff"></div>
                          </label>
                          <label>
                            <input type="radio" name="color">
                            <div class="ag-shop-card__colors-item" style="background-color:#80807E"></div>
                          </label>
                          <label>
                            <input type="radio" name="color">
                            <div class="ag-shop-card__colors-item" style="background-color:#0F0F0F"></div>
                          </label>
                        </div>
                      </div>
                      <div class="ag-shop-card__field">
                        <div class="ag-shop-card__fieldname">Размер:</div>
                        <div class="ag-shop-card__sizes">
                          <label>
                            <input type="radio" name="size" checked>
                            <div class="ag-shop-card__sizes-item">S</div>
                          </label>
                          <label>
                            <input type="radio" name="size">
                            <div class="ag-shop-card__sizes-item">M</div>
                          </label>
                          <label>
                            <input type="radio" name="size">
                            <div class="ag-shop-card__sizes-item">L</div>
                          </label>
                          <label>
                            <input type="radio" name="size">
                            <div class="ag-shop-card__sizes-item">XL</div>
                          </label>
                        </div>
                      </div>
                      <div class="ag-shop-card__field ag-shop-card__field--error">
                        <div class="ag-shop-card__fieldname">Где получить?</div>
                        <div class="ag-shop-card__places-tabs">
                          <div class="ag-shop-card__places-tabs-item ag-shop-card__places-tabs-item--active">списком</div>
                          <div class="ag-shop-card__places-tabs-item">на карте</div>
                        </div>
                        <div class="ag-shop-card__places">
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. Алтуфьевское</div>
                          </label>
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. Митино</div>
                          </label>
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. Люблино</div>
                          </label>
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. ВДНХ</div>
                          </label>
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. Ботанический сад</div>
                          </label>
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. Алексеевкая</div>
                          </label>
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. Саларьево</div>
                          </label>
                          <label>
                            <input type="radio" name="place">
                            <div class="ag-shop-card__places-item">м. Проспект мира</div>
                          </label>
                        </div>
                        <div class="ag-shop-card__selected-place">
                          <div class="ag-shop-card__selected-place-header">
                            <div class="grid grid--bleed grid--justify-space-between">
                              <div class="grid__col-shrink">
                                <div class="ag-shop-card__selected-place-station"><i class="ag-shop-icon ag-shop-icon--metro"></i><span>м. Люблино</span></div>
                              </div>
                              <div class="grid__col-shrink">
                                <div class="ag-shop-card__remaining-count"><span class="ag-shop-card__remaining-count-title">осталось:</span><span class="ag-shop-card__remaining-count-text">много</span></div>
                              </div>
                            </div>
                          </div>
                          <table class="ag-shop-card__selected-place-table">
                            <tr>
                              <td>Адрес:</td>
                              <td>Москва, Нижний сусальный переулок, 5с5</td>
                            </tr>
                            <tr>
                              <td>Телефон:</td>
                              <td>+7 985 999 00 87</td>
                            </tr>
                            <tr>
                              <td>Режим:</td>
                              <td>9-18  пн-вс с перерывом на обед</td>
                            </tr>
                            <tr>
                              <td>Сайт:</td>
                              <td><a href="#">www.site.ru</a></td>
                            </tr>
                          </table>
                          <p class="ag-shop-card__selected-place-description">Станция метро Люблино (Люблинской линии), первый вагон из центра, выход на улицу направо. Здание МФЦ района Марьино находится прямо около метро с правой стороны по отношению к дороге.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="ag-shop-card__container">
                <div class="ag-shop-card__field ag-shop-card__field--no-gaps">
                  <div class="ag-shop-card__fieldname">Описание:</div>
                  <p class="ag-shop-card__description"><?= 
                    $arResult["CATALOG_ITEM"]["DETAIL_TEXT"]
                  ?></p>
                </div>
                <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]):?>
                <div class="ag-shop-card__warning"><i class="ag-shop-icon ag-shop-icon--attention"></i><span>срок действия вашего заказа <?= $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]?> <?= get_days($arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"]);?> с момента оформления</span></div>
                <? endif ?>
                <button class="ag-shop-card__submit-button" type="button">Заказать за <strong>1170</strong> баллов</button>
                <div class="ag-shop-card__additional-info">
                  <div class="ag-shop-card__tabs">
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link" href="#">Полное описание</a></div>
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link" href="#">Правило отмены</a></div>
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link" href="#">Как получить</a></div>
                    <div class="ag-shop-card__tabs-item"><a class="ag-shop-menu__link ag-shop-menu__link--active" href="#">Отзывы (23)</a></div>
                  </div>
                  <div class="ag-shop-card__tabs-content">
                    <form class="ag-shop-card__review-form">
                      <div class="grid grid--bleed">
                        <div class="grid__col-12 grid__col-md-shrink">
                          <label class="ag-shop-card__review-form-container"><span class="ag-shop-card__review-form-label">Ваш отзыв:</span>
                            <textarea class="ag-shop-card__review-form-input" placeholder="Текст отзыва"></textarea>
                          </label>
                        </div>
                        <div class="grid__col-12 grid__col-md-shrink">
                          <div class="ag-shop-card__review-form-container"><span class="ag-shop-card__review-form-label">Ваша оценка:</span>
                            <div class="ag-shop-card__rating ag-shop-card__rating--interactive">
                              <div class="ag-shop-item-card__rating-item"></div>
                              <div class="ag-shop-item-card__rating-item"></div>
                              <div class="ag-shop-item-card__rating-item"></div>
                              <div class="ag-shop-item-card__rating-item"></div>
                              <div class="ag-shop-item-card__rating-item"></div>
                            </div>
                          </div>
                        </div>
                        <div class="grid__col-12">
                          <div class="ag-shop-card__review-form-container">
                            <div class="ag-shop-card__review-form-buttons">
                              <button class="ag-shop-card__review-form-button" type="button">Оставить отзыв</button>
                              <button class="ag-shop-card__review-form-button ag-shop-card__review-form-button--cancel" type="button">Отмена</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </form>
                    <div class="ag-shop-card__reviews">
                      <div class="ag-shop-card__reviews-item">
                        <div class="ag-shop-card__reviews-item-name">Константин Владиморович Велихов</div>
                        <div class="ag-shop-card__rating">
                          <div class="ag-shop-item-card__rating-item ag-shop-item-card__rating-item--active"></div>
                          <div class="ag-shop-item-card__rating-item ag-shop-item-card__rating-item--active"></div>
                          <div class="ag-shop-item-card__rating-item ag-shop-item-card__rating-item--active"></div>
                          <div class="ag-shop-item-card__rating-item ag-shop-item-card__rating-item--active"></div>
                          <div class="ag-shop-item-card__rating-item"></div>
                        </div>
                        <p class="ag-shop-card__addititonal-info-text">Он очень крут-сборка,начинка,батарея. Очень много крутых фишек,как уменьшение звука когда берешь в руки ,ответ на звонок при подносе к уху, пульт управления итд .</p>
                        <div class="ag-shop-card__reviews-item-date">07.10.16</div>
                      </div>
                      <div class="ag-shop-card__reviews-item">
                        <div class="ag-shop-card__reviews-item-name">Анжела Денисовна Свиридова</div>
                        <div class="ag-shop-card__rating">
                          <div class="ag-shop-item-card__rating-item ag-shop-item-card__rating-item--active"></div>
                          <div class="ag-shop-item-card__rating-item ag-shop-item-card__rating-item--active"></div>
                          <div class="ag-shop-item-card__rating-item ag-shop-item-card__rating-item--active"></div>
                          <div class="ag-shop-item-card__rating-item"></div>
                          <div class="ag-shop-item-card__rating-item"></div>
                        </div>
                        <p class="ag-shop-card__addititonal-info-text">бщее впечатление только положительное. Заряд держит хорошо, шустрый, экран классный, на вид аппарат очень стильный. В руке удобный, не греется (прошлый аппарат М8s грел как утюг). Камеру особо не тестировал, но фронталка неплохая.</p>
                        <div class="ag-shop-card__reviews-item-date">07.10.16</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        <? endif ?>
