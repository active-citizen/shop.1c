<?php
    require_once(realpath(__DIR__."/..")."/CUser.class.php");
    use AGShop\User as User;

    class userTest extends PHPUnit_Framework_TestCase{

        /**
        *   Тест метода обработки ошибок
        */
        function testFetch(){

            $objCUser = new \User\CUser;
            
            $nUserId = 1;
            $nUserLogin = 'admin';
            
            $this->assertTrue($objCUser->fetch("ID",1));
            $arUserInfo = $objCUser->get();
            
            $this->assertTrue(boolval($arUserInfo));
            $this->assertArrayHasKey("ID",$arUserInfo);
            $this->assertArrayHasKey("LOGIN",$arUserInfo);
            $this->assertEquals(1,$arUserInfo["ID"]);
            $this->assertEquals('admin',$arUserInfo["LOGIN"]);
            
            $this->assertTrue($objCUser->fetch("LOGIN",'admin'));
            $arUserInfo = $objCUser->get();
            
            $this->assertTrue(boolval($arUserInfo));
            $this->assertArrayHasKey("ID",$arUserInfo);
            $this->assertArrayHasKey("LOGIN",$arUserInfo);
            $this->assertEquals(1,$arUserInfo["ID"]);
            $this->assertEquals('admin',$arUserInfo["LOGIN"]);

            $this->assertFalse($objCUser->fetch("ID",'aaaa'));
            $this->assertFalse($objCUser->fetch("LOGIN",md5(rand())));
            $this->assertFalse($objCUser->fetch("EMAIL",md5(rand())));

            $this->assertFalse($objCUser->fetch("SEX",md5(rand())));
        }

    }
