$(document).ready(function() {

  var animationDelay = 250;

  // list toggle
  (function () {
    var menuButton = $('.js-menu__button');
    var menuList = $('.js-menu__list');

    menuButton.on('click', function(e) {
        e.preventDefault();
        if ( menuList.is(':visible') ) {
            menuList.slideUp(animationDelay);
            menuButton.removeClass('ag-shop-menu__button--close');
        } else {
            menuList.slideDown(animationDelay);
            menuButton.addClass('ag-shop-menu__button--close');
        }
    });
  })();

  /**********/

  // init slider
  (function () {
    $('.js-content-slider').slick({
      mobileFirst: true,
      centerPadding: 0,
      arrows: false,
      dots: true,
      dotsClass: 'ag-shop-slider__dots',
      infinite: true,
      speed: 300,
      slidesToShow: 1,
      centerMode: false,
      variableWidth: true,
      autoplay: false,
      responsive: [{
          breakpoint: 992,
          settings: {
              arrows: true
          }
      }]
    });
  })();

  /**********/

  // init spoilers
  (function () {
    $('.js-spoiler__content').hide();
    $('.js-spoiler__link').on('click', function(e) {
      e.preventDefault();
      var target = $(e.currentTarget);
      var targetContent = target.next('.js-spoiler__content');
      var linkClass = 'ag-shop-rules__spoiler-link';

      console.log($('.' + linkClass));
      if ( targetContent.is(':visible') ) {
        $('.' + linkClass).removeClass(linkClass + '--active');
        targetContent.slideUp(animationDelay);
      } else {
        $('.' + linkClass).removeClass(linkClass + '--active');
        target.children('.' + linkClass).addClass(linkClass + '--active');
        target.addClass(linkClass + '--active');
        $('.js-spoiler__content').slideUp(animationDelay);
        targetContent.slideDown(animationDelay);
      }
    })
  })();


  /**********/

  // share popup
  (function () {
    var shareTrigger = $('.js-share-trigger');
    var sharePopup = $(".js-share-popup");

    shareTrigger.on('click', function(e) {
      shareOpenHandler(e)
    });

    function shareOpenHandler(e) {
      e.preventDefault();
      if (!sharePopup.is(':visible')) {
        sharePopup.fadeIn(animationDelay);
        $(document).bind('mouseup', function(e) {
          shareCloseHandler(e);
        });
      }
    }

    function shareCloseHandler(e) {
      var container = sharePopup;
      if (!container.is(e.target) && container.has(e.target).length === 0) {
          container.fadeOut(animationDelay);
          $(document).unbind('mouseup', shareCloseHandler);
      }
    }
  })();

    if(document.location.hash){
        var id = document.location.hash.split("#")[1];
        if(id)$('#store-click-'+id).trigger('click')
    }


});
