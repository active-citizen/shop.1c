$(function() {

  // ===================
  // Clear autocomplete
  // ===================

  $("#multiple-datasets .tt-input").focus(function() {
      $(".mobile-header-search__clear").addClass("show-clear");
      $(".mobile-search-status").addClass("show-results");
  });

  $('#multiple-datasets .tt-input').blur(function(){
    if( !$(this).val() ) {
      $(".mobile-header-search__clear").removeClass("show-clear");
      $(".mobile-search-status").removeClass("show-results");

      clearSearchInput();
    }
  });

  // Очистка поисковой строки, и удаление всех классов хелперов
  function clearSearchInput() {
    $('.typeahead').typeahead('val', '');
    $(".mobile-header-search__clear").removeClass("show-clear");
    if ($('body').hasClass("noscroll white-background") != true) {
      $('body').removeClass("noscroll");
    }

    if ($('.mobile-search-notfind').hasClass("disabled") != true) {
      $('.mobile-search-notfind').addClass("disabled");
    }
  };

  $('.mobile-header-search__clear').on('click',function () {
    clearSearchInput();
  });

  // Дублирование
  $('.mobile-search-notfind__reset').on('click',function () {
    clearSearchInput();
  });

  // Adding submit event for search form
  // after selecting search option

  $('#multiple-datasets .tt-input').bind('typeahead:select', function(ev, suggestion) {
    $("#mobileHeaderSearchForm")[0].submit();
  });

  $('#multiple-datasets .tt-input').bind('typeahead:autocomplete', function(ev, suggestion) {
    $("#mobileHeaderSearchForm")[0].submit();
  });

  $('#multiple-datasets .tt-input').bind('typeahead:render', function(ev, suggestion) {

    // Запрещаем скролл, если открыты подсказки
    if ($('body').hasClass("noscroll") != true) {
      $('body').addClass("noscroll");
    }
    // По умолчанию скрываем "без результатов"
    $(".mobile-search-notfind").addClass("disabled");

    // Когда поисковая строка активна, и результатов нет
    if ( ($(".mobile-search-status").hasClass("tt-open")) && ($(".mobile-search-status").hasClass("tt-empty"))) {
      // Показываем блок "без результатов"
      $(".mobile-search-notfind").removeClass("disabled");
      // Берем строку из поискового ипута
      var searchedQuery = $('#multiple-datasets .tt-input').typeahead('val');
      // Записываем поисковый запрос в блок
      var notFindContainer = $("#mobileSearchQuery").text(searchedQuery);
    }
  });

  // Выдвижение поиска

$('.searchform').hover(function(){
        $(this).addClass('opensearch');
    }, function(){
        if ( $(this).find('input').val() == '' && ! $(this).find('input').is(":focus") ) {
            $(this).removeClass('opensearch');
        }
    });
    if ( $('.searchform').val() != '' ) {
        $('.searchform').addClass('opensearch');
    }
    $('.searchform').on('blur', 'input', function(){
        if ( $(this).val() == '' ) {
            $(this).removeClass('opensearch');
        } else {
            $(this).addClass('opensearch');
        }
    });
    

});

