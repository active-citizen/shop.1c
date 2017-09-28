<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Тэги::Кабинет партнёра");
?>
<div class="partners-main">
    <h1>Тэги</h1>
    <? include("../menu.php"); ?>
<?
    $resProduct  = CIBlockElement::GetList(
        ["SORT"=>"ASC","NAME"=>"ASC"],
        [
            "IBLOCK_ID" =>  CATALOG_IB_ID,
            "ACTIVE"    =>  "Y"
        ],false,false,
        [
            "ID","NAME","SORT"
        ]
    );
    
    $resWants = CIBlockElement::GetList(
        ["SORT"=>"ASC","NAME"=>"ASC"],
        [
            "IBLOCK_ID" =>  IWANT_IBLOCK_ID,
            "ACTIVE"    =>  "Y"
        ],false,false,
        [
            "ID","NAME","SORT"
        ]
    );
    $arWants = array();
    while($arWant = $resWants->Fetch()){
        $arWants[] = $arWant;
    }

    $resInterests = CIBlockElement::GetList(
        ["SORT"=>"ASC","NAME"=>"ASC"],
        [
            "IBLOCK_ID" =>  INTEREST_IBLOCK_ID,
            "ACTIVE"    =>  "Y"
        ],false,false,
        [
            "ID","NAME","SORT"
        ]
    );
    $arInterests = array();
    while($arInterest = $resInterests->Fetch()){
        $arInterests[] = $arInterest;
    }

?>
<table class="table table-striped table-hover table-bordered">
    <tr>
        <th rowspan="3">
            Поощрение
        </th>
    </tr>
    <tr>
        <? foreach($arWants as $sWantId=>$arWant):?>
        <th class="iwant">
            <?= $arWant["NAME"]?>
        </th>
        <? endforeach ?>
    </tr>
    <tr>
        <? foreach($arInterests as $sInterestId=>$arInterest):?>
        <th class="interests">
            <?= $arInterest["NAME"]?>
        </th>
        <? endforeach ?>
    </tr>
    <? while($arProduct=$resProduct->Fetch()):?>
    <? 
        $resProps = CIBlockElement::GetProperty(
            CATALOG_IB_ID,
            $arProduct["ID"],
            array(),
            array("ID"=>IWANT_PROPERTY_ID)
        );
        $arPropsWants = array();
        while($arProp = $resProps->Fetch())
            if(trim($arProp["VALUE"]))
                $arPropsWants[] = $arProp["VALUE"];
        
        $resProps = CIBlockElement::GetProperty(
            CATALOG_IB_ID,
            $arProduct["ID"],
            array(),
            array("ID"=>INTEREST_PROPERTY_ID)
        );
        $arPropsInterests = array();
        while($arProp = $resProps->Fetch())
            if(trim($arProp["VALUE"]))
                $arPropsInterests[] = $arProp["VALUE"];
        
    ?>
    <tr
        <? if(
            !count($arPropsInterests)
            &&
            count($arPropsWants)
        ):?>
        class="interests"
        <? elseif(
            !count($arPropsWants)
            &&
            count($arPropsInterests)
        ):?>
        class="wants"
        <? elseif(
            !count($arPropsWants)
            &&
            !count($arPropsInterests)
        ):?>
        class="tag-alarm"
        <? endif ?>
    >
        <td>
            <?= $arProduct["NAME"];?>
            <? foreach($arProps as $nPropId):?>
            <?= $arWants[$nPropId]["NAME"]?><br/>
            </div>
            <? endforeach ?>
        </td>
        <? for($i=0;$i<count($arWants) || $i<count($arInterests);$i++):?>
        <td>
            <? if(array_search($arWants[$i]["ID"],$arPropsWants)!==false):?>
            <div class="iwant">
            <?= $arWants[$i]["NAME"]?>
            </div>
            <? endif ?>
            <? if(array_search($arInterests[$i]["ID"],$arPropsInterests)!==false):?>
            <div class="interests">
            <?= $arInterests[$i]["NAME"]?>
            </div>
            <? endif ?>
        </td>
        <? endfor ?>
    </tr>
    <? endwhile ?>
</table>
</div>

<style>
.iwant{
    background-color: rgba(0,122,108,1) !important;
}
.interests{
    background-color: #f49541 !important;
}

div.iwant, div.interests{
    text-align: center;
    padding: 3px;
    border-radius: 5px;
}

div.iwant{
    color: white;
}

th.wants{
    width: 50px;
}

th.interests{
    width: 50px;
}

.tag-alarm{
    background-color: red !important;
}

</style>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
