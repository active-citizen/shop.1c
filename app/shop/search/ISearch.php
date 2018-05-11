<?php

namespace app\components\shop\search;

interface ISearch
{
    // Таблицы БД поискового движка
    const t_csearch_documents = 'csearch_documents';
    const t_csearch_entries = 'csearch_entries';
    const t_csearch_phrases = 'csearch_phrases';
    const t_csearch_stems = 'csearch_stems';
    const t_csearch_options = 'csearch_options';

}
