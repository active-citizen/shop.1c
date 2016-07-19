Установка магазина поощрений "Активный гражданин"
===

# Системные требования

* Linux x86_64 (CentOS, RHEL, Debian)
* Apache > 2.2
* PHP > 5.3 (php-fpm, cli)
    * short_open_tag = 0
    * mbstring.internal_encoding = UTF-8
    * mbstring.func_overload = 2
    * realpath_cache_size = 4096k
    * pcre.recursion_limit = 10000
* MySQL > 5.6
    * sql_mode = ALLOW_INVALID_DATES
* git
* Perl > 5.20
    * модуль DBI
    * модуль Config::IniFiles
    * модуль Getopt::Long
    * модуль Time::localtime
    * модуль Email::Sender::Simple
    * XML::Simple
* wget
* gzip


# Установка

- Перейдите в корневой каталог будущего сайта
- Склонируйте репозиторий из github 

    `git clone git@github.com:active-citizen/shop.1c.git .` 
- Переключитесь на ветку **master**

    `git checkout master`
- Перейдите в каталог **.install**

    `cd .install`
- Создайте шаблонный файл конфигурации

    `./update.pl --show-template-config > config.ini`
- Заполните все пустые параметры в **config.ini**
- Параметр **branch** из секции **Git** нужно прописать в зависимости от того, из какой ветки нужно получить ПО
    * **master** - для последнего вышедшего релиза
    * **develop** - для версии разработчика
- Запустите установщик 
    `./update.pl --install-bitrix -sync`
- Дождитесь завершения установки

## Дополнительные опции установки

- Подробный ход процесса установки можно наблюдать, включив режим подробного отчета

    `./update.pl --install-bitrix -sync --verbose`

- Запустить прохождение unit-тестов, оценку качества кода и выслать результаты на email, указанный в config.ini можно так

    `./update.pl --install-bitrix -sync --unittests --send-report`
    
- Просмотреть все доступные опции

    `./update.pl --help`

    > --help                  помощь  
    > --verbose               болтливый режим  
    > --config=ФАЙЛ           задать конфиг для установки вручную (по умолчанию config.ini)  
    > --show-template-config  показать файл конфигурации с опциями по умолчанию  
    > --install-bitrix        установить Битрикс  
    > --unittests             Выполнение автотестов  
    > --sync                  Синхронизация кода и выполнение миграций между коммитами (если в удалённом репозитории появился новый коммит в заданной конфигом ветке)  
    > --make-report           Создать отчет об обновлении в указанной папке  
    > --send-report           Послать отчёт об обновлении на прописанные в конфиге адреса  
    
    
# Обновление

# Тонкая настройка web-хостинга для увеличения производительности

## Замена Apache на nginx

## Настройка кеширования статических файлов в nginx

## Настройка кеширования через memcached

- Установите демон memcached доступным в вашем дистрибутиве Linuxменеджером пакетов

    `apt-get install memcached`

    `yum install memcached`
    
- Убедитесь, что memcached настроен тапим образос, что
    - Прослушиваемый хост **127.0.0.1**
    - Прослушиваемый порт 11211

- запустите memcached

    `servece memcached restart`
    
- Пропишите в конце файла **bitrix/php_interface/dbconn.php** следующее

    > define("BX_CACHE_TYPE", "memcache");  
    > define("BX_CACHE_SID", $_SERVER["DOCUMENT_ROOT"]."#01");  
    > define("BX_MEMCACHE_HOST", "127.0.0.1");  
    > define("BX_MEMCACHE_PORT", "11211");  
    
## Настройка MySQL

