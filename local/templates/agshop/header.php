<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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
    <title>ag-shop__main</title>

    <?$APPLICATION->ShowHead();?>                                                                                                                                                           
    <?                                                                                                                                                                                      
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/colors.css");                                                                                                                       
    $APPLICATION->SetAdditionalCSS("/bitrix/css/main/font-awesome.css");
    ?>

    <link rel="stylesheet" href="/local/assets/styles/fonts.css">
    <link rel="stylesheet" href="/local/assets/styles/components.css">
    <link rel="stylesheet" href="/local/assets/styles/catalog.css">
    
    <script src="/local/assets/libs/jquery.min.js"></script>
    <script src="/local/assets/libs/jquery-ui.js"></script>
    
    <script src="/local/assets/scripts/scripts.js"></script>
    <link rel="stylesheet" href="/local/assets/libs/slick.css">
    <link rel="stylesheet" href="/local/assets/libs/jquery-ui.css">
    
    <script src="/local/assets/libs/slick.min.js"></script>
    <script src="/local/assets/scripts/index.js"></script>

    
    <title><?$APPLICATION->ShowTitle()?></title>

  </head>
  <body>
    <div class="ag-shop">
      <div class="ag-shop__sidebar">
        <!-- Sidebar {{{-->
        <div class="ag-shop-sidebar">
          <div class="ag-shop-sidebar__logo-container"><a class="ag-shop-sidebar__logo" href="#"></a></div>
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


      <div class="ag-shop__sidebar">
        <!-- Sidebar {{{-->
        <div class="ag-shop-sidebar">
          <div class="ag-shop-sidebar__logo-container"><a class="ag-shop-sidebar__logo" href="#"></a></div>
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
                <div class="ag-shop-nav__link ag-shop-nav__link--active"><i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--basket"></i>
                  <div class="ag-shop-nav__link-caption">Магазин<span class="show-on-desktop">&nbsp;поощрений</span></div>
                </div>
              </div>
              <div class="grid__col-auto grid__col-md-shrink"><a class="ag-shop-nav__link" href="#">
                  <div class="ag-shop-nav__profile-container"><i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--profile"></i>
                    <div class="ag-shop-nav__link-caption">
                      <span class="hide-on-desktop">Профиль</span>
                      <span class="show-on-desktop">Константин&nbsp;Констанинович<br>Иванов</span>
                    </div>
                    <div class="ag-shop-nav__profile-points">
                       1654&nbsp;балла</div>
                  </div></a></div>
              <div class="grid__col-auto grid__col-md-shrink"><a class="ag-shop-nav__link" href="#"><i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--rules"></i>
                  <div class="ag-shop-nav__link-caption">
                     Правила</div></a></div>
              <!-- Можно выпилить целиком, если не нужно-->
              <div class="grid__col-auto grid__col-md-shrink ag-shop-nav__last-item">
                <button class="ag-shop-nav__link" type="button" style="padding:0;/*safari/firefox*/">
                  <div class="ag-shop-nav__link"><i class="ag-shop-nav__link-icon ag-shop-nav__link-icon--search"></i>
                    <div class="ag-shop-nav__link-caption">
                       Поиск</div>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </nav>
        <!-- }}} Top Nav-->
        <!-- Menu {{{-->
        <div class="ag-shop-menu">
          <div class="ag-shop-menu__container">
            <div class="ag-shop-menu__header">
              <div class="grid grid--bleed grid--justify-space-between grid--align-content-center">
                <div class="grid__col grid__col-shrink">
                  <h2 class="ag-shop-menu__current">Все&nbsp;категории</h2>
                </div>
                <div class="grid__col grid__col-shrink">
                  <button class="ag-shop-menu__button ag-shop-menu__button--lines js-menu__button" type="button"><span></span></button>
                </div>
              </div>
            </div>
            <div class="ag-shop-menu__items js-menu__list">
                
                <?php foreach($SECTIONS as $section):?>
                <? if(!$section["products"])continue;?>
                <div class="ag-shop-menu__item">
                    <a class="ag-shop-menu__link<? if(preg_match("#^".$section["SECTION_PAGE_URL"]."#",$_SERVER["REQUEST_URI"])):?> ag-shop-menu__link--active<? endif?>" 
                        href="<?= $section["SECTION_PAGE_URL"];?>">
                        <?= $section["NAME"];?>
                    </a>
                </div>
                <?endforeach?>
            </div>
          </div>
        </div>
        <!-- }}} Menu-->
        <div class="ag-shop-content">



