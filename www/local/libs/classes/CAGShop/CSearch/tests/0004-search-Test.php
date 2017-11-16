<?php
    require_once(realpath(__DIR__."/..")."/CSearch.class.php");
    require_once(realpath(__DIR__."/..")."/CSearchDocument.class.php");
    use AGPhop\Search as Search;
    
    class agshopSearchTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            Тест поиска
        */
        function testSearch(){
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
            $this->assertTrue(count($arSearch)>=1);
            
            // Удаляем документ из индекса
            $objCSearchDocument->delete();
            
        }

        function testSearchExt(){
            $objCSearch = new \Search\CSearch;
        }
  
    }
