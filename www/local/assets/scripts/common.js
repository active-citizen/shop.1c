$(document).ready(function() {

    if(document.location.hash){
        var id = document.location.hash.split("#")[1];
        var re = /&/;
        if(id && !re.test(id))
            $('#store-click-'+id).trigger('click');
        if(id && !re.test(id))
            $('#faq-click-'+id).trigger('click');
    }

    $('.hash-navigation').click(function(){
        document.location.hash = $(this).attr("href").split("#")[1];
    });

  /**
   * Переключение вкладок
   */
  $('.ag-shop-filter__trigger').click(function(){

    if(
        $(this).hasClass('ag-shop-filter__trigger--active')
        && $('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
        $('#'+$(this).attr("rel")).removeClass('filter-active')
    }else if(
        $(this).hasClass('ag-shop-filter__trigger--active')
        && !$('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
        $('.ag-shop-filter__variants').removeClass('filter-active');
        $('#'+$(this).attr("rel")).addClass('filter-active');
    }
    else if(
        !$(this).hasClass('ag-shop-filter__trigger--active')
        && $('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
    }
    else if(
        !$(this).hasClass('ag-shop-filter__trigger--active')
        && !$('#'+$(this).attr("rel")).hasClass('filter-active')
    ){
        $('.ag-shop-filter__variants').removeClass('filter-active');
        $('#'+$(this).attr("rel")).addClass('filter-active');
    }


    $('.ag-shop-filter__variants').find(":before").css("left","200");

    $('.ag-shop-filter__trigger').removeClass('ag-shop-filter__trigger--active');
    
    $(this).addClass('ag-shop-filter__trigger--active');
    
    
  });
  
  
  /**
   * Выбор элементов фильтра
   */
  $('.ag-shop-filter__variants input').click(function(){
    if($(this).parent().parent().find('input:checked').length>3){
       $(this).removeAttr('checked');
        riseError('Нельзя выбирать более 3 элементов');
        return false;
    }
    return true;
  });


  $('.ag-shop-filter__variants input').change(function(){
    if($(this).parent().parent().find('input:checked').length>3){
        $(this).removeAttr('checked');
        riseError('Нельзя выбирать более 3 элементов');
        return false;
    }
    input_variant_click($(this));
    
    ag_filter();
    return false;
  });   

    // Клик на фильтрацию и сортировку
    $('.ag-shop-menu__link_flag').click(function(){
        $('.ag-shop-menu__link_flag').removeClass('ag-shop-menu__link--active');
        $(this).addClass('ag-shop-menu__link--active');
        $('#ag-flag').val($(this).attr('rel'));
        ag_filter();
        return false;
    });
    
    $('.ag-shop-menu__link_sorting').click(function(){
        $('.ag-shop-menu__link_sorting').removeClass('ag-shop-menu__link--active');
        $(this).addClass('ag-shop-menu__link--active');
        $('#ag-sorting').val($(this).attr('rel'));
        ag_filter();
        return false;
    })
    /*событие для кнопки фильтр js-filters-toggle*/    
    $('.js-filters-toggle').click(function(){
        $(this).toggleClass('ag-shop-menu__link--active');
        $('#ag-sorting').val($(this).attr('rel'));
        ag_filter();
        return false;
    });

    /**
     * переключение предпросмотра картинок
     */
    $('.ag-shop-card__preview').click(function(){
        $(this).parent().find('.ag-shop-card__preview').removeClass('ag-shop-card__preview--active');
        $(this).addClass('ag-shop-card__preview--active');
        $('.ag-shop-card__image-container').css('background-image','url('+$(this).attr('rel')+')');
    });
    
    /**
     * Количество заказанного
     */
    $('.ag-shop-card__count-button').click(function(){
        
        var avaible = parseInt(arOffers[totalOfferId]['STORAGES'][$('input[name="place"]:checked').val()]);
        var mon_limit = parseInt($('#mon-limit').html());
        
        var count = parseInt($('.ag-shop-card__count-number').html());
        var price = $('.ag-shop-item-card__points-count').html();
        price = price.replace(/ /gi, '');
        price = parseInt(price);
        if(
            $(this).hasClass('ag-shop-card__count-button--add') 
            && ((count+1)*price)>accountSum
        ){
            counterAlert('Для заказа большего количества у вас недостаточно баллов');
        }
        else if(
            $(this).hasClass('ag-shop-card__count-button--add') 
            && count>=avaible
        ){
            counterAlert('В выбранном месте получения нельзя заказать больше');
        }
        else if(
            $(this).hasClass('ag-shop-card__count-button--add') 
            && mon_limit>0 
            && count>=mon_limit
        ){
            counterAlert('В этом месяце вы не можете заказать больше');
        }
        else if(
            $(this).hasClass('ag-shop-card__count-button--add') 
        ){
            count++;
        }
        else if($(this).hasClass('ag-shop-card__count-button--sub') && count>1){
            count--;
        }
        $('.ag-shop-card__count-number').html(count);
        $('#ag-shop-card__total-points').html(count*price);
        $('.ag-shop-card__submit-button strong').html(count*price);
        $('.ag-shop-card__submit-button span').html(pointsForms(count*price));
    });

    function counterAlert(txt){
        $('#counter-hint span').html(txt);
        $('#counter-hint').fadeIn('slow');
        setTimeout(function(){
            $('#counter-hint').fadeOut('slow');
        },3000);
    }

    function pointsForms(points){
        if(points%100==11){
            return 'баллов';
        }
        else if(points%100==12){
            return 'баллов';
        }
        else if(points%100==13){
            return 'баллов';
        }
        else if(points%100==14){
            return 'баллов';
        }
        else if(points%10==1){
            return 'балл';
        }
        else if(points%10==2){
            return 'балла';
        }
        else if(points%10==3){
            return 'балла';
        }
        else if(points%10==4){
            return 'балла';
        }
        else{
            return 'баллов';
        }
    }


    /**
     * Выбор конкретного предложения
    */
    $('.ag-shop-card__sizes input').click(function(){

        let switched = $(this).attr("switched");
        let crossValues = $(this).attr('cross-values').split(',');
        let availStores = $(this).attr('stores').split(',');
        let offers =  $(this).attr('offers').split(',');
        let pics = $(this).attr('pics').split('|');
        var propCode = $(this).attr("name");

        $(this).parent().parent().find('input').attr('switched','off');

        if(switched=='on'){
            $(this).attr('switched','off');
            $(this).prop('checked',false);
            switched = false;
        }
        else{
            $(this).attr('switched','on');
            $(this).prop('checked',true);
            switched = true;
        }

        // Если выбранный пункт оказывается включенным, то
        // выключаем значения свойств, которых нет в паре с
        // торговым предложением. И складов
        if(switched){
            $('.ag-shop-card__sizes input').each(function(){
                if($(this).attr("name")==propCode)return true;
                // Если это значение в паре со свойством - включем
                if(crossValues.indexOf($(this).val())>=0)
                    $(this).prop('disabled',false);
                // Если не в паре - выключаем
                else{
                    $(this).prop('disabled',true);
                    $(this).prop('checked',false);
                }
            });
        }
        else{
            $('.ag-shop-card__sizes input').each(function(){
                if($(this).attr("name")==propCode)return true;
                $(this).prop('disabled',false);
            });
        }
        let stores = availStores;
        $('.ag-shop-card__sizes input:checked').each(function(){
            console.log($(this).attr('stores'));
            stores = Intersection(stores,$(this).attr('stores').split(','));
        });
        console.log(stores);
        $('.ag-shop-card__places input').each(function(){
            if(stores.indexOf($(this).val())>=0)
                $(this).prop('disabled',false);
            else{
                $(this).prop('disabled',true);
                if($(this).prop('checked')){
                    $('.ag-shop-card__selected-place').addClass('hidden');
                    $('.amounter').removeClass('amounter--on');
                    $('.amounter').addClass('amounter--off');
                    totalStoreId = 0;
                }
                $(this).prop('checked',false);
            }
        });

        if($('.ag-shop-card__sizes input:checked').length<=0){
            $('.ag-shop-card__places input').prop("disabled",false);
        }
    });

    loadComments();

});

// Функция для вычисления пересечения массивов
function Intersection(A,B){
    var M=A.length, N=B.length, C=[];
    for (var i=0; i<M; i++)
     { var j=0, k=0;
       while (B[j]!==A[i] && j<N) j++;
       while (C[k]!==A[i] && k<C.length) k++;
       if (j!=N && k==C.length) C[C.length]=A[i];
     }
   return C;
}



function input_variant_click(obj){
    var triggerTab = $('span[rel="'+obj.parent().parent().attr("id")+'"]');
    var fieldTab = obj.parent().parent();
    var arrOptions = Array();
    
    triggerTab.html('');
    fieldTab.find('input').each(function(){
        if($(this).is(':checked')){
            arrOptions.push($(this).attr("title"));
        }
    });
    triggerTab.html(arrOptions.join(", "));
    if(!triggerTab.html())triggerTab.html(triggerTab.attr('alltitle'));

    return true;
}


function selectStorage(obj){

    let storageId = $(obj).val();
    let switched = $(obj).attr("switched");
    let offers =  $(obj).attr('offers').split(',');
    var propsVals = $(obj).attr("propsvals").split(',');

    $(this).parent().parent().find('input').attr('switched','off');

    if(switched=='on'){
        $(obj).attr('switched','off');
        $(obj).prop('checked',false);
        switched = false;
    }
    else{
        $(obj).attr('switched','on');
        $(obj).prop('checked',true);
        switched = true;
    }

    //Если мы выбрали место, удаляем класс ошибки
    $('.js-choose__place').removeClass('ag-shop-card__field--error');


    $('.ag-shop-card__selected-place-table').html('');
    var value= '';
    if(value = arStorages[storageId].ADDRESS)$('.ag-shop-card__selected-place-table').append(getStorageRow('Адрес',value));
    if(value = arStorages[storageId].PHONE)$('.ag-shop-card__selected-place-table').append(getStorageRow('Телефон',value));
    if(value = arStorages[storageId].SCHEDULE)$('.ag-shop-card__selected-place-table').append(getStorageRow('Режим',value));
    if(value = arStorages[storageId].EMAIL)
        $('.ag-shop-card__selected-place-table').append(getStorageRow('Сайт','<a href="'+value+'" target="_blank">'+
        arStorages[storageId].EMAIL_SHORT
        +'</a>'));
    $('.ag-shop-card__selected-place-station').html(arStorages[storageId].TITLE);
    //$('.ag-shop-card__remaining-count .ag-shop-card__remaining-count-text').css('display','none');
    $('.ag-shop-card__remaining-count .ag-shop-card__remaining-count-text').each(function(){
        if(
            arOffers[totalOfferId]['STORAGES'][storageId]>=parseInt($(this).attr('fromAmmount'))  
            &&
            arOffers[totalOfferId]['STORAGES'][storageId]<=parseInt($(this).attr('toAmmount')) 
        ){
            $(this).css('display','inline-block');
            // Если на складе меньше, чем уже набрали
            if(arOffers[totalOfferId]['STORAGES'][storageId]<parseInt($('.ag-shop-card__count-number').html())){
                $('.ag-shop-card__count-number').html(arOffers[totalOfferId]['STORAGES'][storageId]);
                var count = parseInt($('.ag-shop-card__count-number').html());
                var price = parseInt($('.ag-shop-item-card__points-count').html());
                $('.ag-shop-card__count-number').html(count);
                $('#ag-shop-card__total-points').html(count*price);
                $('.ag-shop-card__submit-button strong').html(count*price);
            }
        }
    });
    updateCounter();
    if(switched){
        totalStoreId = storageId;
        $('.ag-shop-card__selected-place').removeClass('hidden');
        $('.amounter').removeClass('amounter--off');
        $('.amounter').addClass('amounter--on');
    }else{
        totalStoreId = 0;
        $('.ag-shop-card__selected-place').addClass('hidden');
        $('.amounter').removeClass('amounter--on');
        $('.amounter').addClass('amounter--off');
    }

    loadComments();
}

function updateCounter(){
    var avail = arOffers[totalOfferId]['STORAGES'][$('input[name="place"]:checked').val()];
    if( parseInt($('.ag-shop-card__count-number').html()) > avail)
        $('.ag-shop-card__count-number').html(avail);
}


function getStorageRow(title,value){
    return '<tr><td>'+title+':</td><td>'+value+'</td></tr>'
}


function addComment(){
    var comment = $('.ag-shop-card__review-form-input').val();
    var mark = $('.ag-shop-card__review-form-container .ag-shop-card__rating').attr("rel");
    var productId = $('.ag-shop-card__review-form').attr("prodictid");
    if(!mark){
        riseError('Вы не поставили оценку');
        return false;
    }
    if(!productId){
        riseError('Не указан ID продукта');
        return false;
    }
    if(comment.length<3){
        riseError('Вы не написали текст комментария');
        return false;
    }
    $.post(
        "/local/components/ag/card/addcomment.ajax.php",
        {
            "comment":comment,
            "mark":mark,
            "productid":productId
        },
        function(data){
            try{
                var answer = JSON.parse(data);
            }   
            catch(e){
                riseError(data);
            }
        
            if(answer.error && !answer.success){
                riseError(answer.error);
                return false;
            }
            $('.ag-shop-card__review-form').fadeOut();
            loadComments();
        }
    );
    
    
    return false;
}

function setMark(obj){
    $('.ag-shop-card__review-form-container .ag-shop-card__rating').attr("rel",parseInt($(obj).attr('rel')));
    $('.ag-shop-card__review-form-container .ag-shop-card__rating .ag-shop-item-card__rating-item').removeClass('ag-shop-slider-card__rating-item--active');
    $('.ag-shop-card__review-form-container .ag-shop-card__rating .ag-shop-item-card__rating-item').each(function(){
        if(parseInt($(this).attr('rel'))<=parseInt($(obj).attr('rel')))
            $(this).addClass('ag-shop-slider-card__rating-item--active');
    })
    
}

function loadComments(){
    var productId=$('.ag-shop-card__review-form').attr("prodictid");
    $('.ag-shop-card__reviews').load("/local/components/ag/card/comments.ajax.php?productid="+productId);
}

function productConfirm(){
    totalStoreId = $("input[name='place']:checked").val();
    // Не выбран склад
    if(!totalStoreId){
        riseError('Выберите склад получения');
        $('.js-choose__place').addClass('ag-shop-card__field--error');
        return false;
    }
    $('#card-order-confirm').fadeIn();
    $('#confirm-name').html($('.ag-shop-card__header-title').html());
    $('#confirm-price span').html($('.ag-shop-item-card__points-count').html());
    $('#confirm-price span.balls').html($('.ag-shop-item-card__points-text').html());
    
    $('#confirm-unit').html($('.ag-shop-card__total-points').html());
    $('#confirm-amount').html($('.ag-shop-card__count-number').html());
    $('#confirm-cost span').html($('#ag-shop-card__total-points').html());
    $('#confirm-store').html($('.ag-shop-card__selected-place-station').html());
    $('#confirm-store-id').html(totalStoreId);
    if(parseInt($('#confirm-amount').html())>1){
        $('#confirm-total-row').css('display','block');
        $('#confirm-total').html(
            $('.ag-shop-card__submit-button strong').html()
            + ' ' 
            + $('.ag-shop-card__submit-button span').html()
        );
    }
    else{
        $('#confirm-total-row').css('display','none');
    }
}

function productConfirmNext(){
    
    var add_order_url = "/profile/order/order.ajax.php?add_order=1&id="
    +totalOfferId
    +"&quantity="+$('#confirm-amount').html()
    +"&store_id="+$('#confirm-store-id').html();
    
    $('#card-order-confirm-button').html('Обработка заказа...');
    $('#card-order-confirm-button').attr( "onclick" ,"return false;");
    $('#card-order-confirm-button').css("opacity","0.6");
    
    $.get(
        add_order_url,
        function(data){
            answer = {};
            try{
                var answer = JSON.parse(data);
            }
            catch(err){
                $('.ag-shop-modal__container .ag-shop-card__warning')
                    .remove();
                $('.ag-shop-modal__container')
                    .append(
                        '<div class="ag-shop-card__warning">'
                        +data+'</div>'
                    );
                $("#card-order-confirm-button").html("Ошибка");
                setTimeout(function(){
                    $("#card-order-confirm-button").fadeOut();
                },1000);
            }
            if(answer.redirect_url){
                document.location.href=answer.redirect_url;
            }
            else{
                $('#order-process-done').css('display','none');
                $('.ok-button').css('display','block');
                var error_text = '';
                for(i in answer.order.ERROR){
                    error_text += ""+answer.order.ERROR[i]+'<br/>';
                }
                error_text += '';
                $('.ag-shop-modal__container .ag-shop-card__warning')
                    .remove();
                $('.ag-shop-modal__container')
                    .append(
                        '<div class="ag-shop-card__warning">'
                        +error_text+'</div>'
                    );
                $("#card-order-confirm-button").html("Ошибка");
                setTimeout(function(){
                    $("#card-order-confirm-button").fadeOut();
                },1000);
            }
        }    
    );
    return false;
    
    
    var add_basket_url = "/profile/order/order.ajax.php?add_to_basket=1&id="
    +totalOfferId
    +"&quantity="+$('#confirm-amount').html()
    +"&store_id="+$('#confirm-store-id').html();
    
    // добавляем в корзину
    $('#card-order-confirm-button').html('Обработка заказа...');
    $('#card-order-confirm-button').attr( "onclick" ,"return false;");
    $('#card-order-confirm-button').css("opacity","0.6");
    
    
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
                "/profile/order/order.ajax.php?add_order=Y&store_id="
                    +answer.store_id,
                function(data){
                    answer = {};
                    try{
                        var answer = JSON.parse(data);
                    }
                    catch(err){
                        $('.ag-shop-modal__container .ag-shop-card__warning')
                            .remove();
                        $('.ag-shop-modal__container')
                            .append(
                                '<div class="ag-shop-card__warning">'
                                +data+'</div>'
                            );
                        $("#card-order-confirm-button").html("Ошибка");
                        setTimeout(function(){
                            $("#card-order-confirm-button").fadeOut();
                        },1000);
                    }
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
                                    error_text += ""+answer.order.ERROR[i]+'<br/>';
                                }
                                error_text += '';
                                $('.ag-shop-modal__container .ag-shop-card__warning')
                                    .remove();
                                $('.ag-shop-modal__container')
                                    .append(
                                        '<div class="ag-shop-card__warning">'
                                        +error_text+'</div>'
                                    );
                                $("#card-order-confirm-button").html("Ошибка");
                                setTimeout(function(){
                                    $("#card-order-confirm-button").fadeOut();
                                },1000);
                           }
                        );
                    }
                }
            );
        }
    );
}


