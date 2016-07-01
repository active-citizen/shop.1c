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
    <link rel="stylesheet" href="/bitrix/templates/agnew/css/jquery-ui.css">
    <link rel="stylesheet" href="/bitrix/templates/agnew/css/fotorama.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="/bitrix/templates/agnew/ag-styles.css" />
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
                    <a href="/points/" class="ag2-menu-item" >Мои баллы</a>
                    <a href="/rules/" class="ag2-menu-item" >Мои правила</a>
                    <a href="" class="ag2-menu-item profile" >Иван Иванович Иванов(<? 
                   CModule::IncludeModule("sale");
                   $res = CSaleUserAccount::GetList(array("TIMESTAMP_X"=>"DESC"),array("USER_ID"=>CUser::GetID()));
                   $account = $res->GetNext();
                   if($account["CURRENT_BUDGET"])echo round($account["CURRENT_BUDGET"]);
                   ?>)</a>
                </div>
                
                <div class="ag2-bottom-part">
                    <div class="ad-search-form">
                        <input type="text" name="searchtext" placeholder="Поиск">
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
            
    