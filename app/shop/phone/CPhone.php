<?php

namespace app\shop\phone;

use app\shop\CAGShop;

class CPhone extends CAGShop
{
    function __construct()
    {
        parent::__construct();
    }

    function isCorrect($sPhoneNumber)
    {
        if (!preg_match("#^\d{5,11}$#", $sPhoneNumber)) {
            return false;
        }
        return true;
    }
}
    
