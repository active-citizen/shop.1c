var scrollProcess = 0;

$(document).ready(function(){
    
    
    $(".ag-product-mark-post").mousemove(function(){
        var ofset = $(this).offset();
        var percent = parseInt(100*(event.clientX - ofset.left)/$(this).width());
        var mark = parseInt(5*(event.clientX - ofset.left)/$(this).width()+1);
        $(this).find('.yellow').css("width",20*parseInt(5*(percent/100)+1)+'%');
        $(this).attr("mark",mark)
    });
    $(".ag-product-mark-post").mouseout(function(){
        $(this).find('.yellow').css("width",'0%');
    });
    $(".ag-product-mark-post").click(function(){
        var mark = $(this).attr("mark");
        var product = $(this).attr("product");
        var markObj = $(this);
        
        markObj.text('Загрузка...');
        $.get(
            "/order/order.ajax.php?mark="+mark+"&product="+product,
            function(data){
                var answer = JSON.parse(data);
                if(answer.percent){
                    markObj.find('yellow').remove();
                    markObj.removeClass("ag-product-mark-post");
                    markObj.addClass("ag-product-mark");
                    markObj.css("right",parseInt(4+24*(1-answer.percent))+'px');
                    markObj.css("background-position",parseInt(24*(1-answer.percent))+'px');
                    markObj.css("right",'70px');
                    markObj.text('');
                }
                else{
                    alert(answer.error);
                }
            }
        );
    });
    
    
    
    $("#back-top").hide();
    $(window).scroll(function () {
        if ($(this).scrollTop() > 630) {
            $('#back-top').fadeIn();
        } else {
            $('#back-top').fadeOut();
        }
    });

    $('#back-top a').click(function () {
        $('body,html').animate({
            scrollTop: 630
        }, 800);
        return false;
    });
        
    $('#c_store_amount li label').click(function(){
        var storage_id = 0;
        $('#c_store_amount li').removeClass('active');
        $('.ag-store-detail').hide();
        $(this).find("input").each(function(){if(this.checked)storage_id = $(this).val();})
        $('#agst-'+storage_id).show();
        $(this).parent().addClass('active');
    });
    
        
    if($('.catalog-ajax-block')){
        var hash = document.location.hash;
        hash = hash.replace(/#/,"");
        $('.catalog-ajax-block').load("/catalog/index.ajax.php?"+hash,function(){$('.catalog-ajax-block').removeClass('catalog-ajax-block-loader');});
    }
    
    if($('.ad-main-filter')){
        var hash = document.location.hash;
        hash = hash.replace(/#/,"");
        var parameters_array = hash.split("&");
        
        parameters = {};
        for(i in parameters_array){
            tmp = parameters_array[i].split("=");
            parameters[tmp[0]] = tmp[1];
        }
        
        if(parameters.filter_iwant)$('#ag-iwant').val(parameters.filter_iwant);
        if(parameters.filter_interest)$('#ag-interest').val(parameters.filter_interest);
        if(parameters.filter_balls)$('#ag-balls').val(parameters.filter_balls);
        if(parameters.flag)$('#ag-flag').val(parameters.flag);
        if(parameters.sorting)$('#ag-sorting').val(parameters.sorting);
        if(parameters.filter_type)$('#ag-types').val(parameters.filter_type);
        
        $('.filter-flag').each(function(){if($(this).attr("rel")==parameters.flag)$(this).addClass('radio-active')});
        $('.sorting-flag').each(function(){if($(this).attr("rel")==parameters.sorting)$(this).addClass('radio-active')});
        
        if(!parameters.flag){
            $('.filter-flag[rel="all"]').addClass('radio-active');
            $('#ag-flag').val('all');
        }
        
        
        if(!parameters.sorting){
            $('.sorting-flag[rel="price-asc"]').addClass('radio-active');
            $('#ag-sorting').val('price-asc');
        }
        
        var types = $('#ag-types').length>0?$('#ag-types').val().split(','):Array();
        for(i in types)
            $('.ag-filter-params label[rel="'+types[i]+'"]').addClass('radio-active');
        
    }
    
    
    $('.ag-product-mark').click(function(){var product_id = $(this).attr("productid");});

    
    $('#ag-interest').selectmenu({
        width:180,
        change: function(){ag_filter();}
    });
    $('#ag-iwant').selectmenu({
        width:180,
        change: function(){ag_filter();}
    });
    
    $('.ag-filter-params label').click(function(){
        if(!$(this).hasClass("radio-active")){
            $(this).addClass("radio-active");
        }
        else{
            $(this).removeClass("radio-active");
        }
        var types = Array();
        $('.ag-filter-params label').each(function(){
            if($(this).hasClass("radio-active"))types.push($(this).attr("rel"));
        })
        $('#ag-types').val(types.join(','));
        ag_filter();
    });

    $('div.ag2-wrap div.ag-section-title input[name="ag-flags"]').change(function(){
        $('div.ag2-wrap div.ag-section-title label').removeClass('radio-active');
        $(this).parent().addClass("radio-active");
    }); 

    
    $('.bx_cart_ag').click(function(){
        var offer_id = 0;
        
        
        // Определяем ID выбранного товарного предложения
        $('.bx_slider_conteiner').each(function(){
            if($(this).css('display')!='none'){
                offer_id = $(this).find('.bx_slide_left').attr("data-value");
                return false;
            }
        });
        
        
        if(!offer_id){ag_ci_rise_error("Неизвестный ID товарного каталога");return false;}
        
        // Определяем ID выбранного склада
        var store_id = 0;
        var default_store_id = 0;
        var stores_count = 0;
        $('#c_store_amount li input').each(function(){
            if($(this).css('display')!='none'){
                default_store_id = $(this).val();
                stores_count++;
                if(this.checked)store_id = $(this).val();
            }
        });
        // Если центр выдачи один - его и назначаем
        if(stores_count==1 && default_store_id)store_id = default_store_id;
        if(!store_id){ag_ci_rise_error("Центр выдачи не выбран, либо товара нет в наличии");return false;}

        var get_profile_url = "/order/order.ajax.php?offer_id="+offer_id+'&store_id='+store_id;


        // Получаем информацию по профилю пользователя
        var profile = {};
        var product = {};
        var store = {};
        var price = {};
        var account = {};
        $('#order-process').css('display','block');
        $('.bx_cart_ag').css('display','none');
        $.get(get_profile_url,function(data){
            var answer = JSON.parse(data);
            console.log(answer);
            if(!answer.profile){
                ag_ci_rise_error('Ошибка запроса профиля, склада, товара:'+answer.error);
                $('#bx_cart_ag').css('display','block');
                return false;
            }
            profile = answer.profile; 
            product = answer.product; 
            store = answer.store;
            price = answer.price;
            account = answer.account;
            if(!profile.ID){
                ag_ci_rise_error('Ошибка получения профиля');
                $('#order-process').css('display','none');
                $('.bx_cart_ag').css('display','block');
                return false;
            }
            if(price.DISCOUNT_PRICE > account.CURRENT_BUDGET){
                ag_ci_rise_error('На счету недостаточно средств для заказа');
                $('#order-process').css('display','none');
                $('.bx_cart_ag').css('display','block');
                return false;
            }
            
            // Выводим окно подтверждения заказа
            $('.bx_cart_ag').css('display','block');
            $('#order-process').css('display','none');
            ag_ci_rise_confirm(profile,store,product);
        });
        
        return false;
    });
    
    // Заказ из окна подтверждения
    $('.catalog_item_confirm_message .ok-button').click(function(){

        var offer_id = $('.catalog_item_confirm_message .ag-window #offer_id').html();
        if(!offer_id){
            ag_ci_rise_error('Не определён ID торгового предложения');
            return false;
        }
        
        var add_basket_url = "/order/order.ajax.php?add_to_basket=1&id="+offer_id+"&quantity=1";

        // добавляем в корзину
        $('#order-process-done').css('display','block');
        $('.ok-button').css('display','none');
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
                    "/order/order.ajax.php?add_order=Y",
                    function(data){
                        var answer = JSON.parse(data);
                        if(answer.redirect_url){
                            document.location.href=answer.redirect_url;
                        }
                        else{
                            // Чистим корзину, если заказ неудачен
                            $.get(
                                "/order/order.ajax.php?clear_basket",
                                function(){
                                    $('#order-process-done').css('display','none');
                                    $('.ok-button').css('display','block');
                                    var error_text = '';
                                    for(i in answer.order.ERROR){
                                        error_text += i+": "+answer.order.ERROR[i];
                                    }
                                    $('.catalog_item_confirm_message').fadeOut('fast');
                                    ag_ci_rise_error(error_text);
                                }
                            );
                        }
                    }
                );
                /*

                var postdata = {
                    "sessid":           $('.catalog_item_confirm_message .ag-window #sess_id').html(),
                    "action":           "saveOrderAjax",
                    "location_type":    "code",
                    "BUYER_STORE":      $('.catalog_item_confirm_message .ag-window #store_id').html(),
                    "DELIVERY_ID":      3,
                    "save":             "Y",
                    "ORDER_PROP_1":     $('.catalog_item_confirm_message .ag-window #ag-name').html(),
                    "ORDER_PROP_2":     $('.catalog_item_confirm_message .ag-window #ag-email').html(),
                    "ORDER_PROP_3":     $('.catalog_item_confirm_message .ag-window #ag-phone').html(),
                    "ORDER_PROP_7":     $('.catalog_item_confirm_message .ag-window #ag-address').html(),
                }



                $.post(
                    "/order/make/",
                    postdata,
                    function(data){
                        var answer = JSON.parse(data);
                        if(answer.order.REDIRECT_URL){
                            document.location.href=answer.order.REDIRECT_URL;
                        }
                        else{
                            // Чистим корзину, если заказ неудачен
                            $.get(
                                "/order/order.ajax.php?clear_basket",
                                function(){
                                    $('#order-process-done').css('display','none');
                                    $('.ok-button').css('display','block');
                                    var error_text = '';
                                    for(i in answer.order.ERROR){
                                        error_text += i+": "+answer.order.ERROR[i];
                                    }
                                    $('.catalog_item_confirm_message').fadeOut('fast');
                                    ag_ci_rise_error(error_text);
                                }
                            );
                        }
                    }
                );
                */
                
            }
        );
        return false;
    });
    
    $('.catalog_item_error_message .ag-window .close-button').click(function(){
        $('.catalog_item_error_message').fadeOut('fast');
    });

    $('.catalog_item_confirm_message .ag-window .close-button').click(function(){
        $('.catalog_item_confirm_message').fadeOut('fast');
    });
    
    
    //$('.ag-filter-params #ag-show').click(function(){ag_filter();});
    $('#ag-balls').change(function(){ag_filter();});
    
    $('.filter-flag').click(function(){
        $('.filter-flag').removeClass('radio-active');
        $(this).addClass('radio-active');
        $('#ag-flag').val($(this).attr('rel'));
        ag_filter();
        return false;
    });

    $('.sorting-flag').click(function(){
        $('.sorting-flag').removeClass('radio-active');
        $(this).addClass('radio-active');
        $('#ag-sorting').val($(this).attr('rel'));
        ag_filter();
        return false;
    });


    $('.fimage').fancybox();
    
});

