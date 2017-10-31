Установка локальной рабочей копии магазина поощрений (для разработчика) версии
1.*
=====================

# Подготовка серверного окружения

+ Создать базу данных, рабочий каталог проекта и сделать в ней подкаталог www
+ Установить Apache2, MySQL и PHP в необходимой для работы Битрикс комплектации
(самостоятельная настройка или BitrixVM)
+ Настроить локальный домен и виртуальный хост Apache на работу из подкаталога
www
+ Для избежания проблем с правами можно настроить php как php-fpm или как CGI,
для работы от того же пользователя, от которого производятся работы. 

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
git checkout feature -f
```

# Копирование настроечных файлов
Репозиторий не содержит файлов с паролями и токенами доступпов, поэтому их
необходимо получить у администрации магазина и скопировать в рабочую копию
+ www/local/.integration/secret.inc.php
+ www/local/php_interface/settings.inc.php
+ www/api/include/config.php

# Выполнение миграций магазина
+ Авторизуемся от пользователя admin и переходим по адресу /local/.migration
+ Выбираем все пункты и кликаем "Запустить". Дождаться окончания установки

# Выполнение миграций ККБ
+ Перейти в www/api/migrations
+ Запустить каждый php-скрипт в каталоге `php {скрипт} dev.shop.ag.mos.ru` в порядке, заданном числом в префиксе их имени
+ Прописать в www/local/php_interface/settings.inc.php у ключа `bcc_url` имя домена, на котором разворачивается копия, а `https` заменить на `http`

# Наполнение каталога товарами
+ Авторизуемся под пользователем admin
+ Копируем содержимое каталоа www/data/catalog в www/upload/1c_catalog/
+ Запускаем из браузера /.integration/1c_exchange.php?type=catalog&mode=import&filename=import.xml
+ Запускаем из браузера /.integration/1c_exchange.php?type=catalog&mode=import&filename=offers.xml

# Наполнение заказами
+ Получаем у администрации магазина файл orders.xml и кладём в www/upload/1c_exchange/
+ Авторизуемся под пользователем admin
+ Запускаем из браузера /.integration/1c_exchange.php?type=sale&mode=file&filename=orders.xml