function showCommonFeedbackForm(){
    $('#common-feedback-form').fadeIn();
    return false;
}

function hideCommonFeedbackForm(){
    $('#common-feedback-form').fadeOut();
    return false;
}

function sendCommonFeedbackForm(){

    var post = {};

    post["type"] = $('#feedback_type').val();
    post["name"] = $('#feedback_name').html();
    post["text"] = $('#feedback_text').val();
    post["order"] = $('#order-feedback-form-ordernum').html();
    
    if(!post["text"]){
        riseError('Введите сообщение');
        return false;
    }
    if(!post["type"]){
        riseError('Выберите тип обращения');
        return false;
    }

    $('#common-feedback-form .ag-shop-modal__container div').hide();
    $('#common-feedback-form .ag-shop-modal__container').prepend('<div class="form-success">Сообщение отправляется</div>');
    
    $.post(
        "/profile/order_feedback.ajax.php",
        post,
        function(data){
            var answer = JSON.parse(data);
            if(!answer.error)
                setTimeout(function(){
                    $('#common-feedback-form').fadeOut(function(){
                        $('#common-feedback-form .ag-shop-modal__container div').show();
                        $('#common-feedback-form .ag-shop-modal__container .form-success').remove();        
                    });
                },2000);
            else
                riseError(answer.error);
        }
    );
    return false;
    
    /*
    var post = {};
    post["type"] = $('#feedback_type').val();
    post["name"] = $('#feedback_name').html();
    post["text"] = $('#feedback_text').val();

    if(!post["text"]){
        alert('Введите сообщение');
        return false;
    }
    if(!post["type"]){
        alert('Выберите тип обращения');
        return false;
    }
    
    $('#common-feedback-form .ag-shop-modal__container div').hide();
    $('#common-feedback-form .ag-shop-modal__container').prepend('<div class="form-success">Сообщение отправляется</div>');

    $.post(
        "/profile/common_feedback.ajax.php",
        post,
        function(data){
            setTimeout(function(){
                $('#common-feedback-form').fadeOut(function(){
                    $('#common-feedback-form .ag-shop-modal__container div').show();
                    $('#common-feedback-form .ag-shop-modal__container .form-success').remove();        
                });
            },2000);
        }
    );

    
    return false;
    */
}

