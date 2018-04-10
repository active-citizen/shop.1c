        <div class="desktop-catalog">
            <!--  Заголовок каталога (сортировать по и вид плиток)-->
			<div class="desktop-catalog-header">
				<div class="desktop-catalog-header__left">
					<button id="catalogHeaderFilter" class="btn-catalog-header" type="button">
						<span class="btn-catalog-header__icon btn-catalog-header__icon--filter"></span>
						<span class="btn-catalog-header__title">Фильтр</span>
					</button>
					<button id="catalogHeaderReset" class="btn-catalog-header disabled" type="button">
						<span class="btn-catalog-header__icon btn-catalog-header__icon--reset"></span>
						<span class="btn-catalog-header__title">Сбросить</span>
					</button>
				</div>
				<div class="desktop-catalog-header__right">
					<div class="dropdown-wrapper desktop-catalog-sort">
						<button class="desktop-catalog-sort__btn" type="button" name="button">
							<span class="desktop-catalog-sort__btn-type">Сортировать по:</span>
                            <span class="desktop-catalog-sort__btn-current">
                            <? if($arParams["sorting"]["param"]=='price' && $arParams["sorting"]["direction"]=='asc'):?>
                            Цена по возрастанию
                            <? elseif($arParams["sorting"]["param"]=='price' && $arParams["sorting"]["direction"]=='desc'):?>
                            Цена по убыванию
                            <? elseif($arParams["sorting"]["param"]=='wishes' && $arParams["sorting"]["direction"]=='desc'):?>
                            Рейтинг
                            <? else:?>
							Дата добавления
                            <?endif?>
                            </span>
							<span class="desktop-catalog-sort__btn-icon"></span>
						</button>
						<div class="desktop-catalog-sort__dropdown">
							<ul class="list-default list-dropdown">
								<li
                                class="list-dropdown__item<?if($arParams["sorting"]["param"]=='price'
                                && $arParams["sorting"]["direction"]=='asc'):?>
                                selected<?
                                endif
                                ?>">
									<a class="list-dropdown__link" href="#" data-dropdownName="Цена по возрастанию"
                                    onclick="return teaserSorting('price-asc');"
                                    >
										<span class="list-dropdown__icon list-dropdown__icon--price"></span>
										<span class="list-dropdown__link-title">Цена по возрастанию</span>
									</a>
								</li>
								<li class="list-dropdown__item<?if($arParams["sorting"]["param"]=='price'
                                && $arParams["sorting"]["direction"]=='desc'):?>
                                selected<?
                                endif
                                ?>">
									<a class="list-dropdown__link" href="#" data-dropdownName="Цена по убыванию"
                                    onclick="return teaserSorting('price-desc');"
                                    >
										<span class="list-dropdown__icon list-dropdown__icon--price"></span>
										<span class="list-dropdown__link-title">Цена по убыванию</span>
									</a>
								</li>
								<li
                                class="list-dropdown__item<?if($arParams["sorting"]["param"]=='wishes'
                                && $arParams["sorting"]["direction"]=='desc'):?>
                                selected<?
                                endif
                                ?>">
									<a class="list-dropdown__link" href="#" data-dropdownName="Избранное"
                                    onclick="return teaserSorting('rating-desc');"
                                    >
										<span class="list-dropdown__icon list-dropdown__icon--favourite"></span>
										<span class="list-dropdown__link-title">Избранное</span>
									</a>
								</li>
								<li
                                class="list-dropdown__item<?if($arParams["sorting"]["param"]=='fresh'
                                && $arParams["sorting"]["direction"]=='desc'):?>
                                selected<?
                                endif
                                ?>">
									<a class="list-dropdown__link" href="#" data-dropdownName="Дата обновления"
                                    onclick="return teaserSorting('fresh-desc');"
                                    >
										<span class="list-dropdown__icon list-dropdown__icon--new"></span>
										<span class="list-dropdown__link-title">Дата обновления</span>
									</a>
								</li>
							</ul>
						</div>
					</div>
                    <?/* 
					<div class="dropdown-wrapper desktop-catalog-grid">
						<button class="desktop-catalog-sort__btn" type="button" name="button">
							<span class="desktop-catalog-sort__btn-type">Вид плиток:</span>
							<span class="desktop-catalog-sort__btn-current">Крупный</span>
							<span class="desktop-catalog-sort__btn-icon"></span>
						</button>
						<div class="desktop-catalog-sort__dropdown">
							<ul class="list-default list-dropdown">
								<li class="list-dropdown__item selected">
									<a class="list-dropdown__link" href="#" data-dropdownName="Крупный">
										<span class="list-dropdown__icon list-dropdown__icon--gridBig"></span>
										<span class="list-dropdown__link-title">Крупный</span>
									</a>
								</li>
								<li class="list-dropdown__item">
									<a class="list-dropdown__link" href="#" data-dropdownName="Мелкий">
										<span class="list-dropdown__icon list-dropdown__icon--gridSmall"></span>
										<span class="list-dropdown__link-title">Мелкий</span>
									</a>
								</li>
								<li class="list-dropdown__item">
									<a class="list-dropdown__link" href="#" data-dropdownName="Список">
										<span class="list-dropdown__icon list-dropdown__icon--gridList"></span>
										<span class="list-dropdown__link-title">Список</span>
									</a>
								</li>
							</ul>
						</div>
					</div>
                    */?>
				</div>
			</div>
            <!--  Конец. Заголовок каталога (сортировать по и вид плиток)-->

