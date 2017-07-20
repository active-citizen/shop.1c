<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>


<div class="ag-shop-rules">
    <div class="ag-shop-rules__content">
        <mark><strong>Ответы на часто задаваемые вопросы</strong></mark>
          <div class="ag-shop-rules__container ag-shop-rules__content--gaps">
            <? if($arResult["section"]["NAME"]):?>
                <mark><strong><?= $arResult["section"]["NAME"]?></strong></mark>
            <? endif?>
            <? if($arResult["section"]["DESCRIPTION"]):?>
                <div class="ag-shop-rules__content ag-shop-rules__content--gaps">
                    <p>
                    <?= $arResult["section"]["DESCRIPTION"]?>
                    </p>
                </div>
            <? endif?>
            <? foreach($arResult["sections"] as $arSection):?>
              <div>
              <a 
                class="ag-shop-rules__spoiler-link
                hash-navigation" 
                href="<?= $arParams["DASE_PATH"]?><?= $arSection["ID"]?>/"
              ><?= $arSection["NAME"] ?></a>
              </div>
            <? endforeach?>
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
</div>
            
