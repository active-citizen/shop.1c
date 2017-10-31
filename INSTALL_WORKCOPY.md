Установка локальной рабочей копии магазина поощрений (для разработчика)
=====================

# Подготовка серверного окружения

+ Создать базу данных, рабочий каталог проекта и сделать в ней подкаталог www
+ Установить Apache2, MySQL и PHP в необходимой для работы Битрикс комплектации
(самостоятельная настройка или BitrixVM)
+ Настроить локальный домен и виртуальный хост Apache на работу из подкаталога
www
+ Для избежания проблем с правами можно настроить php как php-fpm или как CGI,
для работы от того же пользователя, от которого производятся работы. Примеры
конфигов
```
# Apache virtualhost
<VirtualHost *:80>

    <Directory /home/workuser/projects/www/shop.ag.mos.ru.local/www>
    Options +ExecCGI
    AllowOverride All
    Order allow,deny
    allow from all
    </Directory>

    <Directory /home/workuser/projects/cgi-bin>
    Options +ExecCGI
    AllowOverride All
    Order allow,deny
    allow from all
    </Directory>

    ServerName shop.ag.mos.ru.local
    DirectoryIndex index.php
    DocumentRoot /home/workuser/projects/www/shop.ag.mos.ru.local/www
    ServerAdmin andrey@fmf.ru

    CustomLog /home/workuser/projects/logs/shop.ag.mos.ru.local.access.log combined
    ErrorLog /home/workuser/projects/logs/shop.ag.mos.ru.local.ru.error.log

    ScriptAlias /cgi-bin/ "/home/workuser/projects/cgi-bin/"
    AddHandler application/x-httpd-php .php
    Action  application/x-httpd-php /cgi-bin/php7
    SuexecUserGroup workuser workuser


</VirtualHost>

```
```
# php-fpm config
[global]
pid = run/php-fpm.pid
events.mechanism = epoll
[www]
user = workuser
group = workuser
;listen = 127.0.01:8888 
listen = /home/workuser/tmp/www.sock
listen.owner = workuser
listen.group = workuser
pm = dynamic
pm.max_children = 455
pm.start_servers = 64
pm.min_spare_servers = 64
pm.max_spare_servers = 65
pm.max_requests = 4000

```

# Установка интернет-магазина Битрикс

+ Скачать дистрибутив интернет-магазина в редакции Бизнес
(https://www.1c-bitrix.ru/download/business_encode_php5.zip) распаковать у
подкаталог *www* и запустить процедуру установки
+ Установить базовую систему, а при выборе готового решения для установки
выбрать "Интернет-магазин". Все установки по-умолчанию, кроме 
    - "Когда резервировать товар на складе" - выставить "при отгрузке"
    - Типы плательщиков "физическое лицо"
    - Способы оплаты "наличные"
    - Способы доставки "самовывоз"

# Разворачивание исходного кода из репозитория

+ В рабочем каталоге проекта выполнить
```
git init
git remote add origin https://github.com/active-citizen/shop.1c.git
git fetch
git checkout feature
```

# Выполнение миграций
+ Авторизуемся от пользователя admin и переходим по адресу /local/.migration
+ Выбираем все пункты и кликаем "Запустить". Дождаться окончания установки

# Наполнение каталога товарами
+ Авторизуемся под пользователем admin
+ Копируем содержимое архива /data/catalog.zip в /upload/1c_cataog/
+ Запускаем из браузера 

# Наполнение заказами
(Выполняется опционально)