function next_page(query_string){
    $('.catalog-ajax-block .next-page').last().html('');
    $('.catalog-ajax-block .next-page').last().addClass('catalog-ajax-block-loader');
    // Конец промотки
    if(!query_string)return false;
    $.get(
        "/catalog/index.ajax.php?"+query_string,
        function(data){
            $('.catalog-ajax-block .next-page').remove();
            $('.catalog-ajax-block').append(data);
            scrollProcess = 0;
            $('body,html').animate({
                scrollTop: $('body').height()
            }, 1600);
        }
    );
    return false;
}

function ag_ci_rise_error(text){
    $('.catalog_item_error_message .ag-window .message').html(text);
    $('.catalog_item_error_message').fadeIn('fast');
}

function ag_ci_rise_confirm(profile,store,product){
    $('.catalog_item_confirm_message .ag-window #offer').html(product.ELEMENT_NAME);
    $('.catalog_item_confirm_message .ag-window #store').html(store.TITLE+'<br/>'+store.ADDRESS);
    $('.catalog_item_confirm_message .ag-window #offer_id').html(product.ID);
    $('.catalog_item_confirm_message .ag-window #store_id').html(store.ID);
    $('.catalog_item_confirm_message .ag-window #sess_id').html(profile.SESSID);
    
    $('.catalog_item_confirm_message .ag-window #ag-name').html(profile.NAME);
    $('.catalog_item_confirm_message .ag-window #ag-email').html(profile.EMAIL);
    $('.catalog_item_confirm_message .ag-window #ag-phone').html(profile.PERSONAL_PHONE?profile.PERSONAL_PHONE:"-");
    
    $('.catalog_item_confirm_message .ag-window .message').html();
    $('.catalog_item_confirm_message').fadeIn('fast');
}

