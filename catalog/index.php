<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?$APPLICATION->IncludeComponent("bitrix:catalog", "ag", Array(
	"IBLOCK_TYPE" => "catalog",	// Тип инфоблока
		"IBLOCK_ID" => "2",	// Инфоблок
		"TEMPLATE_THEME" => "site",	// Цветовая тема
		"HIDE_NOT_AVAILABLE" => "N",	// Товары, которых нет на складах
		"BASKET_URL" => "/personal/cart/",	// URL, ведущий на страницу с корзиной покупателя
		"ACTION_VARIABLE" => "action",	// Название переменной, в которой передается действие
		"PRODUCT_ID_VARIABLE" => "id",	// Название переменной, в которой передается код товара для покупки
		"SECTION_ID_VARIABLE" => "SECTION_ID",	// Название переменной, в которой передается код группы
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",	// Название переменной, в которой передается количество товара
		"PRODUCT_PROPS_VARIABLE" => "prop",	// Название переменной, в которой передаются характеристики товара
		"SEF_MODE" => "Y",	// Включить поддержку ЧПУ
		"SEF_FOLDER" => "/catalog/",	// Каталог ЧПУ (относительно корня сайта)
		"AJAX_MODE" => "N",	// Включить режим AJAX
		"AJAX_OPTION_JUMP" => "N",	// Включить прокрутку к началу компонента
		"AJAX_OPTION_STYLE" => "Y",	// Включить подгрузку стилей
		"AJAX_OPTION_HISTORY" => "N",	// Включить эмуляцию навигации браузера
		"CACHE_TYPE" => "A",	// Тип кеширования
		"CACHE_TIME" => "36000000",	// Время кеширования (сек.)
		"CACHE_FILTER" => "Y",	// Кешировать при установленном фильтре
		"CACHE_GROUPS" => "Y",	// Учитывать права доступа
		"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
		"ADD_SECTION_CHAIN" => "Y",
		"ADD_ELEMENT_CHAIN" => "Y",	// Включать название элемента в цепочку навигации
		"SET_STATUS_404" => "Y",	// Устанавливать статус 404
		"DETAIL_DISPLAY_NAME" => "N",	// Выводить название элемента
		"USE_ELEMENT_COUNTER" => "Y",	// Использовать счетчик просмотров
		"USE_FILTER" => "Y",	// Показывать фильтр
		"FILTER_NAME" => "",	// Фильтр
		"FILTER_VIEW_MODE" => "VERTICAL",	// Вид отображения умного фильтра
		"FILTER_FIELD_CODE" => array(	// Поля
			0 => "",
			1 => "",
		),
		"FILTER_PROPERTY_CODE" => array(	// Свойства
			0 => "",
			1 => "",
		),
		"FILTER_PRICE_CODE" => array(	// Тип цены
			0 => "BASE",
		),
		"FILTER_OFFERS_FIELD_CODE" => array(	// Поля предложений
			0 => "PREVIEW_PICTURE",
			1 => "DETAIL_PICTURE",
			2 => "",
		),
		"FILTER_OFFERS_PROPERTY_CODE" => array(	// Свойства предложений
			0 => "",
			1 => "",
		),
		"USE_REVIEW" => "Y",	// Разрешить отзывы
		"MESSAGES_PER_PAGE" => "10",	// Количество сообщений на одной странице
		"USE_CAPTCHA" => "Y",	// Использовать CAPTCHA
		"REVIEW_AJAX_POST" => "Y",	// Использовать AJAX в диалогах
		"PATH_TO_SMILE" => "/bitrix/images/forum/smile/",	// Путь относительно корня сайта к папке со смайлами
		"FORUM_ID" => "11",	// ID форума для отзывов
		"URL_TEMPLATES_READ" => "",	// Страница чтения темы (пусто - получить из настроек форума)
		"SHOW_LINK_TO_FORUM" => "Y",	// Показать ссылку на форум
		"USE_COMPARE" => "N",	// Разрешить сравнение товаров
		"PRICE_CODE" => array(	// Тип цены
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "N",	// Использовать вывод цен с диапазонами
		"SHOW_PRICE_COUNT" => "1",	// Выводить цены для количества
		"PRICE_VAT_INCLUDE" => "Y",	// Включать НДС в цену
		"PRICE_VAT_SHOW_VALUE" => "N",	// Отображать значение НДС
		"PRODUCT_PROPERTIES" => "",	// Характеристики товара, добавляемые в корзину
		"USE_PRODUCT_QUANTITY" => "Y",	// Разрешить указание количества товара
		"CONVERT_CURRENCY" => "N",	// Показывать цены в одной валюте
		"QUANTITY_FLOAT" => "N",
		"OFFERS_CART_PROPERTIES" => array(	// Свойства предложений, добавляемые в корзину
			0 => "SIZES_SHOES",
			1 => "SIZES_CLOTHES",
			2 => "COLOR_REF",
		),
		"SHOW_TOP_ELEMENTS" => "N",	// Выводить топ элементов
		"SECTION_COUNT_ELEMENTS" => "N",	// Показывать количество элементов в разделе
		"SECTION_TOP_DEPTH" => "1",	// Максимальная отображаемая глубина разделов
		"SECTIONS_VIEW_MODE" => "TILE",	// Вид списка подразделов
		"SECTIONS_SHOW_PARENT_NAME" => "N",	// Показывать название раздела
		"PAGE_ELEMENT_COUNT" => "15",	// Количество элементов на странице
		"LINE_ELEMENT_COUNT" => "3",	// Количество элементов, выводимых в одной строке таблицы
		"ELEMENT_SORT_FIELD" => "desc",	// По какому полю сортируем товары в разделе
		"ELEMENT_SORT_ORDER" => "asc",	// Порядок сортировки товаров в разделе
		"ELEMENT_SORT_FIELD2" => "id",	// Поле для второй сортировки товаров в разделе
		"ELEMENT_SORT_ORDER2" => "desc",	// Порядок второй сортировки товаров в разделе
		"LIST_PROPERTY_CODE" => array(	// Свойства
			0 => "NEWPRODUCT",
			1 => "SALELEADER",
			2 => "SPECIALOFFER",
			3 => "",
		),
		"INCLUDE_SUBSECTIONS" => "Y",	// Показывать элементы подразделов раздела
		"LIST_META_KEYWORDS" => "UF_KEYWORDS",	// Установить ключевые слова страницы из свойства раздела
		"LIST_META_DESCRIPTION" => "UF_META_DESCRIPTION",	// Установить описание страницы из свойства раздела
		"LIST_BROWSER_TITLE" => "UF_BROWSER_TITLE",	// Установить заголовок окна браузера из свойства раздела
		"LIST_OFFERS_FIELD_CODE" => array(	// Поля предложений
			0 => "NAME",
			1 => "PREVIEW_PICTURE",
			2 => "DETAIL_PICTURE",
			3 => "",
		),
		"LIST_OFFERS_PROPERTY_CODE" => array(	// Свойства предложений
			0 => "SIZES_SHOES",
			1 => "SIZES_CLOTHES",
			2 => "COLOR_REF",
			3 => "MORE_PHOTO",
			4 => "ARTNUMBER",
			5 => "",
		),
		"LIST_OFFERS_LIMIT" => "0",	// Максимальное количество предложений для показа (0 - все)
		"SECTION_BACKGROUND_IMAGE" => "UF_BACKGROUND_IMAGE",	// Установить фоновую картинку для шаблона из свойства
		"DETAIL_PROPERTY_CODE" => array(	// Свойства
			0 => "NEWPRODUCT",
			1 => "MANUFACTURER",
			2 => "MATERIAL",
		),
		"DETAIL_META_KEYWORDS" => "KEYWORDS",	// Установить ключевые слова страницы из свойства
		"DETAIL_META_DESCRIPTION" => "META_DESCRIPTION",	// Установить описание страницы из свойства
		"DETAIL_BROWSER_TITLE" => "TITLE",	// Установить заголовок окна браузера из свойства
		"DETAIL_OFFERS_FIELD_CODE" => array(	// Поля предложений
			0 => "NAME",
			1 => "",
		),
		"DETAIL_OFFERS_PROPERTY_CODE" => array(	// Свойства предложений
			0 => "ARTNUMBER",
			1 => "SIZES_SHOES",
			2 => "SIZES_CLOTHES",
			3 => "COLOR_REF",
			4 => "MORE_PHOTO",
			5 => "",
		),
		"DETAIL_BACKGROUND_IMAGE" => "BACKGROUND_IMAGE",	// Установить фоновую картинку для шаблона из свойства
		"LINK_IBLOCK_TYPE" => "",	// Тип инфоблока, элементы которого связаны с текущим элементом
		"LINK_IBLOCK_ID" => "",	// ID инфоблока, элементы которого связаны с текущим элементом
		"LINK_PROPERTY_SID" => "",	// Свойство, в котором хранится связь
		"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",	// URL на страницу, где будет показан список связанных элементов
		"USE_ALSO_BUY" => "Y",	// Показывать блок "С этим товаром покупают"
		"ALSO_BUY_ELEMENT_COUNT" => "4",	// Количество элементов для отображения
		"ALSO_BUY_MIN_BUYES" => "1",	// Минимальное количество покупок товара
		"OFFERS_SORT_FIELD" => "sort",	// По какому полю сортируем предложения товара
		"OFFERS_SORT_ORDER" => "desc",	// Порядок сортировки предложений товара
		"OFFERS_SORT_FIELD2" => "id",	// Поле для второй сортировки предложений товара
		"OFFERS_SORT_ORDER2" => "desc",	// Порядок второй сортировки предложений товара
		"PAGER_TEMPLATE" => "round",	// Шаблон постраничной навигации
		"DISPLAY_TOP_PAGER" => "N",	// Выводить над списком
		"DISPLAY_BOTTOM_PAGER" => "Y",	// Выводить под списком
		"PAGER_TITLE" => "Товары",	// Название категорий
		"PAGER_SHOW_ALWAYS" => "N",	// Выводить всегда
		"PAGER_DESC_NUMBERING" => "N",	// Использовать обратную навигацию
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",	// Время кеширования страниц для обратной навигации
		"PAGER_SHOW_ALL" => "N",	// Показывать ссылку "Все"
		"ADD_PICT_PROP" => "MORE_PHOTO",	// Дополнительная картинка основного товара
		"LABEL_PROP" => "NEWPRODUCT",	// Свойство меток товара
		"PRODUCT_DISPLAY_MODE" => "Y",	// Схема отображения
		"OFFER_ADD_PICT_PROP" => "MORE_PHOTO",	// Дополнительные картинки предложения
		"OFFER_TREE_PROPS" => array(	// Свойства для отбора предложений
			0 => "SIZES_SHOES",
			1 => "SIZES_CLOTHES",
			2 => "COLOR_REF",
			3 => "",
		),
		"SHOW_DISCOUNT_PERCENT" => "Y",	// Показывать процент скидки
		"SHOW_OLD_PRICE" => "Y",	// Показывать старую цену
		"MESS_BTN_BUY" => "Купить",	// Текст кнопки "Купить"
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",	// Текст кнопки "Добавить в корзину"
		"MESS_BTN_COMPARE" => "Сравнение",	// Текст кнопки "Сравнение"
		"MESS_BTN_DETAIL" => "Подробнее",	// Текст кнопки "Подробнее"
		"MESS_NOT_AVAILABLE" => "Нет в наличии",	// Сообщение об отсутствии товара
		"DETAIL_USE_VOTE_RATING" => "Y",	// Включить рейтинг товара
		"DETAIL_VOTE_DISPLAY_AS_RATING" => "rating",	// В качестве рейтинга показывать
		"DETAIL_USE_COMMENTS" => "Y",	// Включить отзывы о товаре
		"DETAIL_BLOG_USE" => "Y",	// Использовать комментарии
		"DETAIL_VK_USE" => "N",	// Использовать Вконтакте
		"DETAIL_FB_USE" => "Y",	// Использовать Facebook
		"AJAX_OPTION_ADDITIONAL" => "",	// Дополнительный идентификатор
		"USE_STORE" => "Y",	// Показывать блок "Количество товара на складе"
		"FIELDS" => array(	// Поля
			0 => "STORE",
			1 => "SCHEDULE",
		),
		"USE_MIN_AMOUNT" => "N",	// Показывать вместо точного количества информацию о достаточности
		"STORE_PATH" => "/store/#store_id#",	// Шаблон пути к каталогу STORE (относительно корня)
		"MAIN_TITLE" => "Наличие на складах",	// Заголовок блока
		"MIN_AMOUNT" => "10",
		"DETAIL_BRAND_USE" => "Y",	// Использовать компонент "Бренды"
		"DETAIL_BRAND_PROP_CODE" => "BRAND_REF",	// Таблица с брендами
		"SIDEBAR_SECTION_SHOW" => "Y",	// Показывать правый блок в списке товаров
		"SIDEBAR_DETAIL_SHOW" => "Y",	// Показывать правый блок на детальной странице
		"SIDEBAR_PATH" => "/catalog/sidebar.php",	// Путь к включаемой области для вывода информации в правом блоке
		"SEF_URL_TEMPLATES" => array(
			"sections" => "",
			"section" => "#SECTION_CODE#/",
			"element" => "#SECTION_CODE#/#ELEMENT_CODE#/",
			"compare" => "compare/",
		)
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>