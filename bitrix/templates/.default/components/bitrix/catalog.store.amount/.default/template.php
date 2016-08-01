<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);
if(!empty($arResult["STORES"]) && $arParams["MAIN_TITLE"] != ''):?>
<?endif;?>
<div class="bx_storege" id="catalog_store_amount_div">
	<?if(!empty($arResult["STORES"])):?>
    <div class="ag-stores-title">Получение</div>
	<ul id="c_store_amount">
        <? $first = 0; ?>
		<?foreach($arResult["STORES"] as $pid => $arProperty):?>
			<li style="display: <? echo ($arParams['SHOW_EMPTY_STORE'] == 'N' && isset($arProperty['REAL_AMOUNT']) && $arProperty['REAL_AMOUNT'] <= 0 ? 'none' : ''); ?>;"><label>
				<?if (isset($arProperty["TITLE"])):?>
					<input type="radio" name="storeid" value="<?= $arProperty['ID'];?>" style="display: inline;" <? if($arProperty['REAL_AMOUNT']>0 && !$first):$first=1?>checked<?endif?>> 
					<?=$arProperty["TITLE"]?>
				<?endif;?>
				<?if (isset($arProperty["IMAGE_ID"]) && !empty($arProperty["IMAGE_ID"])):?>
					<span class="schedule"><?=GetMessage('S_IMAGE')?> <?=CFile::ShowImage($arProperty["IMAGE_ID"], 200, 200, "border=0", "", true);?></span><br />
				<?endif;?>
				<?if (isset($arProperty["PHONE"])):?>
					<span class="tel"><?=GetMessage('S_PHONE')?> <?=$arProperty["PHONE"]?></span><br />
				<?endif;?>
				<?if (0 && isset($arProperty["SCHEDULE"])):?>
					<span class="schedule"><?=GetMessage('S_SCHEDULE')?> <?=$arProperty["SCHEDULE"]?></span><br />
				<?endif;?>
				<?if (isset($arProperty["EMAIL"])):?>
					<span><?=GetMessage('S_EMAIL')?> <?=$arProperty["EMAIL"]?></span><br />
				<?endif;?>
				<?if (isset($arProperty["DESCRIPTION"])):?>
					<span><?=GetMessage('S_DESCRIPTION')?> <?=$arProperty["DESCRIPTION"]?></span><br />
				<?endif;?>
				<?if (isset($arProperty["COORDINATES"])):?>
					<span><?=GetMessage('S_COORDINATES')?> <?=$arProperty["COORDINATES"]["GPS_N"]?>, <?=$arProperty["COORDINATES"]["GPS_S"]?></span><br />
				<?endif;?>
				<?if ($arParams['SHOW_GENERAL_STORE_INFORMATION'] == "Y") :?>
					<?=GetMessage('BALANCE')?>:
				<?else:?>
					<!-- <?=GetMessage('S_AMOUNT')?> -->
				<?endif;?>
                <?
                    if($arProperty["AMOUNT"]<20){
                        $arProperty["AMOUNT"] = 'Мало';
                    }
                    elseif($arProperty["AMOUNT"]<100){
                        $arProperty["AMOUNT"] = 'Достаточно';
                    }
                    elseif($arProperty["AMOUNT"]>=100){
                        $arProperty["AMOUNT"] = 'Много';
                    }
                ?>
				<span class="balance" id="<?=$arResult['JS']['ID']?>_<?=$arProperty['ID']?>">(<i><?=$arProperty["AMOUNT"]?></i>)</span><br />
				<?
				if (!empty($arProperty['USER_FIELDS']) && is_array($arProperty['USER_FIELDS']))
				{
					foreach ($arProperty['USER_FIELDS'] as $userField)
					{
						if (isset($userField['CONTENT']))
						{
							?><span><?=$userField['TITLE']?>: <?=$userField['CONTENT']?></span><br /><?
						}
					}
				}
				?>
			</label></li>
		<?endforeach;?>
		</ul>
        <? $first = 0; ?>
		<?foreach($arResult["STORES"] as $pid => $arProperty):?>
        <div class="ag-store-detail" <?if($arProperty["REAL_AMOUNT"]>0 && !$first):$first=1;?>style="display: block;"<?endif?> id="agst-<?= $arProperty['ID'];?>">
            <? if(
                trim($arProperty["DETAIL"]['ADDRESS'])
                || trim($arProperty["DETAIL"]['PHONE'])
                || trim($arProperty["DETAIL"]['SCHEDULE'])
                || trim($arProperty["DETAIL"]['DESCRIPTION'])
            ):?>
            <h4><a target="_blank" href="<?= $arProperty["URL"]?>"><?= $arProperty["TITLE"]?></a></h4>
            <? endif?>
            <table class="ag-store-detail-table">
                
                <?if(trim($arProperty["DETAIL"]['ADDRESS'])):?>
                <tr>
                    <th>Адрес:</th>
                    <td><?= html_entity_decode($arProperty["DETAIL"]['ADDRESS'])?></td>
                </tr>
                <?endif?>
                
                <?if(trim($arProperty["DETAIL"]['PHONE'])):?>
                <tr>
                    <th>Телефон:</th>
                    <td><?= html_entity_decode($arProperty["DETAIL"]['PHONE'])?></td>
                </tr>
                <?endif?>
                
                <?if(trim($arProperty["DETAIL"]['SCHEDULE'])):?>
                <tr>
                    <th>Режим работы:</th>
                    <td><?= html_entity_decode($arProperty["DETAIL"]['SCHEDULE'])?></td>
                </tr>
                <?endif?>

                <?if(trim($arProperty["DETAIL"]['DESCRIPTION'])):?>
                <tr>
                    <th>URL:</th>
                    <td><?= html_entity_decode($arProperty["DETAIL"]['DESCRIPTION'])?></td>
                </tr>
                <?endif?>

                
            </table>
            <? if($arProperty["ID"]==6):?>
            <div class="ag-troika-form">
                <div class="title">Введите номер карты Тройка</div>
                <input type="text" placeholder="Пример: 1234 456 789 (10 цифр)"> 
            </div>
            <? endif ?>
        </div>
		<?endforeach;?>
        
	<?endif;?>
</div>
<?if (isset($arResult["IS_SKU"]) && $arResult["IS_SKU"] == 1):?>
	<script type="text/javascript">
		var obStoreAmount = new JCCatalogStoreSKU(<? echo CUtil::PhpToJSObject($arResult['JS'], false, true, true); ?>);
	</script>
	<?
endif;?>
