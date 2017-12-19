$(function() {

  // ===================
  // Get Full page height
  // ===================

  var scrollHeight = Math.max(
    document.body.scrollHeight, document.documentElement.scrollHeight,
    document.body.offsetHeight, document.documentElement.offsetHeight,
    document.body.clientHeight, document.documentElement.clientHeight
  );

  function setWrappersHeight() {
    // ===========
    // Set min-height for body equal 100vh
    // ВАЖНО
    // ТУТ СТОЯТ ПРАВИЛЬНЫЕ ЗНАЧЕНИЯ
    // БЕЗ НЕОБХОДИМОСТИ НЕ ТРОГАТЬ!!!
    
    $("body").css("min-height", innerHeight);
    $(".mobile-header-category").css("top", (innerHeight)*(-1));
    $(".mobile-aside-filter").css("min-height", scrollHeight);
    $(".mobile-aside-form-inner").css("min-height", ((innerHeight) - (33)));
    $(".mobile-aside-dropdown-outer").css("min-height", innerHeight);
    $(".mobile-search-notfind").css("height", ((innerHeight) - (59)));
  }
  setWrappersHeight();

  // ===================
  // Listen for orientation changes
  // ===================

  if (window.onorientationchange) {
    window.onorientationchange = updateOrientation;
  }
  else {
      window.onresize = updateOrientation;
  }

  var body = $(document.body);

  function updateOrientation() {
      var islandscape = window.orientation != null ? window.orientation != 0 : window.innerWidth > window.innerHeight;
      body.toggleClass("landscape", islandscape);

      scrollHeight = Math.max(
        document.body.scrollHeight, document.documentElement.scrollHeight,
        document.body.offsetHeight, document.documentElement.offsetHeight,
        document.body.clientHeight, document.documentElement.clientHeight
      );

      setWrappersHeight();
  }
  updateOrientation();

  // ===================
  // Animation for header
  // ===================

  $(".mobile-header").headroom({
    "offset": 20,
    "tolerance": 5
  });

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
  // Show Category
  // ===================

  $('.mobile-header-caregory-btn').on('click',function () {
    $('body').toggleClass("noscroll white-background");
    $(this).toggleClass("active");
    $('.mobile-header-category').toggleClass("show-category");
  });

  // ===================
  // Show Filters
  // ===================

  $('.mobile-header-filter-btn').on('click',function () {
    $('body').addClass("noscroll");
    $('body').removeClass("white-background");
    $('.mobile-header-category').removeClass("show-category");
    $('.mobile-header-caregory-btn').removeClass("active");
    $('.mobile-aside-filter').addClass("show-filters");
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
    defaultCheckboxState();
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

  // ===================
  // Autocomplete
  // ===================
  var productCategories = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('category'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: '/catalog/index.mobile.sections.php?1'
  });

  var productItems = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('item'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: '/catalog/index.mobile.items.php?1'
  });

  $('#multiple-datasets .typeahead').typeahead({
    highlight: true,
    menu: $('.mobile-search-status'),
  },
  {
    name: 'product-items',
    display: 'item',
    source: productItems,
    templates: {
      // Если нужно будет выводить "отсутствие" результатов для автодополнения,
      // то нужно раскоментировать этот блок
      // empty: function (data) {
      //   return '<h3 class="mobile-header-search__title">По наименованию</h3><p class="tt-dataset">По вашему запросу<b class="tt-highlight"> ' + data.query + ' </b>ничего не найдено</p>';
      // },
      header: '<h3 class="mobile-header-search__title">По наименованию</h3>'
    }
  },
  {
    name: 'category',
    display: 'category',
    source: productCategories,
    templates: {
      // Если нужно будет выводить "отсутствие" результатов для автодополнения,
      // то нужно раскоментировать этот блок
      // empty: function (data) {
      //   return '<h3 class="mobile-header-search__title">По фильтрам</h3><p class="tt-dataset">По вашему запросу<b class="tt-highlight"> ' + data.query + ' </b>ничего не найдено</p>';
      // },
      header: '<h3 class="mobile-header-search__title">По фильтрам</h3>'
    }
  }

  );

  // ===================
  // Clear autocomplete
  // ===================

  $("#multiple-datasets .tt-input").focus(function() {
      $(".mobile-header-search__clear").addClass("show-clear");
  });

  $('#multiple-datasets .tt-input').blur(function(){
    if( !$(this).val() ) {
      $(".mobile-header-search__clear").removeClass("show-clear");
    }
  });

  $('.mobile-header-search__clear').on('click',function () {
    $('.typeahead').typeahead('val', '');
    $(".mobile-header-search__clear").removeClass("show-clear");
  });


  // ===================
  // Cross Browser Width Calc
  // ===================

  function getdocWidth(){
	  /* Получаем строку из юзерагента браузера */
	  var ua = navigator.userAgent.toLowerCase();

	  /* Проверяем, если в строке есть "safari",
	     то скорее всего это webkit, поэтому заходим в этот if
	  */
	  if (ua.indexOf('safari') != -1) {

	    /* Если это браузер на основе Chrome, то записываем в
	       переменную docWidth значение window.innerWidth */
	    if (ua.indexOf('chrome') > -1) {
	      docWidth = window.innerWidth;

	    /* Если это не Chrome, то значит это Safari,
	       поэтому в переменной docWidth уже сохраняем значение document.documentElement.clientWidth*/
	    } else {
	      docWidth = document.documentElement.clientWidth;
	    }
	  /* Если в строке юзерагента нет "Safari", значит это какой-то иной браузер,
	     поэтому отдаём ему window.innerWidth
	     */
	  }else{
	   docWidth = window.innerWidth;
	  }
	  /* Ну и возвращаем переменную */
  return docWidth;
	};


});
