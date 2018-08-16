<div class="ag-shop-modal-wrap" style="display:none" id="card-order-confirm">
  <div class="ag-shop-modal">
    <div class="ag-shop-modal__container">
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Подтверждение заказа</div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Заказ:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-name">Сумка городская</div>
      </div>
      <div class="properties"></div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Цена:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="confirm-price"><span>415</span> <span class="balls">баллов</span></div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Единица:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="confirm-unit"><?=
        $arResult["CATALOG_ITEM"]["PROPERTIES"]["QUANT"][0]["VALUE"]
        ?></div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Количество:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="confirm-amount">1</div>
      </div>
      <div class="ag-shop-modal__row" id="confirm-total-row" style="display:none">
        <div class="ag-shop-modal__label">Итого:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked"
        id="confirm-total">1</div>
      </div>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__label">Получение:</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-store"><span>415</span> баллов</div>
        <div class="ag-shop-modal__text ag-shop-modal__text--marked" id="confirm-store-id" style="display:none;"></div>
      </div>
      <?
      if(
          $arResult["CATALOG_ITEM"]["PROPERTIES"]["CANCEL_ABILITY"]
          [0]["VALUE_ENUM"] != 'да'
      ):?>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__alert">
            <i class="ag-shop-icon ag-shop-icon--attention"></i>
            <span>
                При нажатии кнопки «Оформить заказ» баллы, потраченные на 
                данное поощрение, не возвращаются.
            </span>
        </div>
      </div>
      <? endif?>
      <div class="ag-shop-modal__row">
        <div class="ag-shop-modal__buttons-wrap">
          <button class="ag-shop-modal__button" id="card-order-confirm-button" type="button" onclick="return productConfirmNext();">Оформить заказ</button>
          <button class="ag-shop-modal__button ag-shop-modal__button--cancel" type="button" onclick="$('.ag-shop-modal-wrap').fadeOut();">Отмена</button>
        </div>
      </div>
    </div>
  </div>
</div>


