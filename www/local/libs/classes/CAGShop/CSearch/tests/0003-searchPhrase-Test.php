<?php
    require_once(realpath(__DIR__."/..")."/CSearchPhrase.class.php");
    use AGPhop\Search as Search;
    
    class agshopSearchPhraseTest extends PHPUnit_Framework_TestCase{

        function __construct($sTroykaNum){
        }

        /**
            Тест парсинга документа
        */
        function testPhrase(){
            $objCSearchPhrase = new \Search\CSearchPhrase;
            $sFullPhrase = md5(rand());
            $nMinPhraseLength = 3;
            $nTestPhrases = 3;

            $this->assertEquals(
                count(
                    $objCSearchPhrase->get(
                        mb_substr($sFullPhrase, 0, $nMinPhraseLength)
                    )
                ),
                0
            );

            // Добавляем фразы
            $arPhrases = [];
            for($i=$nMinPhraseLength;$i<$nMinPhraseLength+$nTestPhrases;$i++){
                $sPhrase = mb_substr($sFullPhrase, 0, $i);
                $sPhraseId = $objCSearchPhrase->add($sPhrase);
                $this->assertTrue(
                    boolval($sPhraseId),
                    print_r($objCSearchPhrase->getErrors(),1)
                );
                $arPhrases[$sPhraseId] = $sPhrase;
            }
            
            // Проверяем количество выведенных фраз по шаблону
            $nCounter = 4;
            for($i=$nMinPhraseLength;$i<$nMinPhraseLength+$nTestPhrases;$i++){
                $nCounter--;
                $sPhrase = mb_substr($sFullPhrase, 0, $i);
                $this->assertEquals(
                    count($arPhrasesSearch = $objCSearchPhrase->get($sPhrase)),
                    $nCounter
                );
            }
            
            // Удаляем фразы
            foreach($arPhrases as $nId=>$sPhrase){
                $objCSearchPhrase->delete($sPhrase);
            }
            
            
            // Проверяем количество выведенных фраз по шаблону (что нету)
            for($i=$nMinPhraseLength;$i<$nMinPhraseLength+$nTestPhrases;$i++){
                $sPhrase = mb_substr($sFullPhrase, 0, $i);
                $this->assertEquals(
                    count($arPhrasesSearch = $objCSearchPhrase->get($sPhrase)),
                    0
                );
            }

            
        }

  
    }
