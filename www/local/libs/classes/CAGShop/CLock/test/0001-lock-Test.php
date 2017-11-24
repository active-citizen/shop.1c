<?php
    require_once(realpath(__DIR__."/..")."/CLock.class.php");
    use AGPhop\Lock as Lock;
    
    class lockTest extends PHPUnit_Framework_TestCase{
        

        /**
        *   Проверка режима работы MySQL
        */
        function testLock(){
            $sLockType = 'TESTLOCK';
            $sLockId = rand(1,100000000000);
            $nLockExpire = 3;
            
            $objLock = new \Lock\CLock($sLockType, $sLockId, $nLockExpire);
            // Проверяем блокировку, блокируем снова проверяем
            $this->assertFalse($objLock->isLocked(),print_r($objLock,1));
            $this->assertTrue($objLock->lock(),print_r($objLock,1));
            $this->assertTrue($objLock->isLocked(),print_r($objLock,1));
            // Ждём 5 секунд, снова проверяем
            sleep(5);
            $this->assertFalse($objLock->isLocked(),print_r($objLock,1));
            
            // Снова блокируем, проверяем, принудительно снимаем, проверяем
            $this->assertTrue($objLock->lock(),print_r($objLock,1));
            $this->assertTrue($objLock->isLocked(),print_r($objLock,1));
            
            //$this->assertTrue($objLock->reset(),print_r($objLock,1));
            //$this->assertFalse($objLock->isLocked(),print_r($objLock,1));
            
        }
        
    }
