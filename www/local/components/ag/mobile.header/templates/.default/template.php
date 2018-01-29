	<header class="mobile-header">
		<div class="mobile-container">
			<div class="mobile-header-wrapper">
				<div class="mobile-header-left">
					<button class="mobile-header-caregory-btn" type="button">
						<span class="mobile-header-category__title">
                            <? 
                                $sName = 'Все категории';
                                foreach($arResult["SECTIONS"] as $arSection)
                                    if($arSection["CURRENT"]){
                                        $sName = $arSection["NAME"];
                                        break;
                                    }
                            ?>
                            <?= $sName ?>
                        </span>
					</button>
					<div class="mobile-header-search">
						<form id="mobileHeaderSearchForm">
							<div id="multiple-datasets" class="mobile-header-search__input">
								<input class="typeahead" type="text" id="mobileHeaderSearchInput" name="mobileHeaderSearchInput" placeholder="Что вы ищете?" autocorrect="off" autocomplete="off">
								<button class="mobile-header-search__clear" type="button" name="clearTypeahead"></button>
							</div>
						</form>
					</div>
				</div>
                <?
                /*
                    Определяем выбран ли какой-то фильтр
                */
                $bIsSorted = false;
                if(
                    isset($arParams["filter"]["interest"]) 
                    && 
                    $arParams["filter"]["interest"]!=0)$bIsSorded=true;
                if(
                    !$bIsSorted
                    &&
                    isset($arParams["filter"]["store"][0])
                    &&
                    $arParams["filter"]["store"][0]!=333
                )$bIsSorted = true;
                if(
                    !$bIsSorted
                    &&
                    isset($arParams["filter"]["store"][0])
                    &&
                    isset($arParams["filter"]["interest"])
                    &&
                    count($arParams["filter"])>2

                )$bIsSorted = true;
                ?>
				<div class="mobile-header-right">
					<!--  Когда добавлены какие-то фильтры к странице, добавить для кнопки класс .sorted-->
					<button class="mobile-header-filter-btn<? if($bIsSorted):?> sorted<? endif?>" type="button">
						<span class="mobile-header-filter-btn__icon-active"></span>
						<span class="mobile-header-filter-btn__icon-default"></span>
					</button>
				</div>
			</div>
			<div class="mobile-header-category">
				<nav class="mobile-header-nav">
					<a class="mobile-header-nav__link
                    mobile-header-nav__link--big <?
                    if(
                        !$arResult["CURRENT_SECTION"]
                        &&
                        preg_match("#^/catalog/.*#", $_SERVER["REQUEST_URI"])

                    ):?>current<? endif ?>" href="/catalog/">
						<span class="mobile-header-nav__link-wrapper">
							<span class="mobile-header-nav__link-icon">
								<span class="icon-header-category icon-header-category--all"></span>
							</span>
							<span class="mobile-header-nav__link-title">Все категории</span>
						</span>
					</a>
                    <? foreach($arResult["SECTIONS"] as $arSection):?>
					<a class="mobile-header-nav__link<? if($arSection["CURRENT"]):?> current<? endif ?>" 
                        href="/catalog/<?= $arSection["CODE"]?>/<? if(isset($_REQUEST["productGridCheckbox"])):?>?productGridCheckbox=30<? endif ?>">
						<span class="mobile-header-nav__link-wrapper">
							<span class="mobile-header-nav__link-icon">
								<span class="icon-header-category <?= $arSection["CLASSNAME"]?>"></span>
							</span>
							<span class="mobile-header-nav__link-title"><?= $arSection["NAME"]?></span>
						</span>
					</a>
                    <? endforeach ?>
				</nav>
				<div class="mobile-header-other">
					<!-- Если нужно будет делать отображение "текущей" страницы -->
					<!-- то нужно добавить класс .current  -->
                    <? foreach($arResult["PAGES"] as $arPage):?>
					<a class="mobile-header-other__link<? if($arPage["CURRENT"]):?> current<? endif ?>" href="<?= $arPage["URL"]?>">
						<span class="mobile-header-other__link-icon">
							<span class="icon-header-other <?= $arPage["CLASSNAME"]
                            ?>"></span>
						</span>
						<span class="mobile-header-other__link-title"><?= $arPage["TITLE"]?></span>
					</a>
                    <? endforeach ?>
				</div>
			</div>
		</div>
	</header>
		<!-- Вставил блок , для корректной работы  -->
	<div class="mobile-container">
			<div class="mobile-main-wrapper">
				<section class="mobile-search-status">
					<!-- This div is target for autocomplete -->
					<!-- dont remove him -->
				</section>
				
				<section class="mobile-search-notfind disabled">
					<div class="mobile-search-notfind-wrapper">
						<span class="icon-search-notfind"></span>
						<p class="default-paragraph mobile-search-result__info">
							По запросу <span id="mobileSearchQuery" class="mobile-search-query"></span> ничего не найдено.
						</p>
						<p class="default-paragraph mobile-search-result__info">
							Создайте новый поисковый запрос.
						</p>
						<span class="mobile-search-notfind__reset">Очистить поиск</span>
					</div>
				</section>
				<!-- Чтобы сделать большую плитку - добавить к этому контейнеру класс .mobile-product-grid--big -->
			</div>	
	</div>
		
