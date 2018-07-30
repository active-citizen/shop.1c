                    <div class="ag-shop-card__container">
                      <div class="ag-shop-card__header ag-shop-card__header--mobile">
                        <h2 class="ag-shop-card__header-title"><?= $arResult["CATALOG_ITEM"]["NAME"]?></h2>
                        <? /* if($arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]):?>
                        <div class="ag-shop-card__header-code">Артикул: <strong><?= 
                            $arResult["OFFERS"][0]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]
                        ?></strong></div>
                        <? endif */ ?>

                          <?
                          $useBeforeDate = $arResult["CATALOG_ITEM"]["PROPERTIES"]["USE_BEFORE_DATE"][0]["VALUE"];
                          $daysToExpire = $arResult["CATALOG_ITEM"]["PROPERTIES"]["DAYS_TO_EXPIRE"][0]["VALUE"];

                          $useBefore = null;
                          $finished = false;
                          if (!$useBeforeDate && $daysToExpire) {
                              $useBefore = date("d.m.Y", time() + ($daysToExpire - 1) * 24 * 60 * 60);
                          }

                          if ($useBeforeDate && !$daysToExpire ) {
                              $useBefore = $useBeforeDate;
                          }

                          if ($useBeforeDate && $daysToExpire) {
                              $tmp = date_parse($useBeforeDate);
                              $ts1 = mktime(0, 0, 0, $tmp["month"], $tmp["day"], $tmp["year"]);
                              $ts2 = time() + $daysToExpire * 24 * 60 * 60;
                              $useBefore = date("d.m.Y", $ts1 < $ts2 ? $ts1 : $ts2);

                              if ($ts1 + 24 * 60 * 60 < time()) {
                                  $finished = true;
                              }
                          }

                          $useBeforeHtml = '';
                          if ($useBefore) {
                              $useBeforeHtml = "<div class=\"ag-shop-card__header-code\">Использовать до: <strong>$useBefore</strong></div>";
                          }

                          if ($finished) {
                              $useBeforeHtml .= "<div class=\"ag-shop-card__requirements\" style=\"margin-left: -12px;\">
                                  Мероприятие завершено. Поощрение недоступно для заказа.
                              </div>";
                          } ?>

                          <?= $useBeforeHtml; ?>
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

