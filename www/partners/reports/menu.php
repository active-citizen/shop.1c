<? 
    $arTypes = array(
        "stores"=>"Складские остатки",
        "tags"  =>"Тэги",
    );
?>

<select class="form-control"
onchange="document.location.href=this.options[this.selectedIndex].value;">
    <option value="">-нет-</option>
    <? foreach($arTypes as $sType=>$sName):?>
    <option value="?type=<?= $sType?>"<? 
        if($sType==$_REQUEST["type"])echo "selected";?>><?= $sName?></option>
    <? endforeach ?>
</select>
