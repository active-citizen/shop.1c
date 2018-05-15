<?php

namespace ag;

class Auth
{
    const TYPE_OPENED_ALL = 0;
    const TYPE_CLOSED_ALL = 1;
    const TYPE_CLOSED_FRONT = 2;
    const TYPE_FRONT_UNDER_PASSWORD = 3;

    protected static $frontUrls = [
        'catalog',
        'profile',
        'rules',
    ];

    protected static $armUrls = [
        'partners',
    ];

    protected static $exeptions = [
        'partners/settings',
    ];

    /** @var int|null */
    protected $type;

    /** @var string|null */
    protected $login;

    /** @var string|null */
    protected $password;

    /** @var string|null */
    protected $baseRequestPart;

    public function __construct()
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/local/libs/classes/CAGShop/CIntegration/CIntegrationSetting.class.php");

        $settings = new \Integration\CIntegrationSettings('AUTH');
        $params = $settings->get();

        if (!$params) {
            return;
        }

        if (isset($params['AUTH_TYPE']['VALUE'])) {
            $this->type = (int)$params['AUTH_TYPE']['VALUE'];
        }

        if (isset($params['AUTH_LOGIN']['VALUE'])) {
            $this->login = $params['AUTH_LOGIN']['VALUE'];
        }

        if (isset($params['AUTH_PASSWORD']['VALUE'])) {
            $this->password = $params['AUTH_PASSWORD']['VALUE'];
        }
    }

    protected function getBaseRequestPart()
    {
        if (!$this->baseRequestPart) {
            $requestParts = explode('/', $_SERVER['REQUEST_URI']);
            if (!empty($requestParts[1])) {
                $this->baseRequestPart = $requestParts[1];
            }
        }

        return $this->baseRequestPart;
    }

    protected function isFront()
    {
        return in_array($this->getBaseRequestPart(), self::$frontUrls);
    }

    protected function isArm()
    {
        return in_array($this->getBaseRequestPart(), self::$armUrls);
    }

    protected function isExept()
    {
        foreach(self::$exeptions as $sPattern)
            if(preg_match("#/$sPattern.*#", $_SERVER["REQUEST_URI"]))
                return true;
        return false;
    }

    public function performAuth()
    {
        if($this->isExept())return;

        $isFront = $this->isFront();
        $isArm = $this->isArm();

        if (!$isFront && !$isArm) {
            return; // нет проверок авторизации на других разделах сайта
        }

        if ($this->type === null) {
            self::notAuthorized();
        }

        switch ($this->type) {
            case self::TYPE_OPENED_ALL:
                return;

            case self::TYPE_CLOSED_ALL:
                self::notAuthorized();
                break;

            case self::TYPE_CLOSED_FRONT:
                if ($this->isFront()) {
                    self::notAuthorized();
                }
                return;

            case self::TYPE_FRONT_UNDER_PASSWORD:
                if ($this->isFront()) {
                    $this->needAuth();
                }
                return;
        }
    }

    protected function needAuth()
    {
        if (empty($this->login) || empty($this->password)) {
            self::notAuthorized();
        }

        if ($_SERVER['PHP_AUTH_USER'] == $this->login && $_SERVER['PHP_AUTH_PW'] == $this->password) {
            return;
        }

        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');

        self::notAuthorized();
    }

    protected static function notAuthorized()
    {
        include($_SERVER["DOCUMENT_ROOT"] . "/403.php");
        exit;
    }
}
