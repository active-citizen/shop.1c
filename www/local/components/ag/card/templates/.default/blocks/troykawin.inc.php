<div class="ag-shop-modal-wrap" style="display:none"
id="card-order-confirm-troika">
  <div class="ag-shop-modal">
    <div class="ag-shop-modal__container">
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Подтверждение заказа</div>
      </div>
      <div class="ag-shop-modal__row" style="display:none;">
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" >
        <input class="ag-shop-card__card-number-input" id="confirm-code" type="tel"
        placeholder="00000" value="" id="confirm-code">
        </div>
        <div class="ag-shop-modal__label"><br>На ваш мобильный телефон <b><?=
        str_replace("u","",$arResult["USER_INFO"]['LOGIN'])?></b> выслан код подтверждения для
        пополнения карты Тройка <span id="troykanum"></span>.<br>
        Вам необходимо указать код для осуществления операции.<br>
        Если вы не получили код в течение 5 минут, пожалуйста, запросите
        новый код<br/>
        </div>
      </div>
       <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Заказ:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-name">Сумка городская</div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Номер карты:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="confirm-card"></div>
      </div>
       <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Цена:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-price"><span>415</span> баллов</div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Получение:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="troyka-confirm-store"><span>415</span> баллов</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="troyka-confirm-store-id" style="display:none;"></div>
      </div>
      <div class="ag-shop-modal__row">
        <?
        if(
            $arResult["CATALOG_ITEM"]["PROPERTIES"]["CANCEL_ABILITY"][0]["VALUE_ENUM"]
            !=
            'да'
        ):?>
        <div class="ag-shop-modal__alert"><i class="ag-shop-icon ag-shop-icon--attention"></i><span>При нажатии кнопки «Оформить заказ» баллы, потраченные на данное поощрение, не возвращаются.</span></div>
      </div>
      <? endif?>
      <div class="ag-shop-modal__row" style="display:none" id="troyka-error">

      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__buttons-wrap">
          <button class="ag-shop-modal__button"
          id="card-order-confirm-button-troyka" type="button" onclick="return productConfirmNext();">Оформить заказ</button>
          <button class="ag-shop-modal__button ag-shop-modal__button--cancel" type="button" onclick="$('.ag-shop-modal-wrap').fadeOut();">Отмена</button>
        </div>
      </div>
    </div>
  </div>
</div>

