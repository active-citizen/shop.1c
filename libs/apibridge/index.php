<?php
    require("profile.class.php");
    //require("passwd.php");
    
    //$auth = new auth();
    //$auth->setLogin($PASSWD[0]["login"]);
    //$auth->setPassword($PASSWD[0]["password"]);
    //echo $auth->login();
    //die;
    
    $profile = new profile("13fa3e70de2dc40d08c7a558fff55c4a");
    echo "<pre>";
    print_r($profile->get());
    
    
    
    
    
    
    
    
