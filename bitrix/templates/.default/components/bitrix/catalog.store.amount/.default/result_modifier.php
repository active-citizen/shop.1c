<?
foreach($arResult["STORES"] as $store_key=>$storeProperties){
    $res = CCatalogStore::GetList(array(),array("ID"=>$storeProperties["ID"]));
    $arResult["STORES"][$store_key]["DETAIL"] = $res->GetNExt();
}

