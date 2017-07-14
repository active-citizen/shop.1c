var totalStoreId = 0;

$(document).ready(function(){
    // Первоначальная проверка номера
    $('#troyka-card-number').each(function(){
        check_filling_troika(); 
    });

    // Проверяем номер каждое изменение поля выбора прикреплённых   
    $('#troyka-card-number').change(function(){check_filling_troika();});
    // Проверяем номер каждый введённый символ в поле номера
    $('.ag-shop-card__card-number-input').keyup(function(){check_filling_troika();});
   

});

function check_filling_code(){
    var re = /^\d{5}$/;
    var re2 = /^\d{10}$/;
    $('#card-order-confirm-button-troyka').html('Оформить заказ'); 
    if(
        !re2.test($('#troyka-card-number').val())
        &&
        !re.test($('#confirm-code').val())
    ){
        // Снимаем с кнопки отправки заказа события
        $('#card-order-confirm-button-troyka').attr('onclick','return false;');

        $('#card-order-confirm-button-troyka').prop('disabled',true);
        $('#card-order-confirm-button-troyka').addClass('troyka-submit-disabled');
        $('#confirm-code').addClass('troyka-number-wrong');
    }
    else{
        // Снимаем на кнопку функцию заказа тройки
        $('#card-order-confirm-button-troyka').attr(
            'onclick','return buyTroika();'
        );

        $('#card-order-confirm-button-troyka').prop('disabled',false);
        $('#card-order-confirm-button-troyka').removeClass('troyka-submit-disabled');
        $('#confirm-code').removeClass('troyka-number-wrong');
    }
}



function check_filling_troika(){
    var re = /^\d{10}$/;

    if(
        $('#troyka-card-number').val()==''
        &&
        !re.test($('#newcardnum').val())
    ){
        // Снимаем с кнопки отправки заказа события
        $('.ag-shop-card__submit-button').attr('onclick','return false;');

        $('.ag-shop-card__submit-button').prop('disabled',true);
        $('.ag-shop-card__submit-button').addClass('troyka-submit-disabled');
        $('.ag-shop-card__card-number-input').addClass('troyka-number-wrong');
    }
    else{
        // Снимаем на кнопку функцию заказа тройки
        $('.ag-shop-card__submit-button').attr('onclick','return confirmTroika();');

        $('.ag-shop-card__submit-button').prop('disabled',false);
        $('.ag-shop-card__submit-button').removeClass('troyka-submit-disabled');
        $('.ag-shop-card__card-number-input').removeClass('troyka-number-wrong');
    }
}

function confirmTroika(){

    var re = /^\d{10}$/
    totalStoreId = $("input[name='place']:checked").val();
    $('#troyka-confirm-store').html($('.ag-shop-card__selected-place-station').html());
    $('#troyka-confirm-store-id').html(totalStoreId);
    $('#confirm-card').html(
        $('#troyka-card-number').val()
        ?
        $('#troyka-card-number').val()
        :
        $('#newcardnum').val()
    );


    $('#troyka-error').hide();
    $('#confirm-code').parent().parent().hide();
    $('#card-order-confirm-troika').show('fast');

    // Название товара
    $('#card-order-confirm-troika #confirm-name').html(
        $('h2.ag-shop-card__header-title').html()
    );

    if(
        !$('#troyka-card-number').val()
        &&
        re.test($('#newcardnum').val())
    ){
        $('#card-order-confirm-button-troyka').prop('disabled', true);
        $('#card-order-confirm-button-troyka').addClass('troyka-submit-disabled');
        $('#card-order-confirm-button-troyka').html('Отправка SMS...'); 
        /// сделать ajax общего вида
        $.ajax({
            "url"   :   "/.integration/troyka.sendsms.ajax.php",
            "type"  :   "POST",
            "data"  :   {
                "cardnumber":   $('#newcardnum').val(),
            },
            "success":  function(data){
                try{
                    var answer = JSON.parse(data);
                }
                catch(e){
                    troykaRiseError(data);
                    return false;
                }
                if(answer.error){
                    troykaRiseError(answer.error);
                    return false;
                }else{
                    $('#confirm-code').parent().parent().show();
                    check_filling_code();
                }
            },
            "error" :   function(data){
                try{
                    var answer = JSON.parse(data);
                }
                catch(e){
                    troykaRiseError(data);
                }

                if(answer.error){
                    troykaRiseError(answer.error);
                    return false;
                }

                $('#card-order-confirm-button-troyka').html(data);
            }
        });

    }
    else if(re.test($('#troyka-card-number').val())){
    }


    // Цена товара
    $('#card-order-confirm-troika #confirm-price').html(
        parseInt(
            $('.ag-shop-card__image-points .ag-shop-item-card__points-count').html()
        )
    );

    // Первоначальная проверка кода подтведжения
    $('#confirm-code').each(function(){
        check_filling_code(); 
    });

    // Проверяем номер каждый введённый символ в поле номера
    $('#confirm-code').keyup(function(){check_filling_code();});
 

}

