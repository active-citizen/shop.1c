<? include($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php")?>
<h1>Исполнение файлов миграций</h1>
<div class="migrations-list">
    <? 
        $resDir = opendir("migs");
        $arMigs = array();
        while($filename = readdir($resDir)){
            if($filename =='.' || $filename =='..')continue;
            $arMigs[] = $filename;
        }
        sort($arMigs);
    ?>
    <div style="border-bottom: 1px #AAA solid;">
        <label><input type="checkbox" value="" onclick="return mig_check_all(this.checked)"> Все</label>
    </div>
    <? foreach($arMigs as $sMigFilename):?>
    <div class="migration-filename">
        <label><input type="checkbox" value="<?= $sMigFilename ?>"> <?= $sMigFilename ?></label>
    </div>
    <? endforeach?>
</div>

<script>
function mig_check_all(checked){
    var matches = document.querySelectorAll('.migration-filename input');
    for(i in matches)matches[i].checked = checked
    return true;
}
</script>

<? include($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>
