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
              <select class="ag-shop-modal__select" id="feedback_type">
                <option disabled selected>Тип обращения</option>
                <option>Общие вопросы</option>
              </select>
            </div>
          </div>
          <div class="ag-shop-modal__row">
            <label>
              <div class="ag-shop-modal__label">Номер заказа:</div>
              <input class="ag-shop-modal__textinput" type="text" id="input-ordernum" placeholder="Введите номер заказа" value="" onkeyup="$('#order-feedback-form-ordernum').html($(this).val());">
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="order-feedback-form-ordernum" style="display:none;">
            </div>
            </label>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__label">От:</div>
            <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="feedback_name"><?
                $arUser = CUser::GetById(CUSER::GEtID())->GetNext();
                echo ($arUser["NAME"] || $arUser["LAST_NAME"]?$arUser["NAME"]." ".$arUser["LAST_NAME"]:$arUser["LOGIN"]);
            ?></div>
            
          </div>
          <div class="ag-shop-modal__row">
            <label>
              <div class="ag-shop-modal__label">Сообщение:</div>
              <textarea class="ag-shop-modal__textinput" placeholder="Что вас волнует?" id="feedback_text"></textarea>
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


        <? if(1 || !CUser::IsAuthorized()):?>
        <? if(
            !preg_match("#^/partners/#",$_SERVER["REQUEST_URI"])
            && !preg_match("#^/servitor/#",$_SERVER["REQUEST_URI"])
            && !preg_match("#^/local/.migrations/#",$_SERVER["REQUEST_URI"])

        ):?>
        <script src="<?php echo CONTOUR_URL; ?>"></script>
        <? endif ?>
        <? endif?>


  </body>
</html>
