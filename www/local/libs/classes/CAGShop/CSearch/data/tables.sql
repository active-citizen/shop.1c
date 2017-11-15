-- Таблицы для индексирования поисковых запросов

DROP TABLE IF EXISTS `csearch_stems`;

CREATE TABLE IF NOT EXISTS`csearch_stems`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `word` CHAR(48) COMMENT 'Основа слова',
    PRIMARY KEY `id`(`id`),
    UNIQUE KEY `word`(`word`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci 
COMMENT 'Основы слов';
