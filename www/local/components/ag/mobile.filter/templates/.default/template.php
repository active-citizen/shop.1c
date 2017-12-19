	<aside class="mobile-aside-filter">
		<div class="mobile-aside-filter-wrapper">
			<form id="mobileAsideFilterForm">
				<div class="mobile-aside-form">
					<header class="mobile-aside-form-block__header">
						<span class="mobile-aside-form-block__header-title">Настройка каталога</span>
					</header>
					<div class="mobile-aside-form-inner">
						<div class="mobile-aside-form-block">
							<div class="mobile-aside-form-item">
								<div class="custom-checkbox-default">
									<input class="custom-checkbox-square__input" 
                                    id="productGridCheckbox" type="checkbox" 
                                    name="productGridCheckbox" value="30" 
                                    <? if($arResult["GRID"]):?>checked<? endif ?>>
									<label class="custom-checkbox-square__label custom-checkbox-square__label--green" for="productGridCheckbox">
										<span class="custom-checkbox-default__info">
											<span class="custom-checkbox-default__info-icon">
												<span class="icon-aside-filter icon-aside-filter--grid"></span>
											</span>
											<span class="custom-checkbox-default__info-title">Мелкий вид плиток</span>
										</span>
									</label>
								</div>
							</div>
							<div class="mobile-aside-form-item">
								<div class="mobile-aside-dropdown">
									<div class="mobile-aside-form-item__info">
										<span class="mobile-aside-form-item__info-icon">
											<span class="icon-aside-filter icon-aside-filter--sortby"></span>
										</span>
										<span class="mobile-aside-form-item__info-title">Сортировать по:</span>
									</div>
									<div class="mobile-aside-form-item__option">
										<span class="mobile-aside-dropdown-btn" data-dropdown="filter-sort">
											<span class="mobile-aside-dropdown__item">Цена (дешевые сначала)</span>
										</span>
									</div>
								</div>
								<div class="mobile-aside-dropdown-outer">
									<header class="mobile-aside-form-block__header">
										<span class="mobile-aside-form-block__header-title">Сортировать по</span>
									</header>
									<div class="mobile-aside-dropdown-content" data-dropdown="filter-sort">
                                        <? foreach($arResult["SORTING"] as $arSort):?>
										<div class="mobile-aside-form-item">
											<div class="custom-checkbox-default">
												<input class="custom-checkbox-round__input" 
                                                id="productSort<?= $arSort["CODE"]?>" type="radio" 
                                                name="productSortPrice" value="<?= $arSort["VALUE"]?>"
                                                <? if($arSort["CHECKED"]):?>checked<? endif?>>
												<label class="custom-checkbox-round__label custom-checkbox-round__label--pink" 
                                                for="productSort<?= $arSort["CODE"]?>">
													<span class="custom-checkbox-default__info">
														<span class="custom-checkbox-default__info-icon">
															<span class="icon-aside-filter <?= $arSort["CLASSNAME"]?>"></span>
														</span>
														<span class="custom-checkbox-default__info-title">
                                                            <?= $arSort["NAME"]?>
                                                        </span>
													</span>
												</label>
											</div>
										</div>
                                        <? endforeach ?>
									</div>
									<footer class="mobile-aside-form-footer">
										<div class="mobile-aside-form-footer__left">
											<button class="mobile-form-btn mobile-form-btn--grey dropdown-close" type="button">Назад</button>
										</div>
										<div class="mobile-aside-form-footer__right">
											<button class="mobile-form-btn mobile-form-btn--green dropdown-save" type="button" data-dropdown="filter-sort">Применить</button>
										</div>
									</footer>
								</div>
							</div>
						</div>
						<div class="mobile-aside-form-block">
                            <!-- 
							<header class="mobile-aside-form-block__header">
								<span class="mobile-aside-form-block__header-title">Фильтр</span>
								<span class="mobile-aside-form-block__header-count">
									<i>210</i>
									<b>товаров</b>
								</span>
							</header>
                            -->
							<div class="mobile-aside-form-item">
								<div class="mobile-aside-dropdown">
									<div class="mobile-aside-form-item__info">
										<span class="mobile-aside-form-item__info-icon">
											<span class="icon-aside-filter icon-aside-filter--interests"></span>
										</span>
										<span class="mobile-aside-form-item__info-title">Интересы:</span>
									</div>
									<div class="mobile-aside-form-item__option">
										<span class="mobile-aside-dropdown-btn" data-dropdown="filter-interests">
											<span class="mobile-aside-dropdown__item">Все</span>
										</span>
									</div>
								</div>
								<div class="mobile-aside-dropdown-outer">
									<header class="mobile-aside-form-block__header">
										<span class="mobile-aside-form-block__header-title">Интересы</span>
									</header>
									<div class="mobile-aside-dropdown-content" data-dropdown="filter-interests">
                                        <? foreach($arResult["INTERESTS"] as $arInterest):?>
										<div class="mobile-aside-form-item">
											<div class="custom-checkbox-default">
												<input class="dropdown-checkbox-all custom-checkbox-square__input"  
                                                id="productInterest<?= $arInterest["CODE"]?>" type="checkbox" 
                                                name="productInterest<?= $arInterest["CODE"]?>" 
                                                value="<?= $arInterest["ID"]?>" 
                                                <? if($arInterest["CHECKED"]):?>checked<? endif?>>
												<label class="default-dropdown-label custom-checkbox-square__label custom-checkbox-square__label--pink" 
                                                for="productInterest<?= $arInterest["CODE"]?>">
													<span class="custom-checkbox-default__info">
														<span class="custom-checkbox-default__info-title"><?= $arInterest["NAME"]?></span>
													</span>
												</label>
											</div>
										</div>
                                        <? endforeach ?>

									</div>
									<footer class="mobile-aside-form-footer">
										<div class="mobile-aside-form-footer__left">
											<button class="mobile-form-btn mobile-form-btn--grey dropdown-close" type="button">Назад</button>
										</div>
										<div class="mobile-aside-form-footer__right">
											<button class="mobile-form-btn mobile-form-btn--green dropdown-save" type="button" data-dropdown="filter-interests">Применить</button>
										</div>
									</footer>
								</div>
							</div>
							<div class="mobile-aside-form-item">
								<div class="mobile-aside-form-item__info">
									<span class="mobile-aside-form-item__info-icon">
										<span class="icon-aside-filter icon-aside-filter--price"></span>
									</span>
									<span class="mobile-aside-form-item__info-title">Цена</span>
								</div>
								<div class="mobile-aside-form-item__option">
									<div class="mobile-aside-form-item__inputs">
										<div class="mobile-aside-price mobile-aside-price--min">
											<input class="aside-default-input" type="text" name="productPriceMin" 
                                            value="<?= $arResult["MIN_PRICE"]?>" placeholder="Мин." maxlength="5" pattern="[0-9]{,5}">
										</div>
										<div class="mobile-aside-price mobile-aside-price--max">
											<input class="aside-default-input" type="text" name="productPriceMax" 
                                            value="<?= $arResult["MAX_PRICE"]?>" placeholder="Макс." maxlength="5" pattern="[0-9]{,5}">
										</div>
									</div>
								</div>
							</div>
							<div class="mobile-aside-form-item">
								<div class="mobile-aside-dropdown">
									<div class="mobile-aside-form-item__info">
										<span class="mobile-aside-form-item__info-icon">
											<span class="icon-aside-filter icon-aside-filter--delivery"></span>
										</span>
										<span class="mobile-aside-form-item__info-title">Где получать</span>
									</div>
									<div class="mobile-aside-form-item__option">
										<span class="mobile-aside-dropdown-btn" data-dropdown="filter-delivery">
											<span class="mobile-aside-dropdown__item">Все</span>
										</span>
									</div>
								</div>
								<div class="mobile-aside-dropdown-outer">
									<header class="mobile-aside-form-block__header">
										<span class="mobile-aside-form-block__header-title">Где получать</span>
									</header>
									<div class="mobile-aside-dropdown-content" data-dropdown="filter-delivery">
										<div class="mobile-aside-form-item">
											<div class="custom-checkbox-default">
												<input class="dropdown-checkbox-all custom-checkbox-square__input"  id="productDeliveryAll" type="checkbox" name="productDeliveryAll" value="333" <? if(!$arResult["STORE_CHECKED"]):?>checked<? endif ?>>
												<label class="default-dropdown-label custom-checkbox-square__label custom-checkbox-square__label--pink" for="productDeliveryAll">
													<span class="custom-checkbox-default__info">
														<span class="custom-checkbox-default__info-title">Все</span>
													</span>
												</label>
											</div>
										</div>
                                        <? foreach($arResult["STORES"] as $arStore):?>
										<div class="mobile-aside-form-item">
											<div class="custom-checkbox-default">
												<input class="custom-checkbox-square__input" 
                                                id="productDelivery<?= $arStore["CODE"]?>" type="checkbox" 
                                                name="productDelivery<?= $arStore["CODE"]?>" 
                                                value="<?= $arStore["ID"]?>" <? if($arStore["CHECKED"]):?>checked<? endif?>>
												<label class="default-dropdown-label custom-checkbox-square__label custom-checkbox-square__label--pink" for="productDelivery<?= $arStore["CODE"]?>">
													<span class="custom-checkbox-default__info">
														<span class="custom-checkbox-default__info-title"><?= $arStore["TITLE"]?></span>
													</span>
												</label>
											</div>
										</div>
                                        <? endforeach ?>
									</div>
									<footer class="mobile-aside-form-footer">
										<div class="mobile-aside-form-footer__left">
											<button class="mobile-form-btn mobile-form-btn--grey dropdown-close" type="button">Назад</button>
										</div>
										<div class="mobile-aside-form-footer__right">
											<button class="mobile-form-btn mobile-form-btn--green dropdown-save" type="button" data-dropdown="filter-delivery">Применить</button>
										</div>
									</footer>
								</div>
							</div>
                            <!-- 
							<div class="mobile-aside-form-item">
								<div class="custom-checkbox-default">
									<input class="custom-checkbox-square__input" id="productRatingCheckbox" type="checkbox" name="productRatingCheckbox" value="992">
									<label class="custom-checkbox-square__label custom-checkbox-square__label--green" for="productRatingCheckbox">
										<span class="mobile-aside-form-item__info mobile-aside-form-item__info--stars">
											<span class="mobile-aside-form-item__info-icon">
												<span class="icon-aside-filter icon-aside-filter--stars"></span>
											</span>
											<span class="mobile-aside-form-item__info-title">и более</span>
										</span>
									</label>
								</div>
							</div>
							<div class="mobile-aside-form-item disabled">
								<div class="mobile-aside-dropdown">
									<div class="mobile-aside-form-item__info">
										<span class="mobile-aside-form-item__info-icon">
											<span class="icon-aside-filter icon-aside-filter--size"></span>
										</span>
										<span class="mobile-aside-form-item__info-title">Размер</span>
									</div>
									<div class="mobile-aside-form-item__option">
										<span class="mobile-aside-dropdown-btn disabled" data-dropdown="filter-size">
											<span class="mobile-aside-dropdown__item">Все</span>
										</span>
									</div>
								</div>
								<div class="mobile-aside-dropdown-outer">
									<header class="mobile-aside-form-block__header">
										<span class="mobile-aside-form-block__header-title">Размеры</span>
									</header>
									<div class="mobile-aside-dropdown-content" data-dropdown="filter-size">
										<div class="mobile-aside-form-item">
											<div class="custom-checkbox-default">
												<input class="dropdown-checkbox-all custom-checkbox-square__input"  id="productSizeAll" type="checkbox" name="productSizeAll" value="777" checked>
												<label class="default-dropdown-label custom-checkbox-square__label custom-checkbox-square__label--pink" for="productSizeAll">
													<span class="custom-checkbox-default__info">
														<span class="custom-checkbox-default__info-title">Все</span>
													</span>
												</label>
											</div>
										</div>
									</div>
									<footer class="mobile-aside-form-footer">
										<div class="mobile-aside-form-footer__left">
											<button class="mobile-form-btn mobile-form-btn--grey dropdown-close" type="button">Назад</button>
										</div>
										<div class="mobile-aside-form-footer__right">
											<button class="mobile-form-btn mobile-form-btn--green dropdown-save" type="button" data-dropdown="filter-size">Применить</button>
										</div>
									</footer>
								</div>
							</div>
							<div class="mobile-aside-form-item disabled">
								<div class="mobile-aside-dropdown">
									<div class="mobile-aside-form-item__info">
										<span class="mobile-aside-form-item__info-icon">
											<span class="icon-aside-filter icon-aside-filter--color"></span>
										</span>
										<span class="mobile-aside-form-item__info-title">Цвет</span>
									</div>
									<div class="mobile-aside-form-item__option">
										<span class="mobile-aside-dropdown-btn disabled" data-dropdown="filter-color">
											<span class="mobile-aside-dropdown__item">Все</span>
										</span>
									</div>
								</div>
								<div class="mobile-aside-dropdown-outer">
									<header class="mobile-aside-form-block__header">
										<span class="mobile-aside-form-block__header-title">Цвет</span>
									</header>
									<div class="mobile-aside-dropdown-content" data-dropdown="filter-color">
										<div class="mobile-aside-form-item">
											<div class="custom-checkbox-default">
												<input class="dropdown-checkbox-all custom-checkbox-square__input" id="productColorAll" type="checkbox" name="productColorAll" value="555" checked>
												<label class="default-dropdown-label custom-checkbox-square__label custom-checkbox-square__label--pink" for="productColorAll">
													<span class="custom-checkbox-default__info">
														<span class="custom-checkbox-default__info-title">Все</span>
													</span>
												</label>
											</div>
										</div>
									</div>
									<footer class="mobile-aside-form-footer">
										<div class="mobile-aside-form-footer__left">
											<button class="mobile-form-btn mobile-form-btn--grey dropdown-close" type="button">Назад</button>
										</div>
										<div class="mobile-aside-form-footer__right">
											<button class="mobile-form-btn mobile-form-btn--green dropdown-save" type="button" data-dropdown="filter-color">Применить</button>
										</div>
									</footer>
								</div>
							</div>
                            -->
						</div>
						<div class="mobile-aside-form-block mobile-aside-form-block--last">
							<div class="mobile-aside-form-item">
								<div class="custom-checkbox-default">
									<input class="custom-checkbox-square__input" 
                                    id="productHitCheckbox" type="checkbox" 
                                    name="productHitCheckbox" value="1" 
                                    <? if($arResult["HIT"]):?>checked<? endif ?>>
									<label class="custom-checkbox-square__label custom-checkbox-square__label--orange" for="productHitCheckbox">
										<span class="custom-checkbox-default__info">
											<span class="custom-checkbox-default__info-icon">
												<span class="icon-aside-filter icon-aside-filter--hit"></span>
											</span>
											<span class="custom-checkbox-default__info-title">Хит</span>
										</span>
									</label>
								</div>
							</div>
							<div class="mobile-aside-form-item">
								<div class="custom-checkbox-default">
									<input class="custom-checkbox-square__input" 
                                    id="productNewCheckbox" type="checkbox" 
                                    name="productNewCheckbox" value="2"
                                    <? if($arResult["NEW"]):?>checked<? endif ?>>
									<label class="custom-checkbox-square__label custom-checkbox-square__label--blue" for="productNewCheckbox">
										<span class="custom-checkbox-default__info">
											<span class="custom-checkbox-default__info-icon">
												<span class="icon-aside-filter icon-aside-filter--new"></span>
											</span>
											<span class="custom-checkbox-default__info-title">Новинки</span>
										</span>
									</label>
								</div>
							</div>
							<div class="mobile-aside-form-item">
								<div class="custom-checkbox-default">
									<input class="custom-checkbox-square__input" 
                                    id="productSaleCheckbox" type="checkbox" 
                                    name="productSaleCheckbox" value="3"
                                    <? if($arResult["SALE"]):?>checked<? endif ?>>
									<label class="custom-checkbox-square__label custom-checkbox-square__label--pink" for="productSaleCheckbox">
										<span class="custom-checkbox-default__info">
											<span class="custom-checkbox-default__info-icon">
												<span class="icon-aside-filter icon-aside-filter--sale"></span>
											</span>
											<span class="custom-checkbox-default__info-title">Акции</span>
										</span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<footer class="mobile-aside-form-footer">
						<div class="mobile-aside-form-footer__left">
							<button id="mobileFiltersReset" class="mobile-form-btn mobile-form-btn--grey" type="reset" name="mobileFiltersReset">Сброс</button>
						</div>
						<div class="mobile-aside-form-footer__right">
							<button class="mobile-form-btn mobile-form-btn--green" type="submit" name="mobileFiltersSubmit">Готово</button>
						</div>
					</footer>
				</div>
			</form>
		</div>
		<div class="mobile-aside-filter-outer">

		</div>
	</aside>
