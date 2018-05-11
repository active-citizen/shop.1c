<?

namespace app\shop\order;

use app\shop\CAGShop;

class COrderProperty extends CAGShop
{

    private $arPropertyInfo = [];

    function __construct()
    {
        parent::__construct();
        \CModule::IncludeModule('sale');
    }

    function existsByCode($sPropCode)
    {
        $CDB = new \DB\CDB;
        $sPropCode = htmlspecialchars($sPropCode);
        $arProperty = $CDB->searchOne(\AGShop\CAGShop::t_sale_order_props, [
            "CODE" => $sPropCode
        ], [
            "ID"
        ]);
        if (!$arProperty) {
            $this->addError("Не существует свойства с кодом $sPropCode");
            return false;
        }
        return true;
    }

    function fetchByCode($sPropCode)
    {
        if (!$this->existsByCode($sPropCode)) {
            return false;
        }
        $CDB = new \DB\CDB;

        $this->arPropertyInfo = $CDB->searchOne(\AGShop\CAGShop::t_sale_order_props, [
            "CODE" => $sPropCode
        ]);

    }

    function get()
    {
        return $this->arPropertyInfo;
    }

}
