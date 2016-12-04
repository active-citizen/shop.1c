<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


            <div class="ag-shop-rules">
              <div class="ag-shop-rules__content">
                <mark><strong>Ответы на часто задаваемые вопросы:</strong></mark>
              </div>
              <div class="ag-shop-rules__container">
                <? foreach($arResult["faq"] as $faq):?>
                  <a 
                    class="ag-shop-rules__spoiler-link js-spoiler__link hash-navigation" 
                    name="<?= $faq["CODE"]?>" 
                    href="#<?= $faq["CODE"]?>"
                    id="faq-click-<?= $faq["CODE"]?>"
                  >- <?= $faq["NAME"] ?></a>
                  <div class="ag-shop-rules__content ag-shop-rules__content--gaps js-spoiler__content">
                    <p><?= $faq["~DETAIL_TEXT"];?></p>
                  </div>                
                <? endforeach?>
              </div>
            </div>
            
