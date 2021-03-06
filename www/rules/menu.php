        <!-- Menu {{{-->
        <? if(
            !IS_MOBILE
        ):?>        
        <div class="ag-shop-menu">
          <div class="ag-shop-menu__container">
            <div class="ag-shop-menu__header">
              <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
                <div class="grid__col grid__col-shrink">
                  <h2 class="ag-shop-menu__current">Правила</h2>
                </div>
                <div class="grid__col grid__col-shrink">
                  <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button" type="button"><span></span></button>
                </div>
              </div>
            </div>
            <div class="ag-shop-menu__items js-menu__list">
              <div class="ag-shop-menu__item">
                <a class="ag-shop-menu__link<?if(preg_match("#/rules/hiw/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/rules/hiw/">Как это работает?</a>
              </div>
              <div class="ag-shop-menu__item">
                <a class="ag-shop-menu__link<?if(preg_match("#/rules/stores/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/rules/stores/">Адреса</a>
              </div>
              <div class="ag-shop-menu__item">
                <a class="ag-shop-menu__link<?if(preg_match("#/rules/faq/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/rules/faq/">FAQ</a>
              </div>
            </div>
          </div>
        </div>
        <? endif ?>
        <!-- }}} Menu-->


        <? if(
            $arSettings["INFO_MESSAGE"]["VALUE"]
            &&
            !$_COOKIE[$sCookieName]
        ):?>
        <div class="ag-shop-card__warning main-info-message
        ag-shop-card__warning_margin_0" style="<?=
        $arSettings["INFO_STYLE"]["VALUE"]?>;margin-top:5px;">
            <div class="close-pic" onclick="$(this).parent().fadeOut();document.cookie='<?= $sCookieName?>=1;expires=<?= $sHideDate ?>;path=/;';"></div>
            <i class="ag-shop-icon ag-shop-icon--attention"></i>
            <span><?= $arSettings["INFO_MESSAGE"]["VALUE"]?></span>
        </div>    
        <? endif ?>
        

