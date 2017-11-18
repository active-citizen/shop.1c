<?php
    require_once(realpath(__DIR__."/..")."/CSearch.class.php");
    require_once(realpath(__DIR__."/..")."/CSearchDocument.class.php");
    require_once(realpath(__DIR__."/..")."/CSearchDocumentOption.class.php");
    use AGPhop\Search as Search;
    
    class agshopSearchTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }


        function testIndex(){
            $objCSearchdDocument = new \Search\CSearchDocument;
            $objCSearchdDocumentType = new \Search\CSearchDocumentType;
            $objCSearch = new \Search\CSearch;
            
            // Берём документ, исходя из минимального времени переиндексирования
            $this->assertTrue(boolval(
                $nDocId = $objCSearchdDocumentType->getNextDocument(
                    'PRODUCT',100000
                )
            ));
            
            // Извлекаем опции документа
            $objCSearchDocumentOption = new \Search\CSearchDocumentOption;
            $arOptions = $objCSearchDocumentOption->fetch($nDocId, 'PRODUCT');
            $this->assertTrue(isset($arOptions["SECTION_ID"]));
            $this->assertTrue(isset($arOptions["INTEREST_ID"]));
            
            // Тестируем выдачу списка типов опций
            $this->assertTrue(boolval(
                count($arOptsTypes = $objCSearchDocumentOption->getTypes())
            ));
            $this->assertTrue(
                isset($arOptsTypes["SECTION_ID"]) 
                && intval($arOptsTypes["SECTION_ID"])
            );
            $this->assertTrue(
                isset($arOptsTypes["INTEREST_ID"]) 
                && intval($arOptsTypes["INTEREST_ID"])
            );
            $this->assertTrue(
                isset($arOptsTypes["AT_STORAGE"]) 
                && intval($arOptsTypes["AT_STORAGE"])
            );
            $this->assertTrue(
                isset($arOptsTypes["WHISHES"]) 
                && intval($arOptsTypes["WHISHES"])
            );
            
            // Сохраняем опции документа
            $objCSearchDocumentOption->save($nDocId, 'PRODUCT', $arOptions);
            
            // Получаем опции документа
            $arOptions = $objCSearchDocumentOption->get($nDocId, 'PRODUCT');

            // Удаляем опции документа
            $objCSearchDocumentOption->delete($nDocId, 'PRODUCT');
            // Получаем опции документа
            $arOptions = $objCSearchDocumentOption->get($nDocId, 'PRODUCT');
            $this->assertFalse(boolval(isset($arOptions[0])));
            
            
            // Получаем контент, который надо проиндексировать
            $this->assertTrue(boolval(
                $nDocContent = $objCSearchdDocumentType->getSearchableContent(
                    $nDocId, 'PRODUCT'
                )
            ));

            // Получаем число непроиндексированных документов
            
            $nDocsCount = $objCSearchdDocumentType->getUnindexedDocuments(
                'PRODUCT', 300
            );
            $this->assertTrue(boolval($nDocsCount));
            $nDocsCount = $nDocsCount>10?10:$nDocsCount;

            // Индексируем документ, исходя из минимального времени переиндексирования
            for($i=1;$i<$nDocsCount;$i++)
            $this->assertTrue(boolval(
                $objCSearch->indexNextDocument('PRODUCT', 300)
            ),print_r($objCSearch, 1));
            // Индексируем документ, исходя из минимального времени переиндексирования
            for($i=1;$i<$nDocsCount;$i++)
            $this->assertTrue(boolval(
                $objCSearch->indexNextDocument('PRODUCT', 300)
            ),print_r($objCSearch, 1));
            
        }
        
        function testResult(){
            $objCSearch = new \Search\CSearch;
            
            $sPhrase01 = 'Билеты';
            
            // Проверяем что получили какие-то результат
            $arResult01 = $objCSearch->results($sPhrase01,["LIMIT"=>100,"PAGE"=>1]);
            $this->assertTrue(boolval(
                count($arResult01)
            ));
            
            // Берём ID раздела для следующего запроса
            foreach($arResult01 as $arResult){
                $nSectionId = $arResult["OPTIONS"]['SECTION_ID'][0];
                break;
            }

            // Запрашиваем с опциями раздела
            $arResult01 = $objCSearch->results($sPhrase01,[
                "LIMIT" =>  100,
                "PAGE"  =>  1,
                "FILTER"    =>  [
                    "SECTION_ID" => $nSectionId
                ]
            ]);
            
            // Проверяем, что в выдачу попали только результаты для этого раздела
            $bOnlySection = true;
            foreach($arResult01 as $arResult)
                if(
                    !isset($arResult["OPTIONS"]["SECTION_ID"][0])
                    || $arResult["OPTIONS"]["SECTION_ID"][0]!=$nSectionId
                ){
                    $bOnlySection = false;
                    break;
                }
            $this->assertTrue($bOnlySection);
            

            // Берём ID склада для следующего запроса
            foreach($arResult01 as $arResult){
                $nStorageId = $arResult["OPTIONS"]['AT_STORAGE'][0];
                break;
            }


            // Запрашиваем с опциями раздела и склада
            $arResult01 = $objCSearch->results($sPhrase01,[
                "LIMIT" =>  100,
                "PAGE"  =>  1,
                "FILTER"    =>  [
                    "SECTION_ID"    =>  $nSectionId,
                    "AT_STORAGE"    =>  $nStorageId
                ]
            ]);
            
            // Проверяем, что в каждом есть товар с этого склада и этого раздела
            $bOnlySection = true;
            
            foreach($arResult01 as $arResult){
                if(
                    !isset($arResult["OPTIONS"]["SECTION_ID"][0])
                    || $arResult["OPTIONS"]["SECTION_ID"][0]!=$nSectionId
                ){
                    $bOnlySection = false;
                    break;
                }
                
                if(!isset($arResult["OPTIONS"]["AT_STORAGE"][0])){
                    $bStoreExists = false;
                    break;
                }
                
                $bStoreExists = false;
                foreach($arResult["OPTIONS"]["AT_STORAGE"] as $nStore)
                    if($nStore==$nStorageId){
                        $bStoreExists = true;
                        break;
                    }
                if(!$bStoreExists)break;
            }
            $this->assertTrue($bOnlySection);
            $this->assertTrue($bStoreExists, print_r($arResult, 1));
        }
        
  
    }
