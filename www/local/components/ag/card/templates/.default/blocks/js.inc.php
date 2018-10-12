
<script>
    // ID текущего торгового предложения
    var totalOfferId = <? 
        foreach($arResult["OFFERS"] as $arOffer)break;
        echo $arOffer["ID"];
    ?>;
    // Id текущего склада 
    var totalStoreId = <? 
        foreach($arResult["OFFERS_JSON"] as $offer){
            if(!$offer["STORAGES"])continue;
            foreach($offer["STORAGES"] as $storeId=>$store){
                echo $storeId;
                break;
            };
            break;
        }?>;
    var accountSum=<?= 
        round($arResult["ACCOUNT"]["CURRENT_BUDGET"])
    ?>;
    var offerCounts = <? 
        foreach($arResult["OFFERS"] as $nOfferId=>$arOffer)
        foreach(
            $arOffer["STORAGES"] 
            as 
            $storageId=>$storageCount
        ){
            if(!$arOffer["STORAGES"])continue;
            $arResult["OFFERS"][0]["STORAGES"];break;
        }
        echo $storageCount;
    ?>;
    var arOffers=<?=json_encode($arResult["OFFERS_JSON"])?>;
    var arStorages = <?= json_encode($arResult["STORAGES"])?>;
    var arOfferParameters = <?= json_encode($arResult["OFFER_PARAMETERS"])?>;

</script>

