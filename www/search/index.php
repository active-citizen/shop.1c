<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поиск");
//require($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CSearch/CSearch.class.php");
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CSection/CSection.class.php");
use AGShop\Section as Section;

$objSections = new \Section\CSection;

$arSections = $objSections->get();
?>
<div class="search">
    <div class="search-option">
        <select id="">
            <option value="">-Все-</option>
            <? foreach($arSections as $arSection):?>
            <option value="<?= $arSection["ID"]?>">
                <?= $arSection["NAME"]?>
            </option>
            <? endforeach ?>
        </select>
    </div>
</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
