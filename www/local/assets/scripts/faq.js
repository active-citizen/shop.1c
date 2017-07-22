$(document).ready(function(){

    var hash =
        document.location.hash.split('#')[
            document.location.hash.split('#').length-1
        ];
    var section_id = hash.split(".")[0];
    var item_id = hash.split(".")[0];
    if(section_id){
        $('#faq-section-id-'+section_id).css('display','block');
        $('#faq-section-id-'+section_id).parent().
            find('a').first().addClass('faq-section-active');
    }

    $('.faq-section>a').click(function(){
         var activity = $(this).hasClass('faq-section-active'); 
         if(activity){
            $(this).removeClass('faq-section-active');
            $(this).parent().find('.faq-section-spoiler').first().
                hide();
         }
         else{
            $(this).addClass('faq-section-active');
            $(this).parent().find('.faq-section-spoiler').first().
                show();
         }

    });

});
