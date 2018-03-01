$(document).ready(function(){
//Для выпадающего меню
	$('.all_cat_wrap').click(function(){
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

});