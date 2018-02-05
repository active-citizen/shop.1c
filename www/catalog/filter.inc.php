<? 
/*
    // Определяем ID раздела  
    $sCode = '';
    if(preg_match("#/catalog/(.*?)/.*#",$_SERVER["REQUEST_URI"],$m))
        $sCode = $m[1];

    if($sCode)
    $arSection = CIBlockSection::GetList(
        array(),
        array(
            "IBLOCK_ID"=>CATALOG_IB_ID,
            "CODE"=>$sCode
        ),
        false,
        array("ID"),
        array("nTopCount"=>1)
    )->Fetch();
*/
?>

<?

    $APPLICATION->IncludeComponent("ag:filter","",array(
    "SECTION_ID"=>$arSection["ID"],
    "CACHE_TIME"=>1//COMMON_CACHE_TIME
),false);?> 


