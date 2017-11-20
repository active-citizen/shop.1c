<?
    require(
        $_SERVER["DOCUMENT_ROOT"]
        ."/bitrix/modules/main/include/prolog_before.php"
    );

    require_once(
        $_SERVER["DOCUMENT_ROOT"]
        ."/local/libs/classes/CAGShop/CSearch/CSearchPhrase.class.php"
    );
    use AGPhop\Search as Search;

    $objCSearchPhrase = new \Search\CSearchPhrase;
    $arPhrases = $objCSearchPhrase->get($_REQUEST['term']);
    
    $arResult = [];
    foreach($arPhrases as $arPhrase)$arResult[] = $arPhrase['phrase'];
    
    echo json_encode($arResult);
    
    
