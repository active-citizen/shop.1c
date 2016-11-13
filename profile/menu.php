        <!-- Menu {{{-->
        <div class="ag-shop-menu">
          <div class="ag-shop-menu__container">
            <div class="ag-shop-menu__header">
              <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
                <div class="grid__col grid__col-shrink">
                  <h2 class="ag-shop-menu__current">Мои желания</h2>
                </div>
                <div class="grid__col grid__col-shrink">
                  <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button" type="button"><span></span></button>
                </div>
              </div>
            </div>
            <div class="ag-shop-menu__items js-menu__list">
              <div class="ag-shop-menu__item"><a class="ag-shop-menu__link<?if(preg_match("#^/profile/order/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/profile/order/">Мои заказы</a></div>
              <div class="ag-shop-menu__item"><a class="ag-shop-menu__link<?if(preg_match("#^/profile/points/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/profile/points/">Мои баллы</a></div>
              <div class="ag-shop-menu__item"><a class="ag-shop-menu__link<?if(preg_match("#^/profile/wishes/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/profile/wishes/">Мои желания</a></div>
            </div>
          </div>
        </div>
        <!-- }}} Menu-->
