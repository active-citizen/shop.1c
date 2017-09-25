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
    <?  $APPLICATION->ShowPanel(); ?>

<? if(
    // Заглушка
    0 
    &&
    !$USER->IsAuthorized()
    &&
    !preg_match("#^/partners/#", $_SERVER["REQUEST_URI"])
):?>
<h2 style="width: 80%;margin: 100px auto; text-align: center;">Раздел<br/> "Магазина<br/> поощрений"<br/> временно<br/> недоступен<br/>
в связи<br/> с обновлениями.
<br/>Приносим свои<br/> извинения.</h2>
<? else: ?>
    <div class="ag-shop">
    <? if(
        !preg_match("#^/partners/#", $_SERVER["REQUEST_URI"])
    ):?>
    <? if(IS_MOBILE && $_SERVER["REQUEST_URI"]!='/catalog/'):?>
      <div class="ag-shop-mob-nav">
        <a class="ag-shop-mob-back" href="<? 
            if(
                $arPath = explode("/",$_SERVER["REQUEST_URI"])
            ){
                if($arPath[1]!='catalog'){
                    echo "/catalog/";
                }
                elseif($arPath[1]=='catalog' && trim($arPath[2]) &&
                !trim($arPath[3])){
                    echo "/catalog/";
                }
                elseif($arPath[1]=='catalog' && trim($arPath[2]) &&
                trim($arPath[3])){
                    echo "/catalog/".$arPath[2]."/";
                }
                else{
                    echo "/catalog/";
                }

            }
        ?>"
        ></a>
        <?  
            if(
                $arPath = explode("/",$_SERVER["REQUEST_URI"])
            ){
                if($arPath[1]!='catalog'){
                    echo "Главная";
                }
                elseif($arPath[1]=='catalog' && trim($arPath[2]) &&
                !trim($arPath[3])){
                    echo "Главная";
                }
                elseif(
                    $arPath[1]=='catalog' 
                    && 
                    trim($arPath[2]) 
                    &&
                    trim($arPath[3])
                ){
                    $arCatalogMeta = CIBlockSection::GetList(
                        array(),
                        array(
                            "IBLOCK_ID" =>  CATALOG_IB_ID,
                            "CODE"      =>  $DB->ForSql($arPath[2])
                        ),
                        false,
                        array("NAME"),
                        array("nTopCount"=>1)
                    )->GetNext();
                    if(
                        isset($arCatalogMeta["NAME"]) 
                        && 
                        trim($arCatalogMeta["NAME"])
                    )
                    echo $arCatalogMeta["NAME"];
                }
                else{
                    echo "Главная";
                }

            }
        ?>
      </div>
      <div class="ag-shop-mob-top-spacer">
        &#160;
      </div>
    <? endif ?>
      <div class="ag-shop__sidebar">
        <!-- Sidebar {{{-->
        <div class="ag-shop-sidebar">
          <div class="ag-shop-sidebar__logo-container"><a
          class="ag-shop-sidebar__logo" href="http://ag.mos.ru/"></a></div>
          <div class="ag-shop-sidebar__social-container">
            <div class="ag-shop-sidebar__social-link"><a
            class="ag-shop-social-link ag-shop-social-link--vk"
            href="https://vk.com/citizenmoscow"></a></div>
            <div class="ag-shop-sidebar__social-link"><a
            class="ag-shop-social-link ag-shop-social-link--fb"
            href="https://www.facebook.com/citizenmoscow"></a></div>
            <div class="ag-shop-sidebar__social-link"><a
            class="ag-shop-social-link ag-shop-social-link--tw"
            href="https://twitter.com/citizenmoscow"></a></div>
            <div class="ag-shop-sidebar__social-link"><a
            class="ag-shop-social-link ag-shop-social-link--inst"
            href="https://www.instagram.com/citizenmoscow/"></a></div>
          </div>
          <button class="ag-shop-sidebar__up" type="button"></button>
        </div>
        <!-- }}} Sidebar-->
      </div>
    <? endif ?>


      <div class="ag-shop__main">
    <? if(
        !preg_match("#^/partners/#", $_SERVER["REQUEST_URI"])
        &&
        !IS_MOBILE
    ):?>
    <?$APPLICATION->IncludeComponent("ag:menu.top", "", array(
            "CACHE_TIME"      =>  COMMON_CACHE_TIME
        ),
        false
    );?>
    <? endif ?>

    <? if(IS_MOBILE):?>
        <div class="ag-shop-menu">
          <div class="ag-shop-menu__container">
            <div class="ag-shop-menu__header">
              <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
                <div class="grid__col grid__col-shrink">
                  <h2 class="ag-shop-menu__current">
                    <? if(preg_match("#^/catalog/$#",$_SERVER["REQUEST_URI"])):?>
                        Главная
                    <? elseif(preg_match("#^/profile/order/#",$_SERVER["REQUEST_URI"])):?>
                        Мои заказы
                    <? elseif(preg_match("#^/profile/wishes/#",$_SERVER["REQUEST_URI"])):?>
                        Мои желания
                    <? elseif(preg_match("#^/rules/hiw/#",$_SERVER["REQUEST_URI"])):?>
                        Как это работает
                    <? elseif(preg_match("#^/rules/stores/#",$_SERVER["REQUEST_URI"])):?>
                        Адреса
                    <? elseif(preg_match("#^/rules/faq/#",$_SERVER["REQUEST_URI"])):?>
                        FAQ
                    <? else:?>
                        Навигация
                    <? endif ?>
                  </h2>
                </div>
                <div class="grid__col grid__col-shrink">
                  <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button" type="button"><span></span></button>
                </div>
              </div>
            </div>
            <div class="ag-shop-menu__items js-menu__list">
              <div class="ag-shop-menu__item"><a
              class="ag-shop-menu__link"><a class="ag-shop-menu__link" href="/">Главная</a></div>
              <div class="ag-shop-menu__item"><a class="ag-shop-menu__link<?if(preg_match("#^/profile/order/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/profile/order/">Мои заказы</a></div>
              <div class="ag-shop-menu__item"><a class="ag-shop-menu__link<?if(preg_match("#^/profile/wishes/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/profile/wishes/">Мои желания</a></div>
              <div class="ag-shop-menu__item">
                <a class="ag-shop-menu__link<?if(preg_match("#/rules/hiw/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/rules/hiw/">Как это работает?</a>
              </div>
              <div class="ag-shop-menu__item">
                <a class="ag-shop-menu__link<?if(preg_match("#/rules/stores/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/rules/stores/">Адреса</a>
              </div>
              <div class="ag-shop-menu__item">
                <a class="ag-shop-menu__link<?if(preg_match("#/rules/faq/.*#",$_SERVER["REQUEST_URI"])){?> ag-shop-menu__link--active<?}?>" href="/rules/faq/">FAQ</a>
              </div>
            </div>
          </div>
        </div>

        <?
        
            $APPLICATION->IncludeComponent("ag:menu.catalog", "", array(
                "CACHE_TIME"      =>  COMMON_CACHE_TIME
            ),
            false
        );
        
        ?>

    <? endif ?>

    <? if(
        !IS_MOBILE
        &&
        preg_match("#^/catalog/#", $_SERVER["REQUEST_URI"])
    ):?>
    <?$APPLICATION->IncludeComponent("ag:menu.catalog", "", array(
            "CACHE_TIME"      =>  COMMON_CACHE_TIME
        ),
        false
    );?>
    <? endif ?>

<? endif ?>


