  <!--Подключение Fontawesome-->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

<!--Стили для карусели-->
<style type="text/css">
  /*CSS slider*/

  .buttons-carousel {
    margin: 5px 0;
    margin-left: 17px;
  }
  #next, #prev {
    font-size: 14px;
    display: inline;
    padding: 3px 6px;
    border: none;
    background: none;
    border-radius: 5px;
    outline: none;
    cursor: pointer;
  }

  #carouselWrapper {
    position: relative;
    overflow: hidden;
  }
  #carousel {
    position: absolute;
    visibility: hidden;
  }

  .ag-shop-card__preview--active{
    border: none;
  }

  </style>

<div class="ag-shop-card__image-block">
  <div class="ag-shop-card__image-wrap">

    <div class="desktop-product-price">
        <div class="desktop-product-price-wrapper">
            <div class="middle-aligned">
                <b class="desktop-product-price__summ ag-shop-item-card__points-count"><?=
number_format($arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"],0,",","")                                    
                ?></b>
                <span
                class="desktop-product-price__currency ag-shop-item-card__points-text"><?=
                    \Utils\CLang::getPoints(
$arResult["OFFERS"][0]["RRICE_INFO"]["PRICE"]                                        
                    )
                ?></span>
            </div>

        </div>
    </div>
  
    <div class="ag-shop-card__image-container" style="background-image: url(<?= 
        $arResult["OFFERS"][0]["PROPERTIES"]["MORE_PHOTO"][0]["FILE_PATH"]
      ?>)">
      <div class="ag-shop-card__map" style="display:none"></div>
      <div class="ag-shop-card__image"></div>
      <div class="ag-shop-card__image-info wrap_margin_top">
        <div style="margin-top: 50px;">  
        <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["NEWPRODUCT"][0]["VALUE_ENUM"]=='да'):?>
        <div class="ag-shop-card__image-badges image-badges_margin-0"><img class="ag-shop-item-card__badge" src="/local/assets/images/badge__new.png"></div>
        <? endif ?>

        <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["SALELEADER"][0]["VALUE_ENUM"]=='да'):?>
        <div class="ag-shop-card__image-badges image-badges_margin-0"><img class="ag-shop-item-card__badge" src="/local/assets/images/badge__hit.png"></div>
        <? endif ?>

        <? if($arResult["CATALOG_ITEM"]["PROPERTIES"]["SPECIALOFFER"][0]["VALUE_ENUM"]=='да'):?>
        <div class="ag-shop-card__image-badges image-badges_margin-0"><img class="ag-shop-item-card__badge" src="/local/assets/images/badge__sale.png"></div>
        <? endif ?>
        </div>
        
        
      </div>
      <button class="ag-shop-item-card__likes" type="button">
        <div class="ag-shop-item-card__likes-icon<?if($arResult["MYWISH"]):?> wish-on<? endif ?>"
        productId="<?= $arResult["CATALOG_ITEM"]["ID"]?>"
        <? if($USER->IsAuthorized() && !$arResult["MARK"]):?>
        onclick="return mywish(this)"
        <? endif ?>
        ></div>
        <div class="ag-shop-item-card__likes-count" id="wishid<?= $arResult["CATALOG_ITEM"]["ID"]?>"><?= $arResult["WISHES"];?></div>
      </button>
    
    </div>
    
    
    <div class="ag-shop-card__previews-container">
    
        
    <?$arPics = [];
        foreach($arResult["OFFERS"] as $arOffer)
            foreach($arOffer["PROPERTIES"]["MORE_PHOTO"] as $key=>$morePhoto)
                $arPics[] = $morePhoto["FILE_PATH"];
        $arPics = array_unique($arPics);
        print_r($arPics);
    ?>
   
<!--Carousel-->
   <div class="slider">
   <div class="buttons-carousel">
     <button id="prev"><i class="fas fa-angle-up"></i></button>
   </div>
    <div id="carousel">
    <? foreach($arPics as $key=>$morePhoto):?>
     
    
      <div class="ag-shop-card__preview<?if(!$key):?> ag-shop-card__preview--active<? endif ?>" style="background-image: url(<?=$morePhoto?>);" rel="<?= $morePhoto;?>"></div>
       
    <? endforeach ?>
    </div>
     <div class="buttons-carousel">
    <button id="next"><i class="fas fa-angle-down"></i></button> 
       </div>
    </div>
  <!--End carousel-->
   

    </div>
   
  </div>
</div>


