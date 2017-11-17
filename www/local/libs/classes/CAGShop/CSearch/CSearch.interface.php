<?php
    namespace Search;
    
    interface ISearch{
        // Таблицы БД
        const t_csearch_documents = 'csearch_documents';
        const t_csearch_entries = 'csearch_entries';
        const t_csearch_phrases = 'csearch_phrases';
        const t_csearch_stems = 'csearch_stems';
        const t_csearch_options = 'csearch_options';
        
        const t_iblock_element  =  'b_iblock_element';
        const t_iblock_element_property = 'b_iblock_element_property';
        const t_iblock_section = 'b_iblock_section';
        const t_file = 'b_file';
    }
