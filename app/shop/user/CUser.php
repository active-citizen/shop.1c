<?php

namespace app\shop\user;

use app\shop\CAGShop;

class CUser extends CAGShop
{

    private $arUserInfo = [];

    function __construct()
    {
        parent::__construct();
        \CModule::IncludeModule("sale");
        \CModule::IncludeModule("forum");
    }

    /**
     * Загрузка информации о пользователе из БД по одному из параметров
     *
     * @param $sUserParamName - имя параметра ("ID","LOGIN","EMAIL")
     * @param $sUserParam     - значение параметра
     */
    function fetch($sUserParamName, $sUserParam)
    {
        $objCache = new \Cache\CCache("userInfo" . $sUserParamName, $sUserParam);
        if ($sCacheData = $objCache->get()) {
            $this->arUserInfo = $sCacheData;
            return true;
        }
        $arResult = [];
        switch ($sUserParamName) {
            case 'ID':
                $arResult = \CUser::GetByID(intval($sUserParam))->Fetch();
                break;
            case 'LOGIN':
                $arResult = \CUser::GetByLogin($sUserParam)->Fetch();
                break;
            case 'EMAIL':
                $arResult = \CUser::GetList(
                    ($by = "personal_country"), ($order = "desc"),
                    ["EMAIL" => $sUserParam]
                )->Fetch();
                break;
            default:
                $this->addError("Неизвестный параметр для поиска пользователя "
                    . htmlspecialchars($sUserParamName));
                return false;
                break;
        }
        if (!$arResult) {
            $this->addError("Не удалось найти пользователя для котрого " .
                htmlspecialchars($sUserParamName) . "=" . htmlspecialchars($sUserParam));
            return false;
        }
        $this->arUserInfo = $arResult;
        $objCache->set($arResult);
        return true;
    }

    /**
     * Возвращает текущего пользователя
     */
    function get()
    {
        return $this->arUserInfo;
    }

    /**
     * Получение число баллов на счету пользователя
     *
     * @param $nUserId - ID пользователя
     */
    function getPoints($nUserId)
    {
        $objSSAGAccount = new \SSAG\CSSAGAccount('', $nUserId);
        return $objSSAGAccount->balance();
    }


    function getById($nUserId)
    {
        $this->fetch("ID", $nUserId);
        return $this->get();
    }

}
