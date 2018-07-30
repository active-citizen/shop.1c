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

