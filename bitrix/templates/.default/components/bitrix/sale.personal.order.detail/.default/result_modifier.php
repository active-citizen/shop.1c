<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"]."/libs/rus.lib.php");

$cp = $this->__component;
if (is_object($cp))
{
	CModule::IncludeModule('iblock');

	if(empty($arResult['ERRORS']['FATAL']))
	{

		$hasDiscount = false;
		$hasProps = false;
		$productSum = 0;
		$basketRefs = array();

		$noPict = array(
			'SRC' => $this->GetFolder().'/images/no_photo.png'
		);

		if(is_readable($nPictFile = $_SERVER['DOCUMENT_ROOT'].$noPict['SRC']))
		{
			$noPictSize = getimagesize($nPictFile);
			$noPict['WIDTH'] = $noPictSize[0];
			$noPict['HEIGHT'] = $noPictSize[1];
		}
		if (isset($arResult["BASKET"]))
		{
			foreach ($arResult["BASKET"] as $k => &$prod)
			{
				if (floatval($prod['DISCOUNT_PRICE']))
					$hasDiscount = true;
				// move iblock props (if any) to basket props to have some kind of consistency
				if (isset($prod['IBLOCK_ID']))
				{
					$iblock = $prod['IBLOCK_ID'];
					if (isset($prod['PARENT']))
						$parentIblock = $prod['PARENT']['IBLOCK_ID'];
					foreach ($arParams['CUSTOM_SELECT_PROPS'] as $prop)
					{
						$key = $prop.'_VALUE';
						if (isset($prod[$key]))
						{
							// in the different iblocks we can have different properties under the same code
							if (isset($arResult['PROPERTY_DESCRIPTION'][$iblock][$prop]))
								$realProp = $arResult['PROPERTY_DESCRIPTION'][$iblock][$prop];
							elseif (isset($arResult['PROPERTY_DESCRIPTION'][$parentIblock][$prop]))
								$realProp = $arResult['PROPERTY_DESCRIPTION'][$parentIblock][$prop];
							if (!empty($realProp))
								$prod['PROPS'][] = array(
									'NAME' => $realProp['NAME'],
									'VALUE' => htmlspecialcharsEx($prod[$key])
								);
						}
					}
				}
				// if we have props, show "properties" column
				if (!empty($prod['PROPS']))
					$hasProps = true;
				$productSum += $prod['PRICE'] * $prod['QUANTITY'];
				$basketRefs[$prod['PRODUCT_ID']][] =& $arResult["BASKET"][$k];
				if (!isset($prod['PICTURE']))
					$prod['PICTURE'] = $noPict;
			}
		}

		$arResult['HAS_DISCOUNT'] = $hasDiscount;
		$arResult['HAS_PROPS'] = $hasProps;

		$arResult['PRODUCT_SUM_FORMATTED'] = SaleFormatCurrency($productSum, $arResult['CURRENCY']);

		if($img = intval($arResult["DELIVERY"]["STORE_LIST"][$arResult['STORE_ID']]['IMAGE_ID']))
		{

			$pict = CFile::ResizeImageGet($img, array(
				'width' => 150,
				'height' => 90
			), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);

			if(strlen($pict['src']))
				$pict = array_change_key_case($pict, CASE_UPPER);

			$arResult["DELIVERY"]["STORE_LIST"][$arResult['STORE_ID']]['IMAGE'] = $pict;
		}
        
        // Определяем для заказа возможность его отменить
        foreach($arResult["BASKET"] as $basketItem=>$product){
            $res = CIBlockElement::GetProperty($product["PARENT"]["IBLOCK_ID"],$product["PARENT"]["ID"],array(), array("CODE"=>"CANCEL_ABILITY"));
            $prop = $res->GetNext();
            $arResult["CANCEL_ABILITY"] = $prop["VALUE_ENUM"];
            if(!$prop["VALUE_ENUM"])break;
        }
	}
    
    if(preg_match("#(\d+)#",$arResult["PRICE_FORMATED"],$m)){
        $arResult["PRICE_FORMATED"] = $m[1]." ".get_points($m[1]);
    }
    if(preg_match("#(\d+)#",$arResult["PRODUCT_SUM_FORMATTED"],$m)){
        $arResult["PRODUCT_SUM_FORMATTED"] = $m[1]." ".get_points($m[1]);
    }
    if(preg_match("#(\d+)#",$arResult["PRICE_DELIVERY_FORMATED"],$m)){
        $arResult["PRICE_DELIVERY_FORMATED"] = $m[1]." ".get_points($m[1]);
    }
    
    foreach($arResult["BASKET"] as $key=>$prod){
        if(preg_match("#(\d+)#",$prod["PRICE_FORMATED"],$m)){
            $arResult["BASKET"][$key]["PRICE_FORMATED"] = $m[1]." ".get_points($m[1]);
        }
        if(preg_match("#(\d+)#",$prod["DISCOUNT_PRICE_PERCENT_FORMATED"],$m)){
            $arResult["BASKET"][$key]["DISCOUNT_PRICE_PERCENT_FORMATED"] = $m[1]." ".get_points($m[1]);
        }
        
        
        
    }
}
?>
