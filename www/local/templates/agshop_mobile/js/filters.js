$(function() {

  // ===================
  // Hide filters on outside click
  // ===================

  $('.mobile-aside-filter-outer').on('click',function () {

    if ($(".mobile-aside-dropdown-outer").hasClass("show-dropdown")) {
      $('.mobile-aside-dropdown-outer').removeClass("show-dropdown");
    }
    else {
      $('body').removeClass("noscroll");
      $('.mobile-aside-filter').removeClass("show-filters");
    }

  });

  // ===================
  // Show Filters
  // ===================

  $('.mobile-header-filter-btn').on('click',function () {
    $('body').addClass("noscroll");
    $('body').removeClass("white-background");
    $('.mobile-header-category').removeClass("show-category");
    $('.mobile-header-caregory-btn').removeClass("active");
    // Clear search input
    $('.typeahead').typeahead('val', '');
    $(".mobile-header-search__clear").removeClass("show-clear");
    // Close notfind
    if ($('.mobile-search-notfind').hasClass("disabled") != true) {
      $('.mobile-search-notfind').addClass("disabled");
    }
    // -------
    $('.mobile-aside-filter').addClass("show-filters");
  });


  // ===================
  // Show Category
  // ===================

  $('.mobile-header-caregory-btn').on('click',function () {
    $('body').toggleClass("noscroll white-background");
    // Clear search input
    $('.typeahead').typeahead('val', '');
    $(".mobile-header-search__clear").removeClass("show-clear");
    // Close notfind
    if ($('.mobile-search-notfind').hasClass("disabled") != true) {
      $('.mobile-search-notfind').addClass("disabled");
    }
    // -------
    $(this).toggleClass("active");
    $('.mobile-header-category').toggleClass("show-category");
  });



  // ===================
  // Show Dropdown
  // ===================

  $('.mobile-aside-dropdown').on('click',function () {

    if ($(this).parent().hasClass("disabled")) {

    }
    else {
      $(this).next().addClass("show-dropdown");
    }

  });

  // ===================
  // Close dropdown
  // ===================

  $('.dropdown-close').on('click',function () {
    $('.mobile-aside-dropdown-outer').removeClass("show-dropdown");
  });

  // ===================
  // Save checkboxes
  // works for checkboxes and radio
  // ===================

  $('.dropdown-save').on('click',function () {

    var dropdownName = $(this).attr("data-dropdown");
    var checkboxWrapper = $('.mobile-aside-dropdown-outer').find("[data-dropdown='" + dropdownName + "']");
    var checkboxWrapperChildrens = checkboxWrapper.children();
    var arrayCheckboxTitles = [];
    var checkboxTitlesDestination = $(".mobile-aside-form-item__option").find("[data-dropdown='" + dropdownName + "']");

    // Get all checked checkboxes/radio
    $(checkboxWrapperChildrens).each(function(index, item){

        var currentCheckbox = $(this).find("input");
        var currentCheckboxTitle = $(this).find(".custom-checkbox-default__info-title");
        if (currentCheckbox.prop("checked")) {
          arrayCheckboxTitles.push(currentCheckboxTitle.text());
        }
    });

    // clear destinations from previous items
    checkboxTitlesDestination.empty();
    // Set checkboxes/radio titles to destination
    for (var i=0; i<arrayCheckboxTitles.length; i++) {
      checkboxTitlesDestination.append("<span class='mobile-aside-dropdown__item'>"+arrayCheckboxTitles[i]+"</span>")
    }

    $('.mobile-aside-dropdown-outer').removeClass("show-dropdown");

  });

  // ===================
  // Default Checkbox Status
  // ===================

  function defaultCheckboxState() {
    // Get all dropdowns
    $('.mobile-aside-dropdown-content').each(function(index, item){
      // Get dropdown-name
      var defaultDropdownName = $(this).attr("data-dropdown");
      var defaultCheckboxContainers = $(this).children();
      var defaultCheckboxDestination = $(".mobile-aside-form-item__option").find("[data-dropdown='" + defaultDropdownName + "']");
      var defaultCheckboxTitleArray = [];

        // Get all checked items in current dropdown
        $(defaultCheckboxContainers).each(function (index, name) {
          var currentCheckbox = $(this).find("input");
          var currentCheckboxTitle = $(this).find(".custom-checkbox-default__info-title");
          if (currentCheckbox.prop("checked")) {
            defaultCheckboxTitleArray.push(currentCheckboxTitle.text());
          }
        });

        // Set all checked items in dropdown-outter
        defaultCheckboxDestination.empty();
        // Set checkboxes/radio titles to destination
        for (var i=0; i<defaultCheckboxTitleArray.length; i++) {
            defaultCheckboxDestination.append("<span class='mobile-aside-dropdown__item'>"+defaultCheckboxTitleArray[i]+"</span>")
        }
    });
  }

  defaultCheckboxState();

  // ===================
  // Reset Form Button
  // ===================

  $('#mobileFiltersReset').on('click',function (e) {
    // Prevent Default Reset
    e.preventDefault();
    // Reset form using JS
    $("#mobileAsideFilterForm")[0].reset();
    // Set checkboxes in default state
        // Обнуляем фильтр интересов
        $('span[data-dropdown="filter-interests"]')
            .find('.mobile-aside-dropdown__item').remove();
        $('span[data-dropdown="filter-interests"]')
            .html('<span class="mobile-aside-dropdown__item">Все</span>');
        $('[data-dropdown="filter-interests"] input').prop('checked',false);
        $('[data-dropdown="filter-interests"] .dropdown-checkbox-all').click();
        $('[data-dropdown="filter-interests"] input#productInterestVse').prop('checked',true);

        // Обнуляем фильтр складов
        $('span[data-dropdown="filter-delivery"]')
            .find('.mobile-aside-dropdown__item').remove();
        $('span[data-dropdown="filter-delivery"]')
            .html('<span class="mobile-aside-dropdown__item">Все</span>');
        $('[data-dropdown="filter-delivery"] input').prop('checked',false);
        $('[data-dropdown="filter-delivery"] inout#productDeliveryAll').prop('checked',true);
        $('[data-dropdown="filter-delivery"] .dropdown-checkbox-all').click();

        // Обнуляем фильтр по цене
        $('.mobile-aside-price input').val('');

        // Обнуляем флаги хит, новинка, акция
        $('.mobile-aside-form-block--last input').prop('checked',false);

        // Сброс сортировок
        
        $('.mobile-aside-dropdown-content input[type="radio"]').prop('checked',false);
        $('.mobile-aside-dropdown-content input[id="productSortSortPriceFresh"]').prop('checked',true);
        $('span[data-dropdown="filter-sort"]')
            .html('<span class="mobile-aside-dropdown__item">Дата обновления</span>');


        // Сброс вида плиток
        /*
        $('input[id="productGridCheckbox"]').prop('checked',false);
        */
  });

  // ===================
  // Remove or Set OptionAllCheckbox
  // ===================

  $('.default-dropdown-label').on('click',function (e) {
    var clickedCheckbox = $(this).prev();
    var dropdownContainer = $(this).parents(".mobile-aside-dropdown-content");
    var dropdownContainerChildrens = dropdownContainer.children();
    var selectAllCheckbox = $(dropdownContainer).find(".dropdown-checkbox-all");

    if (clickedCheckbox.hasClass("dropdown-checkbox-all")) {
      e.preventDefault();
      if (clickedCheckbox.prop("checked")) {
        clickedCheckbox.prop("checked",false);
      }
      else {
        $(dropdownContainerChildrens).each(function (index, name) {
          var childrenCheckbox = $(this).find("input");
          childrenCheckbox.prop("checked",false);
        });
        clickedCheckbox.prop("checked",true);
      }
    }
    else {
      selectAllCheckbox.prop("checked",false);
    }

  });

});
