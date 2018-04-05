<aside class="desktop-products-filter">
    <form id="desktopCatalogFilterForm" class="desktop-products-filter-form">
        <div class="desktop-products-filter-form-wrapper">
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Интересы</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="interestAll" class="desktop-checkbox__input defaultCheck filterCheckboxAll" type="checkbox" name="interestAll" value="222" checked>
                        <label for="interestAll" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Все</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="interestAG" class="desktop-checkbox__input" type="checkbox" name="interestAG" value="012">
                        <label for="interestAG" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Активный Гражданин</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="interestHistory" class="desktop-checkbox__input" type="checkbox" name="interestHistory" value="013">
                        <label for="interestHistory" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">История</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="interestBiography" class="desktop-checkbox__input" type="checkbox" name="interestBiography" value="014">
                        <label for="interestBiography" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Биография</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="interestPaint" class="desktop-checkbox__input" type="checkbox" name="interestPaint" value="015">
                        <label for="interestPaint" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Живопись</span>
                        </label>
                    </div>
                    <div class="desktop-products-filter-item__content-more">
                        <!-- Можно вместо button использовать <a> - главное чтобы класс был такой же -->
                        <button class="btn-filter-inner" type="button">Показать еще</button>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Цена</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-products-filter-price">
                        <div class="default-desktop-input-wrapper">
                            <input class="default-desktop-input" type="number" name="productPriceMin" maxlength="5" pattern="[0-9]{,5}">
                        </div>
                        <div class="default-desktop-input-wrapper">
                            <input class="default-desktop-input" type="number" name="productPriceMax" maxlength="5" pattern="[0-9]{,5}">
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Где получать</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="deliveryAll" class="desktop-checkbox__input defaultCheck filterCheckboxAll" type="checkbox" name="deliveryAll" value="322" checked>
                        <label for="deliveryAll" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Все</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="deliveryAcadem" class="desktop-checkbox__input" type="checkbox" name="deliveryAcadem" value="032">
                        <label for="deliveryAcadem" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">МФЦ Академический</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="deliveryArbat" class="desktop-checkbox__input" type="checkbox" name="deliveryArbat" value="033">
                        <label for="deliveryArbat" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">МФЦ Арбат</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="deliveryBogorodskoe" class="desktop-checkbox__input" type="checkbox" name="deliveryBogorodskoe" value="034">
                        <label for="deliveryBogorodskoe" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">МФЦ Богородское</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="deliveryKrasnoselskiy" class="desktop-checkbox__input" type="checkbox" name="deliveryKrasnoselskiy" value="035">
                        <label for="deliveryKrasnoselskiy" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">МФЦ Красносельский</span>
                        </label>
                    </div>
                    <div class="desktop-products-filter-item__content-more">
                        <!-- Можно вместо button использовать <a> - главное чтобы класс был такой же -->
                        <button class="btn-filter-inner" type="button">Показать еще</button>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Рейтинг</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-products-filter-rating">
                        <div class="desktop-checkbox desktop-checkbox-round">
                            <input id="ratingStars" class="desktop-checkbox__input" type="checkbox" name="ratingStars" value="531">
                            <label for="ratingStars" class="desktop-checkbox__label">
                                <span class="desktop-checkbox__icon desktop-checkbox__icon--stars"></span>
                                <span class="desktop-checkbox__title">и более</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Размеры</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXXS" class="desktop-checkbox__input" type="checkbox" name="sizeXXS" value="611">
                        <label for="sizeXXS" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XXS</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXS" class="desktop-checkbox__input" type="checkbox" name="sizeXS" value="602" checked>
                        <label for="sizeXS" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XS</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeS" class="desktop-checkbox__input" type="checkbox" name="sizeS" value="603">
                        <label for="sizeS" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">S</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeM" class="desktop-checkbox__input" type="checkbox" name="sizeM" value="604">
                        <label for="sizeM" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">M</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeL" class="desktop-checkbox__input" type="checkbox" name="sizeL" value="605">
                        <label for="sizeL" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">L</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXL" class="desktop-checkbox__input" type="checkbox" name="sizeXL" value="606">
                        <label for="sizeXL" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XL</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="sizeXXL" class="desktop-checkbox__input" type="checkbox" name="sizeXXL" value="607">
                        <label for="sizeXXL" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">XXL</span>
                        </label>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Цвет</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorBlack" class="desktop-checkbox__input" type="checkbox" name="colorBlack" value="511" checked>
                        <label for="colorBlack" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Черный</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorWhite" class="desktop-checkbox__input" type="checkbox" name="colorWhite" value="502">
                        <label for="colorWhite" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Белый</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorGreen" class="desktop-checkbox__input" type="checkbox" name="colorGreen" value="503">
                        <label for="colorGreen" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Зеленый</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="colorPink" class="desktop-checkbox__input" type="checkbox" name="colorPink" value="504">
                        <label for="colorPink" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Розовый</span>
                        </label>
                    </div>
                </div>
            </div>
            <!-- ============== -->
            <!-- Filter Item -->
            <div class="desktop-products-filter-item">
                <div class="desktop-products-filter-item__header">
                    <span class="desktop-products-filter-item__header-title">Показывать</span>
                </div>
                <div class="desktop-products-filter-item__content">
                    <div class="desktop-checkbox desktop-checkbox-round">
                        <input id="showProductsAll" class="desktop-checkbox__input defaultCheck" type="checkbox" name="showProductsAll" value="111" checked>
                        <label for="showProductsAll" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Все что в наличии</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="showProductsHit" class="desktop-checkbox__input" type="checkbox" name="showProductsHit" value="002">
                        <label for="showProductsHit" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Хит</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="showProductsSale" class="desktop-checkbox__input" type="checkbox" name="showProductsSale" value="003">
                        <label for="showProductsSale" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Акция</span>
                        </label>
                    </div>
                    <div class="desktop-checkbox desktop-checkbox-square">
                        <input id="showProductsNew" class="desktop-checkbox__input" type="checkbox" name="showProductsNew" value="004">
                        <label for="showProductsNew" class="desktop-checkbox__label">
                            <span class="desktop-checkbox__title">Новое</span>
                        </label>
                    </div>
                </div>
            </div>
            <!-- ============== -->
        </div>
    </form>
</aside>

