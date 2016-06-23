<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>

    <?$APPLICATION->ShowHead();?>                                                                                                                                                           
    <?                                                                                                                                                                                      
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/colors.css");                                                                                                                       
    $APPLICATION->SetAdditionalCSS("/bitrix/css/main/bootstrap.css");                                                                                                                       
    $APPLICATION->SetAdditionalCSS("/bitrix/css/main/font-awesome.css");
    ?>
    <title><?$APPLICATION->ShowTitle()?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="language" content="ru"/>

    <meta property="og:title" content="Магазин поощрений - Активный Гражданин" />
    <meta property="og:description" content="Система электронных референдумов Правительства Москвы."/>
    <meta property="og:url" content="http://ag.mos.ru/"/>
    <meta property="og:image" content="http://ag.mos.ru/images/ag_logo.png"/>

    <meta name="author" content="Электронная Москва"/>
    <meta name="apple-itunes-app" content="app-id=873648765"/>
    <meta name="google-play-app" content="app-id=ru.mos.polls"/>

    <link rel="icon" type="image/ico" href="http://ag.mos.ru/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="http://ag.mos.ru/assets/style-HETdkdxocd5-3m6dgavUCQ.css" />
    
                                
    <!-- icon for smart banner -->
    
    
    <link rel="apple-touch-icon" href="http://ag.mos.ru/images/apple-touch-icon.png">
    <script type="text/javascript" src="http://ag.mos.ru/assets/script-0-m_MdmsNGFKrhf9d74r4gFA.js"></script>
    <script type="text/javascript" src="/bitrix/templates/ag/scripts.js"></script>

</head>

    <body>
        <?$APPLICATION->ShowPanel()?>
        <div class="wrapper">
            <header class="header">
                <div class="headerControll">
                    <div class="logo">
                        <a href="http://ag.mos.ru/">
                            <img class="logoImg" src="http://ag.mos.ru/images/logo_AG_1-new.png">
                        </a>
                    </div>
                    <div class="menuTop">
                        <div class="b-flag002"><a href="http://ag.mos.ru/results"></a></div>
                            <nav>
                                <ul>
                                    <li><a href="http://ag.mos.ru/poll/index"
                                           class="link ">Голосования</a>
                                    </li>
                                    <li>
                                        <a href="http://ag.mos.ru/experiments" class="link ">
                                            Городские новинки
                                        </a>
                                    </li>
                                    <li class=""><a href="/catalog"
                                           class="link active">Магазин поощрений</a>
                                        <span id="current_points"><? 
                   CModule::IncludeModule("sale");
                   $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
                   $account = $res->GetNext();
                   if($account["CURRENT_BUDGET"])echo round($account["CURRENT_BUDGET"]);
                   ?></span>
                                    </li>
                                    <li><a href="http://ag.mos.ru/site/news"
                                           class="link ">Новости</a>
                                    </li>
                                    <li><a href="http://ag.mos.ru/results"
                                           class="link ">Результаты работы</a>
                                    </li>
                                    <li><a href="http://ag.mos.ru/photos"
                                           class="link ">Проект в лицах</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="userTop">
                            <a href="http://ag.mos.ru/profile">
                                <span class="userIcoTop"></span>
                                                                                                    <p>Заполнить профиль</p>
                                                            </a>
                            <a href="/user/logout" id="logout_link"
                               class="ui-link">Выйти</a>
                        </div>

                                            <div class="reset"></div>
                </div>
            </header>
            <main class="content">
                <div class="blockBox">
    <div class="contentShopMain">
        <div class="userMenuShop">
        <ul>
            <li>
                <a class="link_2 <? if(preg_match("#^/catalog/.*#",$_SERVER["REQUEST_URI"])){?>active_2<? }?>"
                   href="/catalog/">Поощрения</a>
            </li>
            <li>
                <a class="link_2 <? if(preg_match("#^/order/$#",$_SERVER["REQUEST_URI"])){?>active_2<? }?>"
                   href="/order/">История заказов</a>
            </li>
            <li>
                <a class="link_2 <? if(preg_match("#^/points/.*#",$_SERVER["REQUEST_URI"])){?>active_2<? }?>"
                   href="/points/">Мои баллы
                   </a>
            </li>
            <li>
                <a class="link_2 <? if(preg_match("#^/rules/.*#",$_SERVER["REQUEST_URI"])){?>active_2<? }?>"
                   href="/rules/">Правила
                </a>
            </li>
            <li>
                <a class="link_2 <? if(preg_match("#^/news/.*#",$_SERVER["REQUEST_URI"])){?>active_2<? }?>"
                   href="/news/">Новости
                </a>
            </li>
        </ul>
    </div>



    
    