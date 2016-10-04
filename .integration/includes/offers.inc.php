<?php

/*

Трудности, с которыми столкнулся при импорте каталога. Их оказалось больше, чем 
предполагалось.

В целом импорт работает, но, видимо, надо в 1С сделать небольшие модификации-наполнения, 
чтобы убедиться в полной корректности импорта всех полей.
Решил не дёргать по каждому, а собрать в процессе обнаружения в кучу.

1) Ни у одного товара не заполнены поля "хочу", "интересуюсь". Их импорт тоже 
требуется отработать. 
2) У товара в XML нет поля "типы поощрений" (зарядись, отдохни, узнай, попробуй).
Их бы тоже надо завести и наполнить
3) Поле "время, которое доступен выданный купон", есть "СрокИсполнения", но это, 
мне нкажется, не он. К тому же он везде равен 0.
4) Фотографии для конкретных товарных пердложений. Фотографии есть только для 
общей информации о товарах (import.xml), однако логика магазина подразумевает 
возможность фотографий для отдельных предложений со своими характеристиками, 
например цветом.
5) Артикул. В XML такое поле есть, но оно всюду пустое.
6) "Лидер продаж", "Новинка", "Спецпредложение".  В XML этих полей нет, ну и 
соответственно нет их наполнения.


Теперь по поводу обмена заказами.

Если я правильно понял Владимира, узнать параметры http-запроса, по которым 1C
обращается к сайту не так просто. Всё что пока получилось 
[10:38:48] kvl0209: при загрузке заказов с сайта 1с пытается прочтитать файл
[10:38:50] kvl0209: C:\Users\kuznecovvl\AppData\Local\Temp\1\v8_B9E7_f.tmp

видимо потребуется вживлять в скрипт обмена логгер и писать целиком процесс обмена
(GET, POST, FILES).

21.09.2016 Импорт товаров 1С->Bitrix 
23.09.2016 Импорт заказов 1С->Битрикс 
23.09.2016 Экспорт заказов Битрикс->1С
22.09.2016 Получение сессии из АГ. Муляж.
30.09.2016 Получение сессии с АГ. Боевой.
27.09.2016 Отсылка купона заказа
27.09.2016 Разворачивание БД на is45-ag-1c-pg
30.09.2016 Прикручивание дизайна и верстки
28.09.2016 Импорт баллов и профиля
29.09.2016 Заказ товаров
03.10.2016 Перенос функционала на продакшн. И тестирование


 */

    ///////////////////////////////////////////////////////////////////////
    ///                  Импортируем торговые предложения
    ///////////////////////////////////////////////////////////////////////
    $objPrice = new CPrice;
    $objPrice = new CPrice;
    $objOffer = new CIBlockElement;
    $resCatalogStoreProduct = new CCatalogStoreProduct;
    // Перебираем товарные предложения
    echo "<pre>";
    print_r($arOffers);
    die;
    foreach($arOffers as $arOffer){
        // Если склад еданственный
        if(isset($arOffer["Склад"]) && !isset($arOffer["Склад"][0]))
            $arOffer["Склад"] = array($arOffer["Склад"]);
            
        $offerFields = array(
            "IBLOCK_ID"         =>  3,
            "NAME"              =>  $arOffer["Наименование"],
            "PRICE"             =>  $productsIndexDetail[$arOffer["Ид"]]["Баллы"],
            "XML_ID"            =>  $arOffer["Ид"]
//                "DETAIL_TEXT"       =>  $product["DETAIL_TEXT"],
//                "PREVIEW_TEXT"      =>  $product["PREVIEW_TEXT"],
//                "PREVIEW_TEXT_TYPE" =>  $product["PREVIEW_TEXT_TYPE"],
//                "PREVIEW_PICTURE"   =>  (
//                "DETAIL_PICTURE"    =>  (
        );
        
        // Ищем в товарных предложениях с указанным XML_ID
        $res = CIBlockElement::GetList(array(),array("IBLOCK_ID"=>3,"XML_ID"=>$arOffer["Ид"]));
        // Если предложения нет - добавляем
        if(!$existsOffer = $res->GetNext()){
            // Добавляем предложение
            $offerId = $objOffer->Add($offerFields);
            // Добавляем продукт
            CCatalogProduct::Add(array("ID"=>$offerId,"QUANTITY"=>$arOffer["Количество"],"QUANTITY_TRACE"=>"Y","CAN_BUY_ZERO"=>"N"));
            // Добавляем цену
            $priceId = $objPrice->Add(
                array("PRODUCT_ID"=>$offerId,"CATALOG_GROUP_ID"=>1,"PRICE"=>$productsIndexDetail[$arOffer["Ид"]]["Баллы"],"CURRENCY"=>"BAL"),
                true
            );
            // Добавляем наличие на складах
            if(isset($arOffer["Склад"]) && is_array($arOffer["Склад"]))
                foreach($arOffer["Склад"] as $storage){
                    if(!$resCatalogStoreProduct->Add($arFields = array(
                        "PRODUCT_ID"=>  $offerId,
                        "STORE_ID"=>    $arStoragesIndex[$storage["@attributes"]["ИдСклада"]],
                        "AMOUNT"=>      $storage["@attributes"]["КоличествоНаСкладе"]
                    ))){
                        echo "<pre>";
                        print_r($resCatalogStoreProduct);
                        die;
                    }
                }
        }
        // Если предложения есть - обновляем
        else{
            $offerId = $existsOffer["ID"];
            $objOffer->Update($existsOffer["ID"], $offerFields);
            CCatalogProduct::Update($offerId, array("QUANTITY"=>$arOffer["Количество"],"QUANTITY_TRACE"=>"Y","CAN_BUY_ZERO"=>"N",));
            
            
            $res = CPrice::GetList(array(),array("PRODUCT_ID"=>$offerId));
            if(!$existsPrice = $res->GetNext()){
                $priceId = $objPrice->Add(
                    array(
                        "PRODUCT_ID"=>$offerId,
                        "CATALOG_GROUP_ID"=>1,
                        "PRICE"=>$productsIndexDetail[$arOffer["Ид"]]["Баллы"],
                        "CURRENCY"=>"BAL",
                    ),
                    true
                );
            }
            else{
                $priceId = $existsPrice["ID"];
                $objPrice->Update(
                    $priceId,
                    array(
                        "PRODUCT_ID"=>$offerId,
                        "CATALOG_GROUP_ID"=>1,
                        "PRICE"=>$productsIndexDetail[$arOffer["Ид"]]["Баллы"],
                        "CURRENCY"=>"BAL",
                    ),
                    true
                );
            }
            
            // Обнуляем остатки на складах
            $res = CCatalogStoreProduct::GetList(array(),array("PRODUCT_ID"=>$offerId));
            while($store = $res->GetNext())
                $resCatalogStoreProduct->Update($store["ID"],array("AMOUNT"=>0));
                
            // Прописываем новые остатки
            if(isset($arOffer["Склад"]) && is_array($arOffer["Склад"]))
                foreach($arOffer["Склад"] as $storage){
                    $res = CCatalogStoreProduct::GetList(array(),array(
                        "PRODUCT_ID"=>$offerId,
                        "STORE_ID"  =>$arStoragesIndex[$storage["@attributes"]["ИдСклада"]]
                    ));
                    // Если запись для товара на складе есть - обновляем
                    // Иначе добавляем
                    if(!$existsRest = $res->GetNext()){
                        $resCatalogStoreProduct->Add(array(
                            "PRODUCT_ID"=>$offerId,
                            "STORE_ID"  =>$arStoragesIndex[$storage["@attributes"]["ИдСклада"]],
                            "AMOUNT"=>$storage["@attributes"]["КоличествоНаСкладе"]
                        ));
                    }
                    else{
                        $resCatalogStoreProduct->Update($existsRest,
                            array("AMOUNT"=>$storage["@attributes"]["КоличествоНаСкладе"])
                        );
                    }
                }
            
            
        }
        
        
        $arOffer["Ид"] = explode("#",$arOffer["Ид"]);
        $arOffer["Ид"] = $arOffer["Ид"][0];
        CIBlockElement::SetPropertyValueCode(
            $offerId, "CML2_LINK", $productsIndex[$arOffer["Ид"]]
        );
        
        echo "<pre>";
        print_r($offerFields);
        echo "</pre>";
    }
