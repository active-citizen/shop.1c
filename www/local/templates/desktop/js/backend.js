$(document).ready(function(){

  // ===================
  // Autocomplete
  // ===================
  var productCategories = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('category'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: '../json/category.json'
    // prefetch: '/catalog/index.mobile.sections.php?'
  });

  var productItems = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('item'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: '../json/product-items.json'
    // prefetch: '/catalog/index.mobile.items.php?'
  });

  $('#multiple-datasets .typeahead').typeahead({
    highlight: true,
    menu: $('.mobile-search-status'),
  },
  {
    name: 'product-items',
    display: 'item',
    limit: 5, //если нужно показывать больше результатов - изменить эту цифру
    source: productItems,
    templates: {
      // Если нужно будет выводить "отсутствие" результатов для автодополнения,
      // то нужно раскоментировать этот блок
      // -- это встроенная функция, использовать или ее, или кастомную
      // empty: function (data) {
      //   return '<h3 class="mobile-header-search__title">По наименованию</h3><p class="tt-dataset">По вашему запросу<b class="tt-highlight"> ' + data.query + ' </b>ничего не найдено</p>';
      // },
      header: '<h3 class="mobile-header-search__title">По наименованию</h3>'
    }
  },
  {
    name: 'category',
    display: 'category',
    limit: 5, //если нужно показывать больше результатов - изменить эту цифру
    source: productCategories,
    templates: {
      // Если нужно будет выводить "отсутствие" результатов для автодополнения,
      // то нужно раскоментировать этот блок
      // -- это встроенная функция, использовать или ее, или кастомную
      // empty: function (data) {
      //   return '<h3 class="mobile-header-search__title">По фильтрам</h3><p class="tt-dataset">По вашему запросу<b class="tt-highlight"> ' + data.query + ' </b>ничего не найдено</p>';
      // },
      header: '<h3 class="mobile-header-search__title">По фильтрам</h3>'
    }
  }

  );


  // write here backend scripts
    // $('button[name="mobileFiltersSubmit"]').click(function(){
    //     document.location.hash='';
    // });

    // $('button[name="mobileFiltersReset"]').click(function(){
    //     // Обнуляем фильтр интересов
    //     $('span[data-dropdown="filter-interests"]')
    //         .find('.mobile-aside-dropdown__item').remove();
    //     $('span[data-dropdown="filter-interests"]')
    //         .html('<span class="mobile-aside-dropdown__item">Все</span>');
    //     $('[data-dropdown="filter-interests"] input').prop('checked',false);
    //     $('[data-dropdown="filter-interests"] .dropdown-checkbox-all').click();
    //     $('[data-dropdown="filter-interests"] input#productInterestVse').prop('checked',true);
    //
    //     // Обнуляем фильтр складов
    //     $('span[data-dropdown="filter-delivery"]')
    //         .find('.mobile-aside-dropdown__item').remove();
    //     $('span[data-dropdown="filter-delivery"]')
    //         .html('<span class="mobile-aside-dropdown__item">Все</span>');
    //     $('[data-dropdown="filter-delivery"] input').prop('checked',false);
    //     $('[data-dropdown="filter-delivery"] inout#productDeliveryAll').prop('checked',true);
    //     $('[data-dropdown="filter-delivery"] .dropdown-checkbox-all').click();
    //
    //     // Обнуляем фильтр по цене
    //     $('.mobile-aside-price input').val('');
    //
    //     // Обнуляем флаги хит, новинка, акция
    //     $('.mobile-aside-form-block--last input').prop('checked',false);
    //
    //     // Сброс сортировок
    //     $('.mobile-aside-dropdown-content input[type="radio"]').prop('checked',false);
    //     $('.mobile-aside-dropdown-content input[id="productSortPriceFavourite"]').prop('checked',true);
    //     $('span[data-dropdown="filter-sort"]')
    //         .html('<span class="mobile-aside-dropdown__item">Избранное</span>');
    //
    //     // Сброс вида плиток
    //     $('input[id="productGridCheckbox"]').prop('checked',false);
    //
    // });


});
