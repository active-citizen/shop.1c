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
    $(".mobile-search-status").css("max-height", ((innerHeight) - (59)));
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
});
