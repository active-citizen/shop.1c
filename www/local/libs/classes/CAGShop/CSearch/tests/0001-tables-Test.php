<?php
    require_once(realpath(__DIR__."/..")."/CSearch.class.php");
    use AGPhop\Search as Search;
    
    class agshopSearchTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        function testTableCreate(){
            // Тест создания структуры таблиц для поиска
            $objCSearch = new \Search\CSearch;
            $this->assertTrue(
                $objCSearch->tablesRebuild(), 
                print_r($objCSearch->getErrors(),1)
            );
            
        }

  
    }
