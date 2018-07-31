<? if($arResult["AUCTION"]):?>
<div class="ag-shop-modal-wrap" style="display:none"
id="card-order-confirm-auction">
  <div class="ag-shop-modal">
    <div class="ag-shop-modal__container">
       <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Заказ:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-name">Сумка городская</div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Цена:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-price">
            <input id="bet-price" class="bet-confirm-num" value=""
            change="return checkBetPriceInput()">
            <div class="ag-shop-modal__alert" id="price-hint" style="display: none;">
            <i class="ag-shop-icon
                  ag-shop-icon--attention"></i>
                  <span>Предлагаемая цена не может быть меньше минимальной</span>
            </div>
        </div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Количество:</div>

            <div class="ag-shop-card__count" id="bet-amount">
              <button class="ag-shop-card__count-button ag-shop-card__count-button--sub" 
                type="button"></button>
              <div style="padding-top: 3px;" class="ag-shop-card__count-number">1</div>
              <button class="ag-shop-card__count-button ag-shop-card__count-button--add" 
                type="button">
                  <div class="ag-shop-modal__alert"
                  id="counter-hint" class="counter-hint"
                  style="display: none"><i class="ag-shop-icon
                  ag-shop-icon--attention"></i><span></span></div>
              </button>
            </div>

      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Стоимость:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="confirm-cost">
            <input id="bet-cost" class="bet-confirm-num" value="" disabled>
        </div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Получение:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="troyka-confirm-store">
            <span></span>
        </div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="troyka-confirm-store-id" style="display:none;"></div>
      </div>

      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__buttons-wrap">
          <button class="ag-shop-modal__button"
          id="card-order-confirm-button-bet" type="button"
          onclick="return betSet();">Сделать ставку</button>
          <button class="ag-shop-modal__button ag-shop-modal__button--cancel" type="button" onclick="$('.ag-shop-modal-wrap').fadeOut();">Отмена</button>
        </div>
      </div>
    </div>
  </div>
</div>
<? endif ?>

