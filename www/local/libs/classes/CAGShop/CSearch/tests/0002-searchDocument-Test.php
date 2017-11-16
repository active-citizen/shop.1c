<?php
    require_once(realpath(__DIR__."/..")."/CSearchDocument.class.php");
    use AGPhop\Search as Search;
    
    class agshopSearchDocumentTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            Тест парсинга документа
        */
        function testDocumentParse(){
            $objCSearchDocument = new \Search\CSearchDocument;
            $arDocumentParse = $objCSearchDocument->parse(file_get_contents(
                realpath(__DIR__."/..")."/data/document.txt"
            ));
            
            $this->assertArrayHasKey("ОПЦИИ",$arDocumentParse);
            $this->assertArrayHasKey("ФАЙЛЫ",$arDocumentParse);
            $this->assertArrayHasKey("ИСПОЛЬЗУЕТСЯ",$arDocumentParse);
            $this->assertArrayHasKey("PREDICT",$arDocumentParse);
            
            $this->assertEquals(
                "PREDICT",$arDocumentParse["PREDICT"]['baseform']['word']
            );
            $this->assertEquals(
                "ИСПОЛЬЗОВАТЬСЯ",$arDocumentParse["ИСПОЛЬЗУЕТСЯ"]['baseform']['word']
            );
            $this->assertEquals(
                "ПРЕДСКАЗАНИЕ",$arDocumentParse["ПРЕДСКАЗАНИЯ"]['baseform']['word']
            );
        }

        /**
            Тест индексирования документа
        */
        function testDocumentIndex(){
            $objCSearchDocument = new \Search\CSearchDocument;
            
            $arDocumentEntries = $objCSearchDocument->index(file_get_contents(
                realpath(__DIR__."/..")."/data/document.txt"
            ));

            $this->assertEquals($arDocumentEntries,82);
            
            $arDocumentEntries = $objCSearchDocument->getEntries();
            $this->assertEquals(count($arDocumentEntries),82);
            
            $arDocumentEntries = $objCSearchDocument->delete();
            $arDocumentEntries = $objCSearchDocument->getEntries();
            $this->assertEquals(
                count($arDocumentEntries),
                0
            );
            
        }
  
    }
