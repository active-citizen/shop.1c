-- Таблицы для индексирования поисковых запросов

DROP TABLE IF EXISTS `csearch_stems`;
DROP TABLE IF EXISTS `csearch_entries`;
DROP TABLE IF EXISTS `csearch_phrases`;
DROP TABLE IF EXISTS `csearch_documents`;

CREATE TABLE IF NOT EXISTS `csearch_stems`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `word` CHAR(48) COMMENT 'Основа слова',
    PRIMARY KEY `id`(`id`),
    UNIQUE KEY `word`(`word`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci 
COMMENT 'Основы слов';

CREATE TABLE IF NOT EXISTS `csearch_entries`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `entry` CHAR(24),
    `stem_id` INT(11) UNSIGNED,
    `position` SMALLINT(5) UNSIGNED,
    `doc_id` INT(11) UNSIGNED,
    `doc_type_id` TINYINT(3) UNSIGNED,
    `exact` TINYINT(1) UNSIGNED,
    PRIMARY KEY `id`(`id`),
    INDEX `entry`(`entry`),
    INDEX `stem_id`(`stem_id`),
    INDEX `position`(`position`),
    INDEX `doc_id`(`doc_id`),
    INDEX `exact`(`exact`),
    INDEX `doc_type_id`(`doc_type_id`),
    UNIQUE KEY `entry_in_docs`(`stem_id`,`position`,`doc_id`,`doc_type_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci 
COMMENT 'Вхождения слов в документы';

CREATE TABLE IF NOT EXISTS `csearch_phrases`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ctime` DATETIME,
    `phrase` CHAR(48) COMMENT 'Поисковая фраза',
    PRIMARY KEY `id`(`id`),
    INDEX `ctime`(`ctime`),
    UNIQUE KEY `phrase`(`phrase`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci 
COMMENT 'Поисковые фразы';

CREATE TABLE IF NOT EXISTS `csearch_documents`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `doc_id` INT(11) UNSIGNED,
    `doc_type_id` INT(11) UNSIGNED,
    PRIMARY KEY `id`(`id`),
    INDEX `doc_id`(`doc_id`),
    INDEX `doc_type_id`(`doc_type_id`),    
    UNIQUE KEY `phrase`(`doc_id`,`doc_type_id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_unicode_ci 
COMMENT 'Проиндексированные документы';