function buyTroika(){

    $('#card-order-confirm-button-troyka').prop('disabled', true);
    $('#card-order-confirm-button-troyka').addClass('troyka-submit-disabled');
    $('#card-order-confirm-button-troyka').html('Отправка..'); 


    var newcardnum = $('#newcardnum').val();
    var cardnum = $('#troyka-card-number').val();

    var troyka_num = cardnum?cardnum:newcardnum;
    var code = $('#confirm-code').val();

    var postRequest ={
        "cardnumber":   troyka_num,
    };
    if($('#troyka-card-number').val()=='')
        postRequest["code"] = code;

    


    $.ajax({
        "url"   :   "/.integration/troyka.checkcard.ajax.php",
        "type"  :   "POST",
        "data"  :   postRequest,
        "success":  function(data){
            try{
                var answer = JSON.parse(data);
            }
            catch(e){
                troykaRiseError(data,false)
                $('#card-order-confirm-button-troyka').prop('disabled', false);
                $('#card-order-confirm-button-troyka').removeClass(
                    'troyka-submit-disabled'
                );
                $('#card-order-confirm-button-troyka').html('Оформить заказ'); 
                return false;
            }
            if(answer.error){
                troykaRiseError(answer.error,false);
                $('#card-order-confirm-button-troyka').prop('disabled', false);
                $('#card-order-confirm-button-troyka').removeClass(
                    'troyka-submit-disabled'
                );
                $('#card-order-confirm-button-troyka').html('Оформить заказ'); 
            return false;
            }else{


                var add_basket_url = "/profile/order/order.ajax.php?add_to_basket=1&id="
                +totalOfferId
                +"&quantity="+$('#confirm-amount').html()
                +"&store_id="+$('#confirm-store-id').html();
                
                // добавляем в корзину
                $('#card-order-confirm-button-troyka').html('Обработка заказа...');
                $('#card-order-confirm-button-troyka').prop('disabled', true);
                $('#card-order-confirm-button-troyka').addClass(
                    'troyka-submit-disabled'
                );

                $('#card-order-confirm-button-troyka').attr( "onclick" ,"return false;");
                
                $.get(
                    add_basket_url,
                    function(data){
                        data = data.replace(/'/gi,'"');
                        var answer = JSON.parse(data);
                        if(answer.STATUS!='OK'){
                            ag_ci_rise_error(answer.MESSAGE);
                            return false;
                        }
                        
                        $.get(
                            "/profile/order/order.ajax.php?add_order=Y&store_id="+answer.store_id+"&troyka="+troyka_num,
                            function(data){
                                var answer = JSON.parse(data);
                                if(answer.redirect_url){
                                    document.location.href=answer.redirect_url;
                                }
                                else{
                                   // Чистим корзину, если заказ неудачен
                                    $.get(
                                        "/profile/order/order.ajax.php?clear_basket",
                                        function(){
                                            $('#order-process-done').css('display','none');
                                            $('.ok-button').css('display','block');
                                            var error_text = '';
                                            for(i in answer.order.ERROR){
                                                error_text += i+": "+answer.order.ERROR[i];
                                            }
                                            $('.ag-shop-modal__container').append('<div class="error">'+error_text+'</div>')
            //                              $('.ag-shop-modal-wrap').fadeOut('fast');
                                        }
                                    );
                                }
                            }
                        );
                    }
                );
                


                $('#card-order-confirm-troika').hide('fast');
            }
        },
        "error" :   function(data){
            try{
                var answer = JSON.parse(data);
            }
            catch(e){
                troykaRiseError(data,false);
            }

            if(answer.error){
                troykaRiseError(answer.error,false);
            }

            $('#card-order-confirm-button-troyka').prop('disabled', false);
            $('#card-order-confirm-button-troyka').removeClass(
                'troyka-submit-disabled'
            );
            $('#card-order-confirm-button-troyka').html('Оформить заказ'); 
            return false;
        }
    });

}

function troykaRiseError(errorText,hideCode = true){
    $('#troyka-error').show();
    $('#troyka-error').html( errorText );
    if(hideCode)$('#confirm-code').parent().parent().hide();
}

