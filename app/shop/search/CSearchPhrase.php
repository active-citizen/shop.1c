<?php

namespace app\shop\search;

use app\shop\CAGShop;

/**
 * Документы в поиске
 */
class CSearchPhrase extends CAGShop
{

    private $nMinLength = 3;
    private $nMaxResults = 10;

    /**
     * Выводим ранее введённые поисковые фразы, совпадающие началом с шаблоном
     */
    function get($sPattern = '')
    {
        $objCDB = new \DB\CDB;
        return $objCDB->sqlSelect("
            SELECT
                `id`,`ctime`,`phrase`
            FROM
                `" . ISearch::t_csearch_phrases . "`
            WHERE
                `phrase` LIKE '" . $objCDB->ForSql($sPattern) . "%'
            LIMIT
                " . $this->nMaxResults . "
        ", $this->nMaxResults);
    }

    /**
     *
     * @return ID добавленной фразы
     */
    function add($sPhrase)
    {
        $objCDB = new \DB\CDB;
        return $objCDB->insert(
            ISearch::t_csearch_phrases,
            ["ctime" => date("Y-m-d H:i:s"), "phrase" => $sPhrase]
        );
    }

    function delete($sPhrase)
    {
        $objCDB = new \DB\CDB;
        return $objCDB->delete(ISearch::t_csearch_phrases, ["phrase" => $sPhrase]);
        return true;
    }


}

