<?

namespace app\shop\search;

use app\shop\CAGShop;

/**
 * Поиск по магазину
 */
class CSearch extends CAGShop
{

    private $nMaxWords = 10; //!< Максимальное число значащих слов в запросе
    private $nDocsLimit = 12; //!< Число результатов на страницу

    var $resultsCount = 0;

    /**
     * Перестроение индексных таблиц
     */
    function tablesRebuild()
    {
        $objCDB = new \DB\CDB;
        if (!$objCDB->runSqlFile(realpath(__DIR__) . "/data/tables.sql")) {
            $this->addError($objCDB->getErrors());
            return false;
        }
        return true;
    }

    /*
        Выдача списка ID Документов, по которым что-то нашли
        @param $sPhase - поисковая фраза
        @param $arOptions - опции 
        [
            "LIMIT" =>  {Количество результатов на страницу},
            "PAGE"  =>  {Номер страницы, начиная с 1},
            "OPTIONS"=> {ОПЦИИ пойска}
        ]
        @param $sDocType - тип документа (по-умолчанию PRODUCT)
    */
    function getDocsIndex($sPhase, $arOptions = [], $sDocType = 'PRODUCT')
    {

        switch ($sDocType) {
            case 'PRODUCT':
                $arResult = $this->__getDocsIndexProduct($sPhase, $arOptions);
                break;
        }
        return $arResult;
    }


    /*
        Выдача результатов поискового запроса
        @param $sPhase - поисковая фраза
        @param $arOptions - опции 
        [
            "LIMIT" =>  {Количество результатов на страницу},
            "PAGE"  =>  {Номер страницы, начиная с 1}
        ]
    */
    function results($sPhase, $arOptions = [])
    {

        $CSearchDocumentType = new \Search\CSearchDocumentType;
        $CSearchDocumentOption = new \Search\CSearchDocumentOption;
        $CSearchPhrase = new \Search\CSearchPhrase;
        // Запоминаем поисковую фразу
        $CSearchPhrase->add($sPhase);

        // Получаем список документов
        $arIndex = $this->getDocsIndex($sPhase, $arOptions);
        // Собираем подрообную информацию по каждому
        $arDocs = [];

        // для каждого результата получаем подробности документа
        foreach ($arIndex as $arDocIndexItem) {
            $arDoc = array_merge(
                $arDocIndexItem,
                $CSearchDocumentType->getDocInfo(
                    $arDocIndexItem["doc_id"],
                    $CSearchDocumentType->getCode($arDocIndexItem["doc_type_id"])
                )
            );

            $arDoc["OPTIONS"] = $CSearchDocumentOption->getSummary(
                $arDocIndexItem["doc_id"],
                $CSearchDocumentType->getCode($arDocIndexItem["doc_type_id"])
            );

            $arDocs[] = $arDoc;
        }
        return $arDocs;
    }

    /**
     * Индексирование товара на сайте. Берём ещё непроиндексированный или
     * протухший в индексе товар и индексируем его
     * @param $sType    - тип документа
     * @param $nExpires - время переиндексирования
     */
    function indexNextDocument($sType = 'PRODUCT', $nExpires = 86400)
    {
        $objCSearchDocumentType = new \Search\CSearchDocumentType;
        $objCSearchDocument = new \Search\CSearchDocument;
        $objCSearchDocumentOption = new \Search\CSearchDocumentOption;

        // Получаем ID следующего документа согласно типу и времени индексации
        $nDocId = $objCSearchDocumentType->getNextDocument($sType, $nExpires);
        // Получаем индексируемый контент документа
        $sText = $objCSearchDocumentType->getSearchableContent($nDocId, $sType);
        // Индексируем контент
        if (!$objCSearchDocument->index($sText, $nDocId, $sType)) {
            $this->addError($objCSearchDocument);
            return false;
        }
        return true;
    }

