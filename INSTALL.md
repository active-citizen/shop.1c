Установка магазина поощрений "Активный гражданин"
===

# Системные требования

* Linux x86_64 (CentOS, RHEL, Debian)
* Apache > 2.2
* PHP > 5.3
* MySQL > 5.6
* git
* Perl > 5.20
    * модуль DBI
    * модуль Config::IniFiles
    * модуль Getopt::Long
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

    `./install.pl --show-template-config > config.ini`
- Заполните все пустые параметры в **config.ini**
- Параметр **branch** из секции **Git** нужно прописать в зависимости от того, из какой ветки нужно получить ПО
    * **master** - для последнего вышедшего релиза
    * **develop** - для версии разработчика
- Запустите установщик 
    `./install.pl`
- Дождитесь завершения установки
- Подробный ход процесса установки можно наблюдать, включив режим подробного отчета

    `./install.pl --verbose`

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

