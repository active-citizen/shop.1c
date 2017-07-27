var page = require('webpage').create();
phantom.addCookie({
    "name":"PHPSESSID",
    "value":"{PHPSESSID}",
    "path":"/",
    "domain":"localhost"
});

page.paperSize = {
    format: 'A4',
    orientation: 'portrait',
    margin: '1cm'
};
page.settings.resourceTimeout = 5000;

try{
    page.open('http://localhost/profile/order/print.ajax.php?id={ORDER_ID}', function(status) {
      if(status === "success") {
        page.render('{CERT_PATH}');
      }
      phantom.exit();

    });
}
catch(e) {
    console.log('Ошибка работы PhantomJS.');
    console.log('Сообщение ошибки:');
    console.log(e);
    phantom.exit();
}


