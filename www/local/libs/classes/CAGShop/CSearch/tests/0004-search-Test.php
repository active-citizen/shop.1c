<?php
    require_once(realpath(__DIR__."/..")."/CSearch.class.php");
    require_once(realpath(__DIR__."/..")."/CSearchDocument.class.php");
    require_once(realpath(__DIR__."/..")."/CSearchDocumentOption.class.php");
    use AGPhop\Search as Search;
    
    class agshopSearchTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            Тест поиска
        */
        function testGetDocsIndex(){
            $objCSearch = new \Search\CSearch;
            $objCSearchDocument = new \Search\CSearchDocument;
            // Индексируем документ
            $arDocumentEntries = $objCSearchDocument->index(file_get_contents(
                realpath(__DIR__."/..")."/data/document.txt"
            ));
            
            // Проверяем как проиндексировалось (количество слов)
            $this->assertEquals($arDocumentEntries,82);
            
            $sSearchPhrase = "стРуктуры из Версий Это по 1";
            // Проверяем как бьётся фраза на слова
            $arParsedPhrases = $objCSearchDocument->parse($sSearchPhrase);
            $this->assertEquals(count($arParsedPhrases),2);
            $this->assertEquals(
                $arParsedPhrases["СТРУКТУРЫ"]['baseform']['word'],
                "СТРУКТУРА"
            );
            $this->assertEquals(
                $arParsedPhrases["ВЕРСИЙ"]['baseform']['word'],
                "ВЕРСИЯ"
            );
            
            // Ищем в нём по ключевику ID документов
            $arSearch = $objCSearch->getDocsIndex($sSearchPhrase);
            $this->assertTrue(boolval($arSearch));
            $this->assertArrayHasKey(0,$arSearch);
            $this->assertArrayHasKey("doc_id",$arSearch[0]);
            $this->assertTrue(boolval($arSearch[0]["doc_id"]));
            
            // Удаляем документ из индекса
            $objCSearchDocument->delete();
            
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
            
            // Получаем опции документа
            $objCSearchDocumentOption = new \Search\CSearchDocumentOption;
            $arOptions = $objCSearchDocumentOption->fetch($nDocId, 'PRODUCT');
            $this->assertTrue(isset($arOptions["SECTION_ID"]));
            $this->assertTrue(isset($arOptions["INTEREST_ID"]));
            
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
            $nDocsCount = $nDocsCount>20?20:$nDocsCount;

            // Индексируем документ, исходя из минимального времени переиндексирования
            for($i=1;$i<$nDocsCount;$i++)
            $this->assertTrue(boolval(
                $objCSearch->indexNextDocument('PRODUCT', 300)
            ),print_r($objCSearch, 1));
            
        }
        
        function testResult(){
            $objCSearch = new \Search\CSearch;
            
            $sPhrase = 'Билеты на концерт';
            
            $arResult = $objCSearch->results($sPhrase,[
                "LIMIT" =>  10,
                "PAGE"  =>  1
            ]);
        }
        
  
    }