function showOrdersFeedbackForm(orderNum){
    $('#order-feedback-form-ordernum').html(orderNum);
    $('#orders-feedback-form').fadeIn();
    return false;
}

function hideOrdersFeedbackForm(){
    $('#orders-feedback-form').fadeOut();
    return false;
}

function sendOrdersFeedbackForm(){

    var post = {};
    post["order"] = $('#order-feedback-form-ordernum').html();
    post["type"] = $('#order-feedback-form-type').val();
    post["name"] = $('#order-feedback-form-fio').html();
    post["text"] = $('#order-feedback-form-text').val();
    if(!post["text"]){
        riseError('Введите сообщение');
        return false;
    }
    if(!post["type"]){
        riseError('Выберите тип обращения');
        return false;
    }

    $('#orders-feedback-form .ag-shop-modal__container div').hide();
    $('#orders-feedback-form .ag-shop-modal__container').prepend('<div class="form-success">Сообщение отправляется</div>');
    
    $.post(
        "/profile/order_feedback.ajax.php",
        post,
        function(data){
            setTimeout(function(){
                $('#orders-feedback-form').fadeOut(function(){
                    $('#orders-feedback-form .ag-shop-modal__container div').show();
                    $('#orders-feedback-form .ag-shop-modal__container .form-success').remove();        
                });
            },2000);
        }
    );
    return false;
}

function printOrder(orderId){
    $('body').append('<iframe style="display:none;" src="/profile/order/print.png.ajax.php?act=download&id='+orderId+'"></iframe>');
    return false;
}

function orderCancel(orderId, obj){
    $(obj).unbind("click");
    $(obj).attr("onclick","return false;");
    $(obj).html('Производится отмена...');
    $.get(
        "/profile/order/order.ajax.php?cancel="+orderId,
        function(data){
            var answer = JSON.parse(data);
            if(answer.error){
                riseError(answer.error);
                return false;
            }
            document.location.href='/profile/order/';
        }
    );
    return false;
}

function riseError(message){
    $("#rise-error #rise-error-message").html(message);
    $("#rise-error").fadeIn();
}

