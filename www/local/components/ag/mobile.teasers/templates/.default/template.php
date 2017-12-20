	<main class="mobile-main paddingTop64">
		<div class="mobile-container">
			<div class="mobile-main-wrapper">
				<section class="mobile-search-status">
					<!-- This div is target for autocomplete -->
					<!-- dont remove him -->
				</section>
				<!-- Чтобы сделать большую плитку - добавить к этому контейнеру класс .mobile-product-grid--big -->
				<section class="mobile-product-grid">
                    <? foreach($arResult["PRODUCTS"] as $arProduct):?>
					<article class="mobile-product-item">
						<a class="mobile-product-item-wrapper" href="/catalog/<?= $arProduct["SECTION"]["CODE"]?>/<?= $arProduct["CODE"]?>/">
							<div class="mobile-product-item-preview" style="background-image: url('<?= $arProduct["IMAGE"]?>')">
								<span class="mobile-product-item-badge">
                                    
                                    <? if($arProduct["PROPERTY_SALELEADER_VALUE"]):?>
									<img class="mobile-product-item-badge__img" src="img/icon__product-label--hit.png" alt="" srcset="img/icon__product-label--hit@2x.png 2x">
                                    <? endif ?>
                                    
                                    <? if($arProduct["PROPERTY_NEWPRODUCT_VALUE"]):?>
									<img class="mobile-product-item-badge__img" src="img/icon__product-label--new.png" alt="" srcset="img/icon__product-label--new@2x.png 2x">
                                    <? endif ?>
                                    
                                    <? if($arProduct["PROPERTY_SPECIALOFFER_VALUE"]):?>
                                    <img class="mobile-product-item-badge__img" src="img/icon__product-label--sale.png" alt="" srcset="img/icon__product-label--sale@2x.png 2x">
                                    <? endif ?>
                                    
								</span>
								<button class="mobile-product-item-favourite" type="button">
									<span class="mobile-product-item-favourite__icon"></span>
									<span class="mobile-product-item-favourite__count">233</span>
								</button>
							</div>
							<h3 class="mobile-product-item-title"><?= $arProduct["NAME"]?></h3>
							<span class="mobile-product-item-price">
								<b class="mobile-product-item-price__number"><?= $arProduct["PROPERTY_MINIMUM_PRICE_VALUE"]?></b>
								<i class="mobile-product-item-price__currency"><?= get_points($arProduct["PROPERTY_MINIMUM_PRICE_VALUE"])?></i>
							</span>
						</a>
					</article>
                    <? endforeach ?>
                    
				</section>
			</div>
		</div>
	</main>
