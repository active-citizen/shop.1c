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
    var re = 
    /^\d{5}$/;
    $('#card-order-confirm-button-troyka').html('Оформить заказ'); 
    if(
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
        !re.test($('.ag-shop-card__card-number-input').val())
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
                    $('#confirm-code').parent().parent().hide();
                    $('#card-order-confirm-button-troyka').html(
                        'Ошибка парсинга ответа сервера:'+data
                    );
                }
                if(answer.error){
                    $('#confirm-code').parent().parent().hide();
                    $('#card-order-confirm-button-troyka').html(
                        'Ошибка отправки SMS'+':'+answer.error
                    );
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
                    $('#card-order-confirm-button-troyka').html(
                        "Ошибка парсинга ответа сервера:"+data
                    );
                }

                if(answer.error){
                    alert(answer.error);
                    return false;
                }

                $('#card-order-confirm-button-troyka').html(data);
            }
        });

    }
    else if(re.test($('#troyka-card-number').val())){
        alert(111);
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
    /*
    $.post(
        
    );
    */
}

