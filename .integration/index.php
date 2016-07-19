<?php
    require_once("classes/profile.class.php");
    require_once("classes/points.class.php");
    require_once("classes/orders.class.php");
    require_once("classes/categories.class.php");
    require_once("classes/products.class.php");
    require_once("classes/manufacturers.class.php");
    //require("passwd.php");
    
    //$auth = new auth();
    //$auth->setLogin($PASSWD[0]["login"]);
    //$auth->setPassword($PASSWD[0]["password"]);
    //echo $auth->login();
    //die;
    
    $manufacturers = new manufacturers("kat-zavyalova@mail.ru");
    echo "<pre>";
    print_r($manufacturers->all());

    /*
    $profile = new profile("13fa3e70de2dc40d08c7a558fff55c4a");
    echo "<pre>";
    print_r($profile->get());
    */
    
    
    
    
    
    
    
