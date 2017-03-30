<?
/*
    Настройки
*/
    // Мочтовый адрес магазина
    define("SHOP_EMAIL","shop@ag.mos.ru");
    // Количество заказов, которые выгружаются в 1С за один приём
    define("ORDER_EXPORT_QUANT",1);
    // Каталог с почтовыми шаблонами
    define("MAIL_TMPL_PATH",realpath(dirname(__FILE__)."/../mail_templates/"));

    // List of domains, adresses and coutours
    // Each element means
    // array(
    //      'domain',
    //      'datacenter internal IP',
    //      'external IP',
    //      'crossdomain authorization URL',
    //      'bals conversion controller URL',
    //      'countour name',
    //  )
    $arContours = array(
        array(
            "domain"    =>  "shop.ag.mos.ru",
            "int_ip"    =>  "10.89.79.58",
            "ext_ip"    =>  "213.79.88.37",
            "auth_url"  =>  "http://ag.mos.ru/mvag/auth?callback=agauth",
            "bcc_url"   =>  "http://shop.ag.mos.ru/api/",
            "name"      =>  "prod"
        ),
        array(
            "domain"    =>  "pre-prod02.shop.ag.mos.ru",
            "int_ip"    =>  "10.89.79.57",
            "ext_ip"    =>  "213.79.88.37",
            "auth_url"  =>  "http://uat.ag.mos.ru/mvag/auth?callback=agauth",
            "bcc_url"   =>  "http://pre-prod02.shop.ag.mos.ru/api/",
            "name"      =>  "uat"
        ),
        array(
            "domain"    =>  "pre-prod01.shop.ag.mos.ru",
            "int_ip"    =>  "10.89.79.56",
            "ext_ip"    =>  "213.79.88.37",
            "auth_url"  =>  
                "http://testing.ag.mos.ru/mvag/auth?callback=agauth",
            "bcc_url"   =>  "http://pre-prod01.shop.ag.mos.ru/api/",
            "name"      =>  "test"
        ),
        array(
            "domain"    =>  "dev.shop.ag.mos.ru",
            "int_ip"    =>  "10.89.79.59",
            "ext_ip"    =>  "213.79.88.37",
            "auth_url"  =>  
                "http://testing.ag.mos.ru/mvag/auth?callback=agauth",
            "bcc_url"   =>  "http://dev.shop.ag.mos.ru/api/",
            "name"      =>  "test"
        ),
     );


    $sContour = 'test';    
    $sCrossDomainAuthURL = 'http://testing.ag.mos.ru/mvag/auth?callback=agauth';
    $sBCCUrl = 'http://127.0.0.1/api/';
    foreach($arContours as $arContour)
        if(
            $arContour["domain"]==$_SERVER["HTTP_HOST"]
            || 
            $arContour["int_ip"]==$_SERVER["HTTP_HOST"]
        ){ 
            $sContour = $arContour["name"]; 
            $sCrossDomainAuthURL = $arContour["auth_url"];
            $sBCCUrl = $arContour["bcc_url"];
            break; 
        }
    // Define current countour
    define("CONTOUR",$sContour);
    // Define contour URL
    define( "CONTOUR_URL", $sCrossDomainAuthURL);
    // Define Bals Conversion Controller
    define( "BCC_URL", $sBCCUrl);

    //
    if(preg_match("#^/bitrix/admin#",$_SERVER["REQUEST_URI"]))
        define("ORDERS_EXCHANGE_ADMIN_MODE", true);
    else
        define("ORDERS_EXCHANGE_ADMIN_MODE", false);


