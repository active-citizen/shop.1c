  <?
  if(
    $arResult["CATALOG_ITEM"]["PROPERTIES"]["ARTNUMBER"][0]["VALUE"]=='troyka'
    &&
    $USER->IsAuthorized()
  ):?>
  <div class="ag-shop-card__field">
    <div class="ag-shop-card__fieldname">Введите номер карты Тройка:</div>
    <div class="ag-shop-card__card-number">
      <select class="ag-shop-modal__select" id="troyka-card-number">
        <option value="">Добавить карту...</option>
        <p class="ag-shop-modal__select_arrow"></p>
      </select>
      <input class="ag-shop-card__card-number-input"
      type="tel" placeholder="0000000000" value=""
      id="newcardnum"
      >
      <div class="ag-shop-card__card-number-tooltip">
        <div
        class="ag-shop-card__card-number-tooltip-content pos_center"><img
        src="/local/assets/images/troyka_last_v.png" class="ag-shop-card__troika_margin_center">
          <p>Пример: 0004456789 (10цифр)</p>
        </div>
      </div>
    </div>
  </div>
  <script>
    $.get(
        "/.integration/troyka.getcards.ajax.php",
        function(data){
            try{
                var answer = JSON.parse(data);
            }
            catch(e){
                riseError('Не могу получить список карт');                                    
                return false;
            }

            if(answer.error){
                riseError('Не могу получить список карт:'+answer.error);
            }

            for(i in answer.cards){
                $('#troyka-card-number').append(
                    '<option value="'+answer.cards[i]+'">'
                    + answer.cards[i]
                    +'</option>'
                );   
            }
            
            // Прячем око
            if(answer.cards.length){
                $('#troyka-card-number option').last()
                    .prop('selected', true);
                $('#newcardnum').hide();
                $('.ag-shop-card__card-number-tooltip').hide();
            }

            // Выводим окошко для ввода новой карты при
            // выборе "добавить карту"
            $('#troyka-card-number').change(function(){
                if(!$('#troyka-card-number').val()){
                    $('#newcardnum').show();
                    $('.ag-shop-card__card-number-tooltip').show();
                }else{
                    $('#newcardnum').hide();
                    $('.ag-shop-card__card-number-tooltip').hide();
                }
            });
            check_filling_troika();

        }

    );

  </script>
  <? endif ?>

