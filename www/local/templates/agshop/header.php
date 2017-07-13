<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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


$APPLICATION->AddHeadScript("/local/assets/libs/jquery.min.js");
$APPLICATION->AddHeadScript("/local/assets/libs/jquery-ui.js");
$APPLICATION->AddHeadScript("/local/assets/libs/slick.min.js");
$APPLICATION->AddHeadScript("/local/assets/scripts/index.js");
$APPLICATION->AddHeadScript("/local/assets/scripts/scripts.js");
$APPLICATION->AddHeadScript("/local/assets/scripts/common.js");
$APPLICATION->AddHeadScript("/local/assets/scripts/troika.js");

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

    <?
        $APPLICATION->ShowHead();
    ?>                                                                                                                                                           
    <?  
    //$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/colors.css");                                                                                                                       
    //$APPLICATION->SetAdditionalCSS("/bitrix/css/main/font-awesome.css");
    ?>

    <?
    /*
    <link rel="stylesheet" href="/local/assets/styles/fonts.css">
    <link rel="stylesheet" href="/local/assets/styles/components.css">
    <link rel="stylesheet" href="/local/assets/styles/profile.css">
    <link rel="stylesheet" href="/local/assets/styles/catalog.css">
    <link rel="stylesheet" href="/local/assets/styles/rules.css">
    <link rel="stylesheet" href="/local/assets/styles/card.css">
    <link rel="stylesheet" href="/local/assets/libs/jquery-ui.css">
    <link rel="stylesheet" href="/local/assets/libs/slick.css">
    <link rel="stylesheet" href="/local/assets/styles/mod.css">
    
    <script src="/local/assets/libs/jquery.min.js"></script>
    <script src="/local/assets/libs/jquery-ui.js"></script>
    <script src="/local/assets/libs/slick.min.js"></script>
    <script src="/local/assets/scripts/index.js"></script>
    <script src="/local/assets/scripts/scripts.js"></script>
    <script src="/local/assets/scripts/common.js"></script>
    */
    ?>
    
    <title><?$APPLICATION->ShowTitle()?></title>

  </head>
  <body>
    <?
	$APPLICATION->ShowPanel();
    ?>
    <div class="ag-shop">
    <? if(
        !preg_match("#^/partners/#", $_SERVER["REQUEST_URI"])
    ):?>
      <div class="ag-shop__sidebar">
        <!-- Sidebar {{{-->
        <div class="ag-shop-sidebar">
          <div class="ag-shop-sidebar__logo-container"><a class="ag-shop-sidebar__logo" href="/catalog/"></a></div>
          <div class="ag-shop-sidebar__social-container">
            <div class="ag-shop-sidebar__social-link"><a class="ag-shop-social-link ag-shop-social-link--vk" href="#"></a></div>
            <div class="ag-shop-sidebar__social-link"><a class="ag-shop-social-link ag-shop-social-link--fb" href="#"></a></div>
            <div class="ag-shop-sidebar__social-link"><a class="ag-shop-social-link ag-shop-social-link--tw" href="#"></a></div>
            <div class="ag-shop-sidebar__social-link"><a class="ag-shop-social-link ag-shop-social-link--inst" href="#"></a></div>
          </div>
          <button class="ag-shop-sidebar__up" type="button"></button>
        </div>
        <!-- }}} Sidebar-->
      </div>
    <? endif ?>


      <div class="ag-shop__main">
    <? if(
        !preg_match("#^/partners/#", $_SERVER["REQUEST_URI"])
    ):?>
    <?$APPLICATION->IncludeComponent("ag:menu.top", "", array(
            "CACHE_TIME"      =>  COMMON_CACHE_TIME
        ),
        false
    );?>
    <? endif ?>

    <? if(
        preg_match("#^/catalog/#", $_SERVER["REQUEST_URI"])
    ):?>
    <?$APPLICATION->IncludeComponent("ag:menu.catalog", "", array(
            "CACHE_TIME"      =>  COMMON_CACHE_TIME
        ),
        false
    );?>
    <? endif ?>



