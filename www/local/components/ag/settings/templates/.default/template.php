<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="partnet-settings">
    <form method="POST" class="form-horizontal" role="form">
        <? foreach($arResult["SETTINGS"] as $sCode=>$arSetting):?>
          <div class="form-group">
            <label for="input<?= $sCode?>" class="col-sm-2 control-label"><?=
            $arSetting["TITLE"]?></label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="input<?= $sCode?>"
              placeholder="<?= $arSetting["TITLE"]?>" name="KEY_<?= $sCode?>" 
              value="<?=  htmlspecialchars($arSetting["VALUE"])?>">
            </div>
          </div>        
        <? endforeach ?>
        <input type="hidden" name="CODE" value="<?= $arParams["CODE"]?>">
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button type="submit" class="btn btn-default">Сохранить</button>
            </div>
          </div>        
    </form>
</div>

<? echo $arResult["~DETAIL_TEXT"]; ?>
