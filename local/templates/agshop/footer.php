<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
        <!-- Footer {{{-->
        <footer class="ag-shop-footer">
          <div class="ag-shop-footer__links">
            <a class="ag-shop-footer__link" href="/rules/hiw/">О проекте</a>
            <a class="ag-shop-footer__link" href="/rules/faq/">Часто задаваемые вопросы</a>
            <a class="ag-shop-footer__link" href="#" onclick="return showCommonFeedbackForm();">Обратная связь</a>
          </div>
          <div class="ag-shop-footer__copy"><small class="ag-shop-footer__copy-text">&copy; 2016, Активный Гражданин. <br class="hide-on-desktop">Все права защищены. <a href="#">Публичная оферта</a></small></div>
        </footer>
        <!-- }}} Footer-->
    </div>

    <div class="ag-shop-modal-wrap" id="common-feedback-form" style="display:none">
      <div class="ag-shop-modal">
        <div class="ag-shop-modal__container">
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__select-wrap">
              <select class="ag-shop-modal__select">
                <option disabled selected>Тип обращения</option>
                <option>Тип 1</option>
                <option>Тип 2</option>
                <option>Тип 3</option>
              </select>
            </div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">От:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked">Константин Констанинович Иванов </div>
          </div>
          <div class="ag-shop-modal__row">
            <label>
              <div class="ag-shop-modal__label">Сообщение:</div>
              <textarea class="ag-shop-modal__textinput" placeholder="Что вас волнует?"></textarea>
            </label>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__buttons-wrap">
              <button class="ag-shop-modal__button" type="button" onclick="return sendCommonFeedbackForm();">Отправить</button>
              <button class="ag-shop-modal__button ag-shop-modal__button--cancel" type="button" onclick="return hideCommonFeedbackForm();">Отмена</button>
            </div>
          </div>
        </div>
      </div>
    </div>


        <? if(!CUser::IsAuthorized()):?>
        <? 
            require_once(realpath(dirname(__FILE__)."/../../..")."/.integration/secret.inc.php");
            $url = $AG_KEYS["uat"]["url"];
            if($_SERVER["HTTP_HOST"]=='shop.ag.mos.ru')$url = $AG_KEYS["prod"]["url"];
        ?>
        <script src="<?php echo $url; ?>"></script>
        <? endif?>


  </body>
</html>
