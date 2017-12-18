<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CUtil::InitJSCore(['ajax']);

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

?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="HandheldFriendly" content="True"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="format-detection" content="address=no"/>
    <meta name="msapplication-tap-highlight" content="no"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <? $APPLICATION->ShowHead(); ?>                                                                                                                                                           
    <title><?$APPLICATION->ShowTitle()?></title>

  </head>
  <body>
