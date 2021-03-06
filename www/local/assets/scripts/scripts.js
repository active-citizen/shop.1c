var scrollProcess = 0;

$(document).ready(function(){
    
    // Удаляем в мобильных экранах лишнее
    if(
        (window.pageYOffset && window.pageYOffset<460)   
        ||
        (window.innerWidth && window.innerWidth<460)
    ){
        $('.desktop-filter').remove();

        $('.catalog-menu').css('width',
            $('.tags-menu').length
            ?
            parseInt($(window).width()*0.55)+'px'
            :
            '100%'
        );
        $('.tags-menu').css('width',parseInt($(window).width()*0.45)+'px');
        $('.tags-menu .js-menu__list').css(
            'width',parseInt($(window).width()*1.05)+'px'
        );
        $('.catalog-menu .js-menu__list').css(
            'width', parseInt($(window).width()*1.05)+'px'
        );


    }
    else{
        $('.mobile-filter').remove();
    }

    // Вывод фильтров
    $('.ag-shop-catalog__filter-instance a[rel="filters"]').click(function(){

        if($('.desktop-filter').css('display')=='none')
            $('.desktop-filter').slideDown();
        else
            $('.desktop-filter').slideUp();

        if($('.desktop-filter').css('display')=='none')$(this).removeClass('ag-shop-menu__link--active');
        return false;
    });


    $('#go-login').click(function(){
        
        var myObj = $(this);
        
        myObj.addClass('catalog-ajax-block-loader');
        
        
        var postData = {};
        postData["login"] = $('#ag-login').val();
        postData["password"] = $('#ag-password').val();
        
        $.post(
            "/.integration/auth.ajax.php",
            postData,
            function(data){
                var answer = JSON.parse(data);
                if(answer.errors.length){
                    $('#ag-login-error').html('');
                    $('#ag-login-error').append('<ol>');
                    for(i in answer.errors){
                        $('#ag-login-error').append('<li>'+answer.errors[i]+'</li>');
                    }
                    $('#ag-login-error').append('</ol>');
                    $('#ag-login-error').fadeIn();
                }
                else{
                    document.location.href="/";
                }
                myObj.removeClass('catalog-ajax-block-loader');
            }
        );
        return false;
    });
    
    $('.ag-tab-title').click(function(){
        $('.ag-tab-title').removeClass('active');
        $(this).addClass('active');
        $('.ag-tab-content').css('display','none');
        $('div.ag-tab-content[rel="'+$(this).attr("rel")+'"]').css('display','block');
        
    });
    
    
    $(".ag-product-mark-post").mousemove(function(){
        if($(this).hasClass('voted'))return false;
        var ofset = $(this).offset();
        var percent = parseInt(100*(event.clientX - ofset.left)/$(this).width());
        var mark = parseInt(5*(event.clientX - ofset.left)/$(this).width()+1);
        $(this).find('.yellow').css("width",20*parseInt(5*(percent/100)+1)+'%');
        $(this).attr("mark",mark)
    });
    $(".ag-product-mark-post").mouseout(function(){
        if($(this).hasClass('voted'))return false;
        $(this).find('.yellow').css("width",'0%');
    });
    $(".ag-product-mark-post").click(function(){
        if($(this).hasClass('voted'))return false;
        var mark = $(this).attr("mark");
        var product = $(this).attr("product");
        var markObj = $(this);
        
        markObj.text('Загрузка...');
        $.get(
            "/profile/order/order.ajax.php?mark="+mark+"&product="+product,
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
                    riseError(answer.error);
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

    $('.ag-shop-sidebar__up').click(function () {
        $('body,html').animate({
            // https://jira.altarix.ru/browse/AG-611
            scrollTop: 0
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
        var path = document.location.pathname.split('/');
        var catalog_param = '';
        if(path[1]=='catalog' && path[2].match(/^[\w\d\-]+$/i))catalog_param = path[2];
        $('.catalog-ajax-block').load("/catalog/index.ajax.php?"+hash+'&catalog_name='+catalog_param,function(){
            $('.catalog-ajax-block').removeClass('catalog-ajax-block-loader');
            // Показывать или прятать кнопку "исчо"
            if($('.catalog-page-input').last().val()){
                $('.ag-shop-catalog__more-button').html('Ещё');
                $('.ag-shop-catalog__more-button').fadeIn();
            }
            else{
                $('.ag-shop-catalog__more-button').fadeOut();
            }
            wishes_load_catalog();
        });
    }
    
    if($('.ag-shop-filter')){
        var hash = document.location.hash;
        hash = hash.replace(/#/,"");
        var parameters_array = hash.split("&");
        
        parameters = {};
        for(i in parameters_array){
            tmp = parameters_array[i].split("=");
            parameters[tmp[0]] = tmp[1];
        }

        if(parameters.filter_iwant){
            tmp = parameters.filter_iwant.split(",");
            for(i in tmp){
                $('input.ag-iwant[value="'+tmp[i]+'"]').click();
                input_variant_click($('input.ag-iwant[value="'+tmp[i]+'"]'));
            }
        }
        if(parameters.filter_interest){
            tmp = parameters.filter_interest.split(",");
            for(i in tmp){
                $('input.ag-interest[value="'+tmp[i]+'"]').click();
                input_variant_click($('input.ag-interest[value="'+tmp[i]+'"]'));
            }
        }

        if(parameters.filter_balls){
            $('input[name="ag-balls"]').each(function(){
                if($(this).val()==parameters.filter_balls){
                    $('input.ag-balls[value="'+$(this).val()+'"]').attr('checked',true);
                    input_variant_click($('input.ag-balls[value="'+$(this).val()+'"]'));
                }
            });
        }

        $('.ag-shop-menu__link_flag').each(function(){
            if($(this).attr("rel")==parameters.flag){
                $(this).addClass('ag-shop-menu__link--active');
                $('#ag-flag').val($(this).attr("rel"));
            }
        });
        $('.ag-shop-menu__link_sorting').each(function(){
            if($(this).attr("rel")==parameters.sorting){
                $(this).addClass('ag-shop-menu__link--active');
                $('#ag-sorting').val($(this).attr("rel"));
            }
        });

            /*событие для кнопки фильтр js-filters-toggle*/     
        $('.js-filters-toggle').each(function(){
            if($(this).attr("rel")==parameters.sorting){
                $(this).addClass('ag-shop-menu__link--active');
                $('#ag-sorting').val($(this).attr("rel"));
            }
        });
        
        if(!parameters.flag){
            $('a[rel="all"]').addClass('ag-shop-menu__link--active');
            $('#ag-flag').val('all');
        }
        
        if(!parameters.sorting){
            $('a[rel="rating-desc"]').addClass('ag-shop-menu__link--active');
            $('#ag-sorting').val('price-asc');
        }
    }
    
    
    $('.ag-product-mark').click(function(){var product_id = $(this).attr("productid");});

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

        var get_profile_url = "/profile/profile/order/order.ajax.php?offer_id="+offer_id+'&store_id='+store_id;


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
        var store_id = $('.catalog_item_confirm_message .ag-window #store_id').html();
        if(!offer_id){
            ag_ci_rise_error('Не определён ID торгового предложения');
            return false;
        }
        
        var add_basket_url = "/profile/order/order.ajax.php?add_to_basket=1&id="
            +offer_id+"&quantity="
            +$('#ag-basket-amount').spinner("value")+"&store_id="+store_id;

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
                    "/profile/order/order.ajax.php?add_order=Y&store_id="
                    +answer.store_id,
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
                                    for(i in answer.order.error){
                                        error_text += ''+answer.order.error[i]+'<br/>';
                                    }
                                    error_text +='';
                                    $('.catalog_item_confirm_message').fadeOut('fast');
                                    ag_ci_rise_error(error_text);
                                }
                            );
                        }
                    }
                );
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


    //$('.fimage').fancybox();
    if(typeof $('#ag-basket-amount').spinner === 'function'){
        $('#ag-basket-amount').spinner({culture: "ru",min: "1",step: "1"});
    
        $('#ag-basket-amount').spinner("enable");
        $('#ag-basket-amount').spinner("value",1);
    }

    // Пересчитываем число выбранных фильтров
    calcFilterCount();

    // Подгружем в фоне баллы пользователя
    //loadUserPoints();
});


/**
    Фоновая подгрузка баллов пользователя
*/
function loadUserPoints(){
    $.get(
        "/.integration/points.ajax.php",
        function(data){
            var answer = {};
            try{
                answer = JSON.parse(data);   
            }
            catch(err){
                return false;
            }
            if(typeof answer.title !== undefined){
                $('.ag-shop-nav__profile-points').html(answer.title);
            }
        }
    );
}

function next_page(){
    // Берём информацию о следующей странице для подгрузки из input
    query_string = $('.catalog-page-input').last().val();
    // Удаляем input, хранящий информацию о следующей странице для подгрузки
    $('.catalog-page-input').remove();
    // Конец промотки
    if(!query_string)return false;
    var button_text = $('.next-page').html();
    $('.next-page').html('<img src="/local/assets/images/loading.gif">');
    $.get(
        "/catalog/index.ajax.php?"+query_string,
        function(data){
            $('.catalog-ajax-block').append(data);
            scrollProcess = 0;
            /*
            Прокрутка
            $('body,html').animate({
                scrollTop: $('body').height()
            }, 1600);
            */
            // Возвращаем надпись на кнопку
            $('.next-page').html(button_text);
            //Удаляем кнопку прокрутки, если прокручивать нечего (отсутствует input)
            if(!$('.catalog-page-input').last().val())$('.next-page').remove();
            // Загружаем сердечки
            wishes_load_catalog();
        }
    );
    return false;
}

function wishes_load(){
    wishes_load_catalog();
    
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
    $.get("/profile/order/order.ajax.php?cancel="+order_id,function(){
        document.location.href='/order/';
//        $(obj).css('display','inline-block');
//        $('#ag-cancel-loader-'+order_id).css('display','none');
    })
    return false;
}

function mywish(object){
    var obj = $(object);
    var product_id = obj.attr("productid");
    var url = "/profile/order/order.ajax.php?wish=";
    if(obj.hasClass('wish-on')){
        obj.removeClass('wish-on')
        obj.addClass('wish-off')
        url += 'off';
    }else{
        obj.removeClass('wish-off')
        obj.addClass('wish-on')
        url += 'on';
    }
    
    url += '&productid='+product_id;

    $.get(
        url,
        function(data){
            var answer = JSON.parse(data);
            if(!answer.error){
                $('#wishid'+product_id).html(answer.wishes);
            }
            else{
                riseError(answer.error);
            }
        }
    );
}


function filter_clear(){
    $('.ag-shop-filter').find('input:checked').prop('checked',false);
    $('span.ag-shop-filter__trigger').each(function(){$(this).html($(this).attr('alltitle'));});
    ag_filter();
}

function calcFilterCount(){
    var count = $('.ag-shop-filter__variants input:checked').length;
    if(count){ 
        $('.filter-counts').html('('+count+')');
        $('.desktop-filters-count').html(count);

    }
    else{
        $('.filter-counts').html('');
        $('.desktop-filters-count').html('0');
    }
}

function ag_filter(){
    $('.catalog-ajax-block').last().addClass('catalog-ajax-block-loader');

    $('.ag-shop-catalog__item').addClass('catalog-item-loader');
    
    var iwant =  Array();
    $('.ag-iwant').each(function(){if($(this).is(":checked"))iwant.push($(this).val())});
    iwant = iwant.join(',');
    
    
    var interest = Array(); 
    $('.ag-interest').each(function(){if($(this).is(":checked"))interest.push($(this).val())});
    interest = interest.join(',');
    
    var balls = $('input[name="ag-balls"]:checked').last().val();
    var flag = $('#ag-flag').val()?$('#ag-flag').val():'all';
    var sorting = $('#ag-sorting').val()?$('#ag-sorting').val():'rating-desc';
    
    var path = document.location.pathname.split('/');
    var catalog_param = '';
    if(path[1]=='catalog' && path[2].match(/^[\w\d\-]+$/i))catalog_param = path[2];
    

    var uri = 
        "filter_iwant="+iwant+
        "&filter_interest="+interest+
        "&filter_balls="+balls+
        '&flag='+flag+
        '&sorting='+sorting+
        '&catalog_name='+catalog_param
        ;
        
    var url = "/catalog/index.ajax.php?"+uri;
    document.location.hash = uri;
    $('.ag-shop-catalog__items-container > div').load(url,function(){
        $('.catalog-ajax-block').last().removeClass('catalog-ajax-block-loader');
        calcFilterCount();
        wishes_load_catalog();

        /*
        // Перемотка на центр при выборе
        $('body,html').animate(
            {
                scrollTop: 630
            }, 
            800
        );
        */
        
        if($('.catalog-page-input').last().val()){
            $('.ag-shop-catalog__more-button').fadeIn();
        }
        else{
            $('.ag-shop-catalog__more-button').fadeOut();
        }
        
    });
    return false;
}

// Загрузка индивидуальных сердечек
function wishes_load_catalog(){
    var productIds = $('input[name="products"]').last().val();
    $.post(
        "/catalog/wishes.ajax.php",
        {
            "products":productIds
        },
        function(data){
            try{
                var answer = JSON.parse(data);
            }
            catch(err){
                //riseError('Ошибка получения желаний пользователя:' + data);
                return false;
            }
            var obj;
            var i;
            for(i in answer){
                obj = $('[productid="'+answer[i]+'"]');
                if(!obj.hasClass('wish-on')){
                    obj.removeClass('wish-off');
                    obj.addClass('wish-on');
                }
            }
        }

    );
}

function agauth(encsession){

    if(!encsession)return false;

    //$('body').append('<div class="screen-blocker"></div><div class="auth-loader"></div>');
    // Отсылаем шифрованный ID сессии ajax-скрипту для расшифровки и авторизации
    $.post(
	'/.integration/auth.ajax.php?backurl='+document.location.href,
	{"enc_session_id":encsession},
	function(data){
	    var answer = {};
	    try{
            answer = JSON.parse(data);
	    }
	    catch(e){
            answer.errors = new Array(e.message);
	    }
	   
	    // Ошибок нет - возвращаемся на страницу откуда авторизовались
	    if(!answer.errors.length && answer.redirect){
            document.location.href = answer.redirect;
            return true;
	    }

	    else if(!answer.errors.length && typeof answer.profile=='object'){
            if(!$('#profie-win .ag-shop-nav__profile-points').html())
            $("#profie-win").html('<a class="ag-shop-nav__link" href="/profile/">'
              +'<div class="ag-shop-nav__profile-container">'
                +'<i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--profile"></i>'
                +'<div class="ag-shop-nav__link-caption">'
                  +'<span class="hide-on-desktop">Профиль</span>'
                  +'<span class="show-on-desktop">'+
                  answer.profile.personal.firstname
                  +' '+
                  answer.profile.personal.middlename
                  +'<br>'+answer.profile.personal.surname+'</span>'
                +'</div>'
                +'<div class="ag-shop-nav__profile-points"></div>'
              +'</div>'
          +'</a>');
          loadUserPoints();
        }

	    // Формируем блок ошибок
	    for(i in answer.errors){
            console.log(answer.errors[i]);
	    }

        if(typeof answer.alerts!='undefined')
	    for(i in answer.alerts){
            alert(answer.alerts[i]);
	    }
	}
    );
    
    
}


