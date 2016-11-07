<?
define("NO_KEEP_STATISTIC", true); // Не собираем стату по действиям AJAX
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/libs/rus.lib.php");

    $ON_PAGE = 10;
    $PAGE = isset($_REQUEST["PAGE"])?intval($_REQUEST["PAGE"]):1;


    CModule::IncludeModule("iblock");
    CModule::IncludeModule("catalog");

    $arrFilter = array();
    $arrSorting = array();
    
    // Составляем справочник флагов
    $ENUMS = array();
    $res = CIBlockPropertyEnum::GetList(array(),array("IBLOCK_ID"=>2));
    while($data = $res->getNext()){
        $enum = CIBlockPropertyEnum::GetByID($data["ID"]);
        if(!isset($ENUMS[$data["PROPERTY_CODE"]]))$ENUMS[$data["PROPERTY_CODE"]] = array();
        $ENUMS[$data["PROPERTY_CODE"]][$enum["VALUE"]] = $enum["ID"];
    }

    
    if(isset($_REQUEST['sorting']) && $_REQUEST['sorting']=='rating-desc'){
        $arrSorting["PROPERTY_RATING"]="DESC";
    }
    elseif(isset($_REQUEST['sorting']) && $_REQUEST['sorting']=='price-desc'){
        $arrSorting["PROPERTY_MINIMUM_PRICE"]="DESC";
    }
    else{
        $arrSorting["PROPERTY_MINIMUM_PRICE"]="ASC";
    }
    
    if(isset($_REQUEST['flag']) && $_REQUEST['flag']=='news'){
        $arrFilter["PROPERTY_NEWPRODUCT"] = $ENUMS['NEWPRODUCT']["да"];
    }
    if(isset($_REQUEST['flag']) && $_REQUEST['flag']=='actions'){
        $arrFilter["PROPERTY_SPECIALOFFER"] = $ENUMS['SPECIALOFFER']["да"];
    }
    if(isset($_REQUEST['flag']) && $_REQUEST['flag']=='populars'){
        $arrFilter["PROPERTY_SALELEADER"] = $ENUMS['SALELEADER']["да"];
    }
    
    
    if(isset($_REQUEST['filter_iwant']) && preg_match("#^\d+(\,\d+)*$#",$_REQUEST['filter_iwant'])){
        $iwant = explode(",",$_REQUEST['filter_iwant']);
        if(!count($iwant)){
        }else{
            $arrFilter["PROPERTY_WANTS"] = $iwant;
        }
    }

    if(isset($_REQUEST['filter_type']) && preg_match("#^\d+(\,\d+)*$#",$_REQUEST['filter_type'])){
        $type = explode(",",$_REQUEST['filter_type']);
        if(!count($type)){
        }else{
            $arrFilter["PROPERTY_TYPES"] = $type;
        }
    }
    
    if(isset($_REQUEST['filter_interest']) && preg_match("#^\d+(\,\d+)*$#",$_REQUEST['filter_interest'])){
        $interest = explode(",",$_REQUEST['filter_interest']);
        if(!count($interest)){
        }else{
            $arrFilter["PROPERTY_INTERESTS"] = $interest;
        }
    }

    if(isset($_REQUEST['filter_balls']) && preg_match("#^[\d\ ]+(\,[\d\ ]+)*$#",$_REQUEST['filter_balls'])){
        $balls = explode(",",$_REQUEST['filter_balls']);
        $arrFilter[">=PROPERTY_MINIMUM_PRICE"] = $balls[0];
        $arrFilter["<=PROPERTY_MINIMUM_PRICE"] = $balls[1];
        /*
        // Узнаём ID инфоблока
        $res = CIBlock::GetList(array(),array("CODE"=>"clothes_offers"));
        $iblock = $res->GetNext();
        //$arrFilter["<=CATALOG_PRICE_1"] = $balls;
        $arrFilter[] = array(
            "LOGIC"=>"OR",
            array(
                "<=CATALOG_PRICE_1"=>$balls
            ),
            array(
                "=ID"=>CIBlockElement::SubQuery("PROPERTY_CML2_LINK",array("IBLOCK_ID"=>$iblock["ID"],"<=CATALOG_PRICE_1"=>$balls))
            )
        );
        */
    }
    elseif(!isset($_REQUEST['filter_balls'])){
        $arrFilter["<=PROPERTY_MINIMUM_PRICE"] = 1500;
    }
    $arrFilter["ACTIVE"] = 'Y';
    
    // Узнаём ID инфоблока
    $res = CIBlock::GetList(array(),array("CODE"=>"clothes"));
    $iblock = $res->GetNext();
    $arrFilter["IBLOCK_ID"] = $iblock["ID"];
    
    $res = CIBlockElement::GetList(
        $arrSorting,
        $arrFilter,
        false,
        array("iNumPage"=>$PAGE,"nPageSize"=>$ON_PAGE),
        array("PROPERTY_RATING","PROPERTY_MINIMUM_PRICE","ID","DETAIL_PICTURE","DETAIL_PAGE_URL")
    );
    
    while($product = $res->GetNext()){

        // Получение всех свойств товара
        $res2 = CIBlockElement::GetProperty($arrFilter["IBLOCK_ID"],$product["ID"]);
        $product["ALL_PROPERTIES"] = array();
        while($row = $res2->GetNext())$product["ALL_PROPERTIES"][$row["CODE"]] = $row;
        
        $image_url = '';
        if($file_id = intval($product["DETAIL_PICTURE"]))$image_url = CFile::GetPath($file_id);

        // Входит ли товар с писок моих желаний
        $arFilter = array("IBLOCK_CODE"=>"whishes", "PROPERTY_WISH_USER"=>CUser::GetID(),"PROPERTY_WISH_PRODUCT"=>$product["ID"]);
        $res1 = CIBlockElement::GetList(array(),$arFilter,false, array("nTopCount"=>1));
        $product["mywish"] = $res1->SelectedRowsCount();
        
        // Сколько у товара всего желающих
        $arFilter = array("IBLOCK_CODE"=>"whishes", "PROPERTY_WISH_PRODUCT"=>$product["ID"]);
        $res1 = CIBlockElement::GetList(array(),$arFilter,false, array());
        $product["wishes"] = $res1->SelectedRowsCount();

        $product["mark"] = $product["PROPERTY_RATING_VALUE"];

//        echo "<pre>";
//        print_r($image_url);
//        print_r($product);
//        echo "</pre>";
//        die;
        ?>
        <div class="ag-main-product" title="<?= $product["PROPERTY_CML2_LINK.NAME"];?>" style="background-image: url(<?= $image_url?>);">
            <a href="<?= $product["DETAIL_PAGE_URL"]?>" title="<?= $product["NAME"];?>">
            </a>
            <div class="ag-product-wish <?= $product['mywish']?"wish-on":"wish-off"?>" title="Добавить в мои желания" productid="<?= $product['ID']?>" onclick="return mywish(this)"><?= $product['wishes'];?></div>
            <? if($product["mark"]):?>
            <div class="ag-product-mark" style="right: <?= round(4+24*(1-$product["mark"]))?>px;background-position: <?= round(24*(1-$product["mark"]))?>px 0%;" title="Средняя оценка <?= round(5*$product["mark"],1)?>" productid="<?= $product['ID']?>"></div>
            <? endif ?>
            <div class="ag-main-price"><?= number_format($product["PROPERTY_MINIMUM_PRICE_VALUE"],0,","," ")?> <?= get_points($product["PROPERTY_MINIMUM_PRICE_VALUE"])?></div>
            
            <? $top = 10;?>
            <? if($product["ALL_PROPERTIES"]["NEWPRODUCT"]["VALUE_ENUM"]=='да'):?>
            <div class="ag-newproduct" title="Новинка" style="top: <?= $top ?>px">Новинка</div>
            <? $top += 60;?>
            <? endif?>
        
            <? if($product["ALL_PROPERTIES"]["SALELEADER"]["VALUE_ENUM"]=='да'):?>
            <div class="ag-saleleader" title="Лидер продаж" style="top: <?= $top ?>px">Лидер продаж</div>
            <? $top += 60;?>
            <? endif?>
        
            <? if($product["ALL_PROPERTIES"]["SPECIALOFFER"]["VALUE_ENUM"]=='да'):?>
            <div class="ag-specialoffer" title="Спецпредложение" style="top: <?= $top ?>px">Спец- предло- жение</div>
            <? endif?>
            
        </div>
        <?
    }
    
    $request = "";
    foreach($_REQUEST as $key=>$value){$request.="$key=$value&";}
    
    ?>
    
    <?if($res->SelectedRowsCount()>$PAGE*$ON_PAGE):?><a class="next-page" href="#" onclick="return next_page('<?= $request."PAGE=".($PAGE+1);?>');">&#9660; Загрузить ещё &#9660;</a><?endif?>
    
    <?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
