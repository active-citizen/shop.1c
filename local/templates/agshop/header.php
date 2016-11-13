<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

    include_once($_SERVER["DOCUMENT_ROOT"]."/libs/rus.lib.php");
    
    // Получаем корневых разделов
    CModule::IncludeModule("iblock");
    $res = CIBlockSection::GetList(
        array(),
        array("ACTIVE"=>"Y","IBLOCK_CODE"=>"clothes","SECTION_ID"=>0),
        false,
        false
    );

    $SECTIONS = array();
    while($section = $res->getNext()){
        $SECTIONS[$section["ID"]] = $section;
        $res1 = CIBlockElement::GetList(
            array(),array("SECTION_ID"=>$section["ID"]),false
        );
        $SECTIONS[$section["ID"]]["products"]=$res1->SelectedRowsCount();
    }
    
    CModule::IncludeModule("sale");
    $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
    $account = $res->GetNext();
    
    $MY_BALLS = number_format($account["CURRENT_BUDGET"],0 ,',',' ');
    $myBalls = $MY_BALLS." ".get_points($account["CURRENT_BUDGET"]);

    $arUserInfo = $USER->GetById($USER->GetId())->GetNext();
    $FIO = $arUserInfo["NAME"]."<br/>".$arUserInfo["LAST_NAME"];
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
        //$APPLICATION->ShowHead();
    ?>                                                                                                                                                           
    <?  
    //$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/colors.css");                                                                                                                       
    //$APPLICATION->SetAdditionalCSS("/bitrix/css/main/font-awesome.css");
    ?>

    <link rel="stylesheet" href="/local/assets/styles/fonts.css">
    <link rel="stylesheet" href="/local/assets/styles/components.css">
    <link rel="stylesheet" href="/local/assets/styles/profile.css">
    <link rel="stylesheet" href="/local/assets/styles/catalog.css">
    <link rel="stylesheet" href="/local/assets/styles/rules.css">
    <link rel="stylesheet" href="/local/assets/libs/jquery-ui.css">
    <link rel="stylesheet" href="/local/assets/libs/slick.css">
    <link rel="stylesheet" href="/local/assets/styles/mod.css">
    
    <script src="/local/assets/libs/jquery.min.js"></script>
    <script src="/local/assets/libs/jquery-ui.js"></script>
    <script src="/local/assets/libs/slick.min.js"></script>
    <script src="/local/assets/scripts/index.js"></script>
    <script src="/local/assets/scripts/scripts.js"></script>
    
    <title><?$APPLICATION->ShowTitle()?></title>

  </head>
  <body>
    <div class="ag-shop">
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


      <div class="ag-shop__main">
        <!-- Top Nav {{{-->
        <nav class="ag-shop-nav">
          <div class="ag-shop-nav__container">
            <div class="grid grid--bleed">
              <div class="grid__col-auto grid__col-md-shrink">
                <a class="ag-shop-nav__link" href="/catalog/" style="padding-left: 0px;">
                <div class="ag-shop-nav__link <? if(preg_match("#^/catalog/.*$#", $_SERVER["REQUEST_URI"])):?>ag-shop-nav__link--active<? endif ?>"><i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--basket"></i>
                  <div class="ag-shop-nav__link-caption">Магазин<span class="show-on-desktop">&nbsp;поощрений</span></div>
                </div>
                </a>
              </div>
              <div class="grid__col-auto grid__col-md-shrink">
                  <a class="ag-shop-nav__link<? if(preg_match("#^/profile/.*$#", $_SERVER["REQUEST_URI"])):?> ag-shop-nav__link--active<? endif ?>" href="/profile/">
                  <div class="ag-shop-nav__profile-container">
                      <i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--profile"></i>
                    <div class="ag-shop-nav__link-caption">
                      <span class="hide-on-desktop">Профиль</span>
                      <span class="show-on-desktop"><?= $FIO;?></span>
                    </div>
                    <div class="ag-shop-nav__profile-points"><?= $myBalls;?></div>
                  </div></a></div>
              <div class="grid__col-auto grid__col-md-shrink">
                  <a class="ag-shop-nav__link <? if(preg_match("#^/rules/.*$#", $_SERVER["REQUEST_URI"])):?>ag-shop-nav__link--active<? endif ?>" href="/rules/">
                  <i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--rules"></i>
                  <div class="ag-shop-nav__link-caption">
                     Правила
                  </div>
                  </a>
                </div>
              <!-- Можно выпилить целиком, если не нужно-->
              <div class="grid__col-auto grid__col-md-shrink ag-shop-nav__last-item">
                <button class="ag-shop-nav__link" type="button" style="padding:0;/*safari/firefox*/">
                  <div class="ag-shop-nav__link">
                    <form action="/search/" class="searchform">
                      <input name="q" type="text" placeholder="Поиск" value="<?= htmlspecialchars(isset($_GET["q"])?$_GET["q"]:"") ?>">
                    </form>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </nav>
        <!-- }}} Top Nav-->



