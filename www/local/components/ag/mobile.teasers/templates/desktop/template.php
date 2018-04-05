			<div id="productsWrapper" class="desktop-products-wrapper hide-filter">

                <?
                $APPLICATION->IncludeComponent("ag:filter","desktop",array(
                    "SECTION_ID"=>$arSection["ID"],
                    "CACHE_TIME"=>1//COMMON_CACHE_TIME
                    ),false);
                ?> 

				<section class="catalog-inner">
					<div class="desktop-products-container">
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites wish-on" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Самый красивый зеленый рюкзак</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">2008</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Самый красивый зеленый рюкзак</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product notInStock">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Очень длинный заголовок который на две строчки</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">22333</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Очень длинный заголовок который на две строчки</b>
											<span class="desktop-product-info__category">Мероприятия</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Заголовок на три строчки, который будет сам обрезаться если что</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">200</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Заголовок на три строчки, который будет сам обрезаться если что</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Заголовок на три строчки, который будет сам обрезаться если что</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
										<img class="desktop-product-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
										<img class="desktop-product-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">200</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Заголовок на три строчки, который будет сам обрезаться если что</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites wish-on" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Самый красивый зеленый рюкзак</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">2008</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Самый красивый зеленый рюкзак</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product notInStock">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Очень длинный заголовок который на две строчки</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">22333</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Очень длинный заголовок который на две строчки</b>
											<span class="desktop-product-info__category">Мероприятия</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Заголовок на три строчки, который будет сам обрезаться если что</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">200</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Заголовок на три строчки, который будет сам обрезаться если что</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Заголовок на три строчки, который будет сам обрезаться если что</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
										<img class="desktop-product-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
										<img class="desktop-product-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">200</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Заголовок на три строчки, который будет сам обрезаться если что</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites wish-on" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Самый красивый зеленый рюкзак</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">2008</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Самый красивый зеленый рюкзак</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product notInStock">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Очень длинный заголовок который на две строчки</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">22333</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Очень длинный заголовок который на две строчки</b>
											<span class="desktop-product-info__category">Мероприятия</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Заголовок на три строчки, который будет сам обрезаться если что</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">200</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Заголовок на три строчки, который будет сам обрезаться если что</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->
						<!-- Product Item -->
						<article class="desktop-product">
							<button class="desktop-product-favourites" type="button">
								<span class="desktop-product-favourites__icon"></span>
								<span class="desktop-product-favourites__count">333</span>
							</button>
							<a class="desktop-product-link" href="#">
								<div class="desktop-product-inner" style="background-image: url('/catalog/img/desktop-product-preview-1.jpg')">
									<!-- Product Title -->
									<div class="desktop-product-title">
										<div class="desktop-product-title-wrapper">
											<div class="middle-aligned">
												<h3 class="desktop-product-title__name">Заголовок на три строчки, который будет сам обрезаться если что</h3>
											</div>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Badge -->
									<span class="desktop-product-badge">
										<img class="desktop-product-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
										<img class="desktop-product-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
										<img class="desktop-product-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
									</span>
									<!-- ============= -->
									<!-- Product Price -->
									<div class="desktop-product-price">
										<div class="desktop-product-price-wrapper">
											<div class="middle-aligned">
												<b class="desktop-product-price__summ">200</b>
												<span class="desktop-product-price__currency">балов</span>
											</div>

										</div>
									</div>
									<!-- ============= -->
									<!-- Product Info -->
									<div class="desktop-product-info">
										<div class="desktop-product-info-wrapper">
											<b class="desktop-product-info__title">Заголовок на три строчки, который будет сам обрезаться если что</b>
											<span class="desktop-product-info__category">Сувениры</span>
											<p class="desktop-product-info__description">
												Lorem ipsum dolor sit amet, consectetur adipisicing elit,
												sed do eiusmod tempor incididunt ut labore et dolore magna
												aliqua. Ut enim ad minim veniam, quis nostrud exercitation
											 </p>
										</div>
									</div>
									<!-- ============= -->
									<!-- Product Status -->
									<div class="desktop-product-status">
										<div class="desktop-product-status-wrapper">
											<span class="desktop-product-status__icon"></span>
											<span class="desktop-product-status__title">
												<i>Временно</i>
												нет в наличии
											</span>
										</div>
									</div>
									<!-- ============= -->
								</div>
							</a>
						</article>
						<!-- ================= -->



					</div>
				</section>

			</div>

