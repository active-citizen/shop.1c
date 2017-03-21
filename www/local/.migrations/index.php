<? include($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php")?>
<h1>Исполнение файлов миграций</h1>
<div class="migrations-list">
    <? 
	    $sBranch = isset($_REQUEST["branch"])?$_REQUEST["branch"]:"";
        $resDir = opendir("migs/$sBranch");
        $arMigs = array();
        while($filename = readdir($resDir)){
            if($filename =='.' || $filename =='..')continue;
            if(!preg_match("#\.mig$#",$filename))continue;
            if(is_dir("migs/$filename"))continue;
            $arMigs[] = $filename;
        }
        sort($arMigs);

        $resDir = opendir("migs");
        $arBranches = array();
        while($filename = readdir($resDir)){
            if($filename =='.' || $filename =='..')continue;
            if(!is_dir("migs/$filename"))continue;
            $arBranches[] = $filename;
        }
        rsort($arBranches);
    ?>
    <div style="border-bottom: 1px #AAA solid;">
        <label><input type="checkbox" value="" 
        onclick="return mig_check_all(this.checked)"> Все</label>
	<form action=""  method="get" class="mig-branch" name="migbranches">
	    <select name="branch" onchange="document.forms.migbranches.submit();">
		    <option value="">--ветка--</option>
            <? foreach($arBranches as $sB):?>
            <option <? if($sBranch==$sB){?>selected<? }?>><?= $sB?></option>
            <? endforeach?>
	    </select>
	</form>
        <input type="button" value="Запустить" id="mig-run-button"
        onclick="runMigs();">
        <input type="button" value="Остановить" style="display:none;"
        id="mig-stop-button" onclick="stopMigs();">
    </div>
    <? foreach($arMigs as $sMigFilename):?>
    <div class="migration-filename">
        <label>
            <input type="checkbox" value="<? if($sBranch)echo "$sBranch/"?><?= $sMigFilename ?>"> 
            <?= $sMigFilename ?>
       </label>
       <span class="mig-result"></span>
     </div>
    <? endforeach?>
</div>

<script>
var runnedMigs = Array();

function runMigs(){
    document.querySelector('#mig-stop-button').style.display='inline';
    document.querySelector('#mig-run-button').style.display='none';
    var matches = Array.from(
        document.querySelectorAll('.migration-filename input')
    );
    for(i in matches)matches[i].disabled = true;
    runnedMigs = Array.from( 
        document.querySelectorAll( '.migration-filename input:checked') 
    );
    var matches1 = Array.from(
        document.querySelectorAll('.migration-filename .mig-result')
    );
    for(i in matches1)matches1[i].innerHTML = '';
    var matches2 = Array.from(
        document.querySelectorAll('.migration-filename .mig-error')
    );
    for(i in matches2)matches2[i].remove();
  
    migStep();
}

function stopMigs(){
    document.querySelector('#mig-stop-button').style.display='none';
    document.querySelector('#mig-run-button').style.display='inline';
    var matches = document.querySelectorAll('.migration-filename input');
    for(i in matches)matches[i].disabled = false;
    runnedMigs = [];
}

function migStep(){
    var nextMig = runnedMigs.shift();
    var node = nextMig.parentNode.parentNode;
    var result_selector = node.querySelector('.mig-result');
    result_selector.innerHTML = 'process..';
    result_selector.className = 'mig-result process';

    BX.ajax({
        url: '/local/.migrations/step.ajax.php',
        data:{"filename":nextMig.value},
        method: "POST",
        dataType: "text",
        timeout: 300,
        async: true,
        processData: true,
        scriptsRunFirst: true,
        emulateOnload: true,
        start: true,
        cache: false,
        onsuccess: function(data){
            if(!data){
                result_selector.innerHTML = 'Ok';
                result_selector.className = 'mig-result mig-success';
                node.querySelector('input').checked = false;
                migStep();
            }
            else{
                result_selector.innerHTML = 'Failed';
                result_selector.className = 'mig-result mig-failed';
                node.innerHTML = node.innerHTML 
                    + '<div class="mig-error">'+data+'</div>';
                node.querySelector('input').checked = true;
                stopMigs();
            }
        },
        onfailure: function(){
            stopMigs();
        }
    });
}



function mig_check_all(checked){
    var matches = document.querySelectorAll('.migration-filename input');
    for(i in matches)matches[i].checked = checked
    return true;
}
</script>

<style>
.mig-success{
    color: green;
}
.mig-failed{
    color: red;
}
.mig-error{
    border: 1px red solid;
    padding: 5px;
    color: red;
    background-color: #FEE;
    margin: 5px;
}
.mig-branch{
    display: inline;
}
</style>

<? include($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>
