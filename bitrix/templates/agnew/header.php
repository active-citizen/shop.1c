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
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/css/jquery-ui.css">
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/css/fotorama.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH?>/ag-styles.css" />
    <link rel="icon" type="image/ico" href="<?= SITE_TEMPLATE_PATH?>/i/favicon.ico">
    <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH?>/css/jquery.fancybox.css">
    <title><?$APPLICATION->ShowTitle()?></title>

</head>

<?
    // Получаем корневых разделов
    CModule::IncludeModule("iblock");
    $res = CIBlockSection::GetList(array(),array("ACTIVE"=>"Y","IBLOCK_CODE"=>"clothes","SECTION_ID"=>0),false,false);

    $SECTIONS = array();
    while($section = $res->getNext()){
        $SECTIONS[$section["ID"]] = $section;
    }
?>

    <body>
        <?$APPLICATION->ShowPanel()?>
        <div class="ag2-wrap">
        
            <div class="ag2-header">
                <div class="ag2-top-part">
                    <a class="ag2-logo" alt="Логотип" href="/"></a>
                    <a href="/catalog/" class="ag2-menu-item" >Каталог</a>
                    <a href="/order/" class="ag2-menu-item" >Мои заказы</a>
                    <a href="/points/" class="ag2-menu-item" >Мои баллы</a>
                    <a href="/wishes/" class="ag2-menu-item" >Мои желания</a>
                    <a href="/rules/" class="ag2-menu-item" >Мои правила</a>
                    <? if(CUser::IsAuthorized()):?>
                        <? 
                            CModule::IncludeModule("sale");
                            $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
                            $account = $res->GetNext();
                            $userInfo = CUser::GetByID(CUser::GetID());
                            $userInfo = $userInfo->GetNext();
                        ?>
                        <a href="http://ag.mos.ru/profile/" class="ag2-menu-item profile" >
                            <? 
                                echo $userInfo["LAST_NAME"];
                                echo $userInfo["NAME"];
                                if(!trim($userInfo["LAST_NAME"]) && !trim($userInfo["LAST_NAME"]))echo $userInfo["LOGIN"];
                            ?>
                            (<?= number_format($account["CURRENT_BUDGET"],0 ,',',' ')?>)
                        </a>
                        <a href="?logout=yes" class="ag2-menu-item profile" >Выход</a>
                    <? else:?>
                        <div class="smooth"></div>
                        <div class="ag-login-form">
                            <input type="text" id="ag-login" placeholder="Телефон в формате 79130123610">
                            <input type="password" id="ag-password" placeholder="Пароль">
                            <div id="ag-login-error">sdfsdfsdf</div>
                            <div id="go-login">Войти</div>
                        </div>
                    <? endif?>
                </div>
                
                <div class="ag2-bottom-part">
                    <div class="ad-search-form">
                        <form action="/search/">
                            <input type="text" name="q" placeholder="Поиск" value="<?= isset($_REQUEST["q"])?htmlspecialchars($_REQUEST["q"]):''?>">
                        </form>
                    </div>
                    <div class="ag-catalog-menu">
                        <?php foreach($SECTIONS as $section):?>
                        <a href="<?= $section["SECTION_PAGE_URL"];?>" 
                        <? if(preg_match("#^".$section["SECTION_PAGE_URL"]."#",$_SERVER["REQUEST_URI"])):?>class="active"<? endif?>
                        ><?= $section["NAME"];?></a>
                        <?endforeach?>
                    </div>
                </div>
            </div>
