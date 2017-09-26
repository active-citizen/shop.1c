<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<? if(
    // Заглушка
    0
    &&
    !$USER->IsAuthorized()
):?>
<? else: ?>

    <? if(
        !IS_MOBILE
        &&
        !preg_match("#^/partners/#", $_SERVER["REQUEST_URI"])
        //isset($_COOKIE["EMPSESSION"])
    ):?>
    <!-- Не выводим футер для ЛК -->

        <!-- Footer {{{-->
        <footer class="ag-shop-footer">
          <div class="ag-shop-footer__links">
            <a class="ag-shop-footer__link" href="/rules/hiw/">О проекте</a>
            <a class="ag-shop-footer__link" href="/rules/faq/">Часто задаваемые вопросы</a>
            <a class="ag-shop-footer__link" href="#" onclick="return showCommonFeedbackForm();">Обратная связь</a>
          </div>
          <div class="ag-shop-footer__copy"><small
          class="ag-shop-footer__copy-text">&copy; <?= date("Y")?>, Активный
          Гражданин. <br class="hide-on-desktop">Все права защищены. <a
          target="_blank" href="https://ag.mos.ru/site/offer#content">Публичная оферта</a></small></div>
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
                <option>взаимодействие с МФЦ</option>
                <option>проблемы с сертификатом (письмо)</option>
                <option>проблемы с Тройкой, Парковками</option>
                <option>узнать про поощрения</option>
                <option>технические ошибки</option>
                <option>прочее</option>
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
    <!-- Конец: не выводим футер для ЛК -->
    <? endif ?>

<? /* Заглушка */ ?>
<? endif ?>

        <? if(1 || !CUser::IsAuthorized()):?>
            <? if(
                !preg_match("#^/partners/#",$_SERVER["REQUEST_URI"])
                && !preg_match("#^/servitor/#",$_SERVER["REQUEST_URI"])
                && !preg_match("#^/local/.migrations/#",$_SERVER["REQUEST_URI"])
                && (
                    1
                    &&
                    !IS_MOBILE
                    //!isset($_COOKIE["EMPSESSION"])
                    //||
                    //!$_COOKIE["EMPSESSION"]
                )

            ):?>
                <!-- Забираем сессию из ag.mos.ru -->
                <script src="<?php echo CONTOUR_URL; ?>"></script>
            <? elseif(
                !preg_match("#^/partners/#",$_SERVER["REQUEST_URI"])
                && !preg_match("#^/servitor/#",$_SERVER["REQUEST_URI"])
                && !preg_match("#^/local/.migrations/#",$_SERVER["REQUEST_URI"])
                && (
                    IS_MOBILE
                    //isset($_COOKIE["EMPSESSION"])
                    //&&
                    //$_COOKIE["EMPSESSION"]
                )
            ):?>
               <!-- Забираем сессию из мобильного приложения --> 
                <script>
                $.post(
                '/.integration/auth.ajax.php?backurl='+document.location.href,
                {"session_id":'<?= $_COOKIE["EMPSESSION"];?>'},
                function(data){
                    var answer = {};
                    try{
                        answer = JSON.parse(data);
                    }
                    catch(e){
                        answer.errors = new Array(e.message);
                    }
                    
                    // Формируем блок ошибок
                    for(i in answer.errors){
                        //alert(answer.errors[i]);
                    }
                }
                );
                </script>
            <? endif ?>
        <? endif?>


    <div class="ag-shop-modal-wrap" id="rise-error" style="display:none">
      <div class="ag-shop-modal">
        <div class="ag-shop-modal__container">
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__alert">
                <i class="ag-shop-icon ag-shop-icon--attention"></i>
                <span id="rise-error-message"></span>
            </div>
          </div>
          <div class="ag-shop-modal__row">
            <div class="ag-shop-modal__buttons-wrap">
              <button class="ag-shop-modal__button
              ag-shop-modal__button--cancel" type="button"
              onclick="$('#rise-error').fadeOut();">Закрыть</button>
            </div>
          </div>
        </div>
      </div>
    </div>




<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter24583919 = new Ya.Metrika({
                    id:24583919,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/24583919" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->


  </body>
</html>
