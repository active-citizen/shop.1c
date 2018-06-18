<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
    $this->createFrame()->begin("Загрузка");
?>


<div class="ag-shop-rules">
    <div class="ag-shop-rules__content">
        <mark><strong>Ответы на часто задаваемые вопросы</strong></mark>
          <div class="ag-shop-rules__container ag-shop-rules__content--gaps">

            <? if($arResult["section"]["DESCRIPTION"]):?>
                <div class="ag-shop-rules__content ag-shop-rules__content--gaps">
               </div>
            <? endif?>

            <? foreach($arResult["sections"] as $arSection):?>
                <div class="faq-section">
                    <a 
                        class="ag-shop-rules__spoiler-link
                        hash-navigation faq-section" 
                        href="#<?= $arSection["ID"] ?>"
                    ><?= 
                        $arSection["NAME"] 
                    ?></a>
                    <div class="faq-section-spoiler" style="display:none;"
                        id="faq-section-id-<?= $arSection["ID"]?>"
                    >
                        <p>
                        <?= $arSection["DESCRIPTION"]?>
                        </p>

                        <div class="ag-shop-rules__container">
                            <? foreach($arSection["childs"] as $faq):?>
                              <a 
                                class="ag-shop-rules__spoiler-link js-spoiler__link hash-navigation" 
                                name="<?= $faq["CODE"]?>" 
                                href="#<?= $arSection["ID"]?>.<?= $faq["ID"]?>"
                                id="faq-click-<?= $faq["ID"]?>"
                              >- <?= $faq["NAME"] ?></a>
                              <div class="ag-shop-rules__content
                              ag-shop-rules__content--gaps js-spoiler__content"
                              id="faq-question-<?= $faq["ID"]?>">
                                <p><?= str_replace("\n","<br>",$faq["~DETAIL_TEXT"]);?></p>
                              </div>                
                            <? endforeach?>
                        </div>
                        
                    </div>
                </div>
            <? endforeach?>

        </div>

    </div>
</div>

