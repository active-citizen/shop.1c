<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CUtil::InitJSCore(['ajax']);


if(
    preg_match("#^/catalog/.*?/.+$#",$_SERVER["REQUEST_URI"])
    ||
    preg_match("#^/profile/.*$#",$_SERVER["REQUEST_URI"])
    ||
    preg_match("#^/rules/.*$#",$_SERVER["REQUEST_URI"])
){
    if(preg_match("#^/partners#",$_SERVER["REQUEST_URI"])){
        $APPLICATION->SetAdditionalCSS("/local/assets/styles/partners.css");
        $APPLICATION->SetAdditionalCSS("/local/assets/bootstrap/css/bootstrap.min.css");
        $APPLICATION->SetAdditionalCSS("/local/assets/bootstrap/css/bootstrap-theme.min.css");
    }
    
    
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/fonts.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/components.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/profile.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/catalog.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/rules.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/card.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/libs/jquery-ui.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/libs/slick.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/mod.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/troika.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/faq.css");
    $APPLICATION->SetAdditionalCSS("/local/assets/styles/semantic.css");
    
    
    $APPLICATION->AddHeadScript("/local/assets/libs/jquery.min.js");
    $APPLICATION->AddHeadScript("/local/assets/libs/jquery-ui.js");
    $APPLICATION->AddHeadScript("/local/assets/libs/slick.min.js");
    $APPLICATION->AddHeadScript("/local/assets/scripts/index.js");
    $APPLICATION->AddHeadScript("/local/assets/scripts/scripts.js");
    $APPLICATION->AddHeadScript("/local/assets/scripts/common.js");
    $APPLICATION->AddHeadScript("/local/assets/scripts/troika.js");
    $APPLICATION->AddHeadScript("/local/assets/scripts/faq.js");
    
    setcookie("LOGIN", CUser::GetLogin(),time()+600*24*60*60,"/");
}

$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/main.css");
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/scripts.min.js");


?>
<!DOCTYPE html>
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?$APPLICATION->ShowTitle()?></title>

	<meta property="og:image" content="<?php echo SITE_TEMPLATE_PATH ?>/path/to/image.jpg">
	<link rel="shortcut icon" href="<?php echo SITE_TEMPLATE_PATH ?>/img/favicon/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" href="<?php echo SITE_TEMPLATE_PATH ?>/img/favicon/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo SITE_TEMPLATE_PATH ?>/img/favicon/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo SITE_TEMPLATE_PATH ?>/img/favicon/apple-touch-icon-114x114.png">

	<!-- Chrome, Firefox OS and Opera -->
	<meta name="theme-color" content="#000">
	<!-- Windows Phone -->
	<meta name="msapplication-navbutton-color" content="#000">
	<!-- iOS Safari -->
	<meta name="apple-mobile-web-app-status-bar-style" content="#000">
    <? $APPLICATION->ShowHead(); ?>                                                                                                                                                           
</head>
<body class="container-relative">

<?$APPLICATION->IncludeComponent("ag:mobile.header", "", array(
        "CACHE_TIME"      =>  COMMON_CACHE_TIME        
    ),
    false
);?>
<?$APPLICATION->IncludeComponent("ag:mobile.filter", "", array(
        "CACHE_TIME"      =>  COMMON_CACHE_TIME        
    ),
    false
);?>

