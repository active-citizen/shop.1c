$(document).ready(function(){
//Для выпадающего меню
	$('.all_cat_wrap').click(function(e){
		e.preventDefault();
		$( ".nav_main_menu" ).slideToggle( 300, function() {

	  });
	});

//Для аккордиона на странице Адрес
	$('.adress_street').on('click', function(){

		$('.adress_cards_wrap').find('.adress_cards').slideUp();

		var itemAc = $(this).parent().find('.adress_cards');

		if(itemAc.is(':visible')) {
			itemAc.slideUp();	
		} else {
			itemAc.slideDown();
		}
		
	});

//Скрипт для табов
	$(".faq_btn_section").not(":first").hide();
	$(".faq_wrap_btn .faq_btn").click(function(e) {
		e.preventDefault();
		$(".faq_wrap_btn .faq_btn").removeClass("active").eq($(this).index()).addClass("active");
		$(".faq_btn_section").hide().eq($(this).index()).fadeIn()
	}).eq(0).addClass("active");

//Кнопка для скрытия блока Внимание!!!
	$(".att_btn").on("click", function(e){
		e.preventDefault();
		$(this).parent().hide();
	})	
//Кнопка для попап окна
	$(".card_inside_descr_wrap .btn_order_set_wrap").on("click",function(){
		$(".popup_order_wrap,.bg_popup").fadeIn();

		var scrollHeight = Math.max(
							  document.body.scrollHeight, document.documentElement.scrollHeight,
							  document.body.offsetHeight, document.documentElement.offsetHeight,
							  document.body.clientHeight, document.documentElement.clientHeight
							);
		$(".bg_popup").height(scrollHeight + 'px');		
	});

		$(".popup_order_wrap .popup_btn_close,.popup_order_wrap .btn_order_def.cancel").on("click",function(e){
			e.preventDefault();
			 $(".popup_order_wrap,.bg_popup").hide();
		});

//Для Select
	$( function() {
	    $( "#place,#color" ).selectmenu({
		  classes: {
		    "ui-selectmenu-menu": ""
		  }
		});
  	});
//Для Spinner
	$( function() {
	    var spinner = $( "#spinner" ).spinner();
	 
	    $( "#disable" ).on( "click", function() {
	      if ( spinner.spinner( "option", "disabled" ) ) {
	        spinner.spinner( "enable" );
	      } else {
	        spinner.spinner( "disable" );
	      }
	    });
	    $( "#destroy" ).on( "click", function() {
	      if ( spinner.spinner( "instance" ) ) {
	        spinner.spinner( "destroy" );
	      } else {
	        spinner.spinner();
	      }
	    });
	    $( "#getvalue" ).on( "click", function() {
	      alert( spinner.spinner( "value" ) );
	    });
	    $( "#setvalue" ).on( "click", function() {
	      spinner.spinner( "value", 5 );
	    });
	 
	    $( "button" ).button();
	  } );

});