
<script>
    // ID текущего торгового предложения
    var totalOfferId = <?= $arResult["OFFERS"][0]["ID"]?>;
    // Id текущего склада 
    var totalStoreId = <? 
        foreach($arResult["OFFERS_JSON"] as $offer){
            foreach($offer["STORAGES"] as $storeId=>$store){
                echo $storeId;break;
            };
            break;
        }?>;
    var accountSum=<?= 
        round($arResult["ACCOUNT"]["CURRENT_BUDGET"])
    ?>;
    var offerCounts = <? 
        foreach(
            $arResult["OFFERS"][0]["STORAGES"] 
            as 
            $storageId=>$storageCount
        ){
            $arResult["OFFERS"][0]["STORAGES"];break;
        }
        echo $storageCount;
    ?>;
    var arOffers=<?=json_encode($arResult["OFFERS_JSON"])?>;
    var arStorages = <?= json_encode($arResult["STORAGES"])?>;
    var arOfferParameters = <?= json_encode($arResult["OFFER_PARAMETERS"])?>

</script>

