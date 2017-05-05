$(document).ready(function(){
    $('.partners-order-menu li a').click(function(){
        $(this).parent().parent().
            find("li").removeClass('active');
        $(this).parent().addClass('active');
        $('.partners-order-main').hide();
        $('#'+$(this).attr('rel')).show();
    });
});