    /*
        Выдача списка ID Документов-продуктов, по которым что-то нашли
        @param $sPhase - поисковая фраза
        @param $arOptions - опции 
        [
            "LIMIT" =>  {Количество результатов на страницу},
            "PAGE"  =>  {Номер страницы, начиная с 1},
            "OPTIONS"=> {ОПЦИИ пойска}
        ]
        @param $sDocType - тип документа (по-умолчанию PRODUCT)
    */
    private function __getDocsIndexProduct($sPhase, $arOptions = [])
    {
        $CSearchDocumentOption = new \Search\CSearchDocumentOption;

        if (!isset($arOptions["LIMIT"])) {
            $arOptions["LIMIT"] = $this->nDocsLimit;
        }
        if (!isset($arOptions["PAGE"])) {
            $arOptions["PAGE"] = 1;
        }
        if (!isset($arOptions["FILTER"])) {
            $arOptions["FILTER"] = [];
        }

        $objCSearchStem = new CSearchStem;

        if (!$arStemsIds = $objCSearchStem->getBaseFormsIds(
            $sPhase,
            $this->nMaxWords
        )) {
            $this->addError($objCSearchStem->getErrors());
            return false;
        }
        $sStemsIds = "'" . implode("','", $arStemsIds) . "'";

        $objCDB = new \DB\CDB;


        $nOffset = ($arOptions["PAGE"] - 1) * $arOptions["LIMIT"];
        $nLimit = $arOptions["LIMIT"];

        $sFrom = "
                `" . ISearch::t_csearch_entries . "` as `entries`
                    LEFT JOIN
                `" . ISearch::t_csearch_options . "` as `sections`
                    ON 
                    `entries`.`doc_id`=`sections`.`doc_id`
                    AND `entries`.`doc_type_id`=`sections`.`doc_type_id`
                    AND `sections`.`opt_type_id`="
            . $CSearchDocumentOption->getId('SECTION_ID') . "
                    LEFT JOIN
                `" . ISearch::t_csearch_documents . "` as `doc`
                    ON
                    `doc`.`doc_id`=`entries`.`doc_id`
                    AND
                    `doc`.`doc_type_id`=`entries`.`doc_type_id`        
        ";
        $arEnabledOptions = $CSearchDocumentOption->getTypes();
        foreach ($arOptions["FILTER"] as $sFilterOption => $sFilterOptionValue) {
            // Пропускаем несуществующие опции 
            if (!isset($arEnabledOptions[$sFilterOption])) {
                continue;
            }
            $sFilterOptionValue = intval($sFilterOptionValue);
            $sFrom .= "
                    LEFT JOIN
                `" . ISearch::t_csearch_options . "` as `option_$sFilterOption`
                    ON
                    `doc`.`doc_id`=`option_$sFilterOption`.`doc_id`
                    AND
                    `doc`.`doc_type_id`=`option_$sFilterOption`.`doc_type_id`
                    AND
                    `option_$sFilterOption`.`opt_type_id`=" .
                $arEnabledOptions[$sFilterOption]
                . "
            ";
        }

        $sWhere = "
                (
                    `entries`.`stem_id` IN ($sStemsIds)
                    OR
                    `entries`.`entry` LIKE '" . $objCDB->ForSql($sPhase) . "%'
                )
                AND `sections`.`id` IS NOT NULL
        ";
        foreach ($arOptions["FILTER"] as $sFilterOption => $sFilterOptionValue) {
            // Пропускаем несуществующие опции 
            if (!isset($arEnabledOptions[$sFilterOption])) {
                continue;
            }
            $sFilterOptionValue = intval($sFilterOptionValue);
            $sWhere .= "
                AND `option_$sFilterOption`.`opt_value`='$sFilterOptionValue'
            ";
        }

        $sQuery = "
            SELECT
                COUNT(DISTINCT `entries`.`doc_id`) as `COUNT`
            FROM
                $sFrom
            WHERE
                $sWhere
            LIMIT
                1
        ";
        $arCount = $objCDB->sqlSelect($sQuery);

        $this->resultsCount = $arCount[0]["COUNT"];

        $sQuery = "
            SELECT
                `entries`.`doc_id`,
                `entries`.`doc_type_id`,
                SUM(`entries`.`exact`)  as `exacts`,
                COUNT(`entries`.`id`) as `entries`,
                MIN(`entries`.`position`) as `minpos`,
                `doc`.`rank` as `rank`
            FROM
                $sFrom
            WHERE
                $sWhere
            GROUP BY
                `entries`.`doc_id`
            ORDER BY
                `exacts` DESC,
                `entries` DESC,
                `rank` DESC, 
                `minpos` ASC
            LIMIT
                $nOffset, $nLimit
        ";


        return $arResult = $objCDB->sqlSelect($sQuery);
    }
}