function order_cancel(order_id,obj){
    $(obj).css('display','none');
    $('#ag-cancel-loader-'+order_id).css('display','inline-block');
    $.get("/order/order.ajax.php?cancel="+order_id,function(){
        document.location.href='/order/';
//        $(obj).css('display','inline-block');
//        $('#ag-cancel-loader-'+order_id).css('display','none');
    })
    return false;
}

function mywish(object){
    var obj = $(object);
    var product_id = obj.attr("productid");
    var url = "/order/order.ajax.php?wish=";
    if(obj.hasClass('wish-on'))
        url += 'off';
    else
        url += 'on';
    
    url += '&productid='+product_id;
    
    obj.attr("class",'wish-loader');
    $.get(
        url,
        function(data){
            var answer = JSON.parse(data);
            if(!answer.error){
                obj.removeClass('wish-loader');
                obj.html(answer.wishes);
                obj.addClass(answer.addclass);
                obj.addClass('ag-product-wish');
            }
            else{
                obj.removeClass('wish-loader');
                alert(answer.error);
            }
        }
    );
}


function ag_filter(){
    $('.catalog-ajax-block').last().addClass('catalog-ajax-block-loader');
    var type = $('#ag-types').val()?$('#ag-types').val():0;
    var iwant = $('#ag-iwant').val()?$('#ag-iwant').val():0;
    var interest = $('#ag-interest').val()?$('#ag-interest').val():0;
    var balls = $('#ag-balls').val()?$('#ag-balls').val():0;
    var flag = $('#ag-flag').val()?$('#ag-flag').val():'all';
    var sorting = $('#ag-sorting').val()?$('#ag-sorting').val():'all';

    var uri = "filter_type="+type+"&filter_iwant="+iwant+"&filter_interest="+interest+"&filter_balls="+balls+'&flag='+flag+'&sorting='+sorting;
    var url = "/catalog/index.ajax.php?"+uri;
    document.location.hash = uri;
    $('.catalog-ajax-block').load(url,function(){
        $('.catalog-ajax-block').last().removeClass('catalog-ajax-block-loader');
        $('body,html').animate({
            scrollTop: 630
        }, 800);

    });
}
