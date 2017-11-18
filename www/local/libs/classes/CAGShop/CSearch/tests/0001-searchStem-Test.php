<?php
    require_once(realpath(__DIR__."/..")."/CSearch.class.php");
    require_once(realpath(__DIR__."/..")."/CSearchStem.class.php");
    use AGPhop\Search as Search;
    
    class agshopSearchStemTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            Тест создания структуры таблиц для поиска
        */
        function testTableCreate(){
            /*
            $objCSearch = new \Search\CSearch;
            $this->assertTrue(
                $objCSearch->tablesRebuild(), 
                print_r($objCSearch,1)
            );
            */
        }

        /**
            Тест получения базовых форм слова
        */
        function testGetStem(){
            $objCSearchStem = new \Search\CSearchStem;

            $this->assertEquals($objCSearchStem->get("боя"), "БОЙ");
            $this->assertEquals($objCSearchStem->get("дубы"), "ДУБ");
            $this->assertEquals($objCSearchStem->get("вышел"), "ВЫЙТИ");
            $this->assertEquals($objCSearchStem->get("уставший"), "УСТАТЬ");

            $sWord = 'боя';
            $sBaseForm = 'БОЙ';
            $arBaseForm = $objCSearchStem->save($sWord);

            $this->assertEquals($arBaseForm["word"], $sBaseForm);
            $this->assertTrue(boolval(intval($arBaseForm["id"])), $sBaseForm);
        }


  
    }
