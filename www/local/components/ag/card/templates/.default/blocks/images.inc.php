<style type="text/css">
/*CSS slider-dowm*/
.sliderdown{
  margin-left: 5%;
}
.buttonsdown {
  margin: 5px 10px;
  
}
.buttondown {
  font-size: 14px;
  display: inline;
  padding: 3px 6px;
  border:none;
  background: #fff;
  border-radius: 5px;
  outline: none;
  margin: 20px 10px 0 0;
}

#carouselWrapperDown {
  position: relative;
  overflow: hidden;
}
#carouseldown {
  position: absolute;
  visibility: hidden;
  display: inline-flex;
}

#nextdown, #prevdown {
    font-size: 14px;
    display: inline;
    padding: 3px 0px;
    border: none;
    background: none;
    border-radius: 5px;
    outline: none;
    cursor: pointer;
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
   
<!--Carousel from the left-->
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

<!--Carousel from the down-->
<div class="sliderdown">
  <div class="buttonsdown">
    <button class="buttondown" id="prevdown"><i class="fas fa-angle-left"></i></button>
  </div>
<div id="carouseldown">
  <? foreach($arPics as $key=>$morePhoto):?>
     
    
      <div class="ag-shop-carousel-down ag-shop-card__preview<?if(!$key):?> ag-shop-card__preview--active<? endif ?>" style="background-image: url(<?=$morePhoto?>);" rel="<?= $morePhoto;?>"></div>
       
    <? endforeach ?>
</div>
<div class="buttonsdown">
  <button class="buttondown" id="nextdown"><i class="fas fa-angle-right"></i></button> 
</div>
</div>


 <script>

 if(window.matchMedia('(max-width: 1279px)').matches)
{
  var Carousel = {
  width: 55,
  height: 55,     // Images are forced into a width of this many pixels.
  numVisible: 4,  // The number of images visible at once.
  duration: 500,  // Animation duration in milliseconds.
  padding: 2     // Vertical padding around each image, in pixels.
};

function rotateForwarddown() {
  var carousel = Carousel.carousel,
      children = carousel.children,
      firstChild = children[0],
      lastChild = children[children.length - 1];
  carousel.insertBefore(lastChild, firstChild);
}
function rotateBackwarddown() {
  var carousel = Carousel.carousel,
      children = carousel.children,
      firstChild = children[0],
      lastChild = children[children.length - 1];
  carousel.insertBefore(firstChild, lastChild.nextSibling);
}

function animatedown(begin, end, finalTask) {
  var wrapper = Carousel.wrapper,
      carousel = Carousel.carousel,
      change = end - begin,
      duration = Carousel.duration,
      startTime = Date.now();
  carousel.style.left = begin + 'px';
  var animateInterval = window.setInterval(function () {
    var t = Date.now() - startTime;
    if (t >= duration) {
      window.clearInterval(animateInterval);
      finalTask();
      return;
    }
    t /= (duration / 2);
    var top = begin + (t < 1 ? change / 2 * Math.pow(t, 3) : change / 2 * (Math.pow(t - 2, 3) + 2));
    carousel.style.left = top + 'px';
  }, 1000 / 60);
}

window.onload = function () {
  
  var carousel = Carousel.carousel = document.getElementById('carouseldown'),
      images = carousel.getElementsByClassName('ag-shop-carousel-down'),
      numImages = images.length,
      imageWidth = Carousel.width,
      imageHeight = Carousel.height,
     //aspectRatio = images[0].width / images[0].height,
     //imageHeight = imageWidth / aspectRatio,
      padding = Carousel.padding,
      rowHeight = Carousel.rowHeight = imageHeight + 2 * padding;
      carousel.style.width = imageWidth + 'px';
  for (var i = 0; i < numImages; ++i) {
    var image = images[i],
        frame = document.createElement('div');
    frame.className = 'pictureFrameDown';
    var aspectRatio = image.offsetWidth / image.offsetHeight;
    image.style.width = frame.style.width = imageWidth + 'px';
    image.style.height = frame.style.height = imageHeight + 'px';
    image.style.paddingTop = padding + 'px';
    image.style.paddingBottom = padding + 'px';
    image.style.paddingRight = padding + 'px';
    image.style.paddingLeft = -5 + 'px';
    frame.style.height = 2 + rowHeight + 'px';
    frame.style.width = 2 + rowHeight + 'px';
    //frame.style.border = "1px solid black";
    frame.style.borderRadius = "3px";
    frame.style.marginTop = padding + "px";
    frame.style.marginLeft = padding + "px";
    carousel.insertBefore(frame, image);
    frame.appendChild(image);
  }
  Carousel.rowHeight = carousel.getElementsByTagName('div')[0].offsetHeight;
  carousel.style.height = Carousel.numVisible * Carousel.rowHeight + 'px';
  carousel.style.visibility = 'visible';
  var wrapper = Carousel.wrapper = document.createElement('div');
  wrapper.id = 'carouselWrapperDown';
  wrapper.style.width = 260 + 'px';
  wrapper.style.height = 65 + 'px';
  carousel.parentNode.insertBefore(wrapper, carousel);
  wrapper.appendChild(carousel);
  var prevButton = document.getElementById('prevdown'),
      nextButton = document.getElementById('nextdown');
  prevButton.onclick = function () {
    prevButton.disabled = nextButton.disabled = true;
    rotateForwarddown();
    animatedown(-Carousel.rowHeight, 1, function () {
      carousel.style.left = '0';
      prevButton.disabled = nextButton.disabled = false;
    });
  };
  nextButton.onclick = function () {
    prevButton.disabled = nextButton.disabled = true;
    rotateBackwarddown();
    animatedown(1, -Carousel.rowHeight, function () {
      carousel.style.left = '1';
      prevButton.disabled = nextButton.disabled = false;
    });
  };
};

}
  
      
    </script>

    

