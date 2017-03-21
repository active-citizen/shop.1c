Установка магазина поощрений "Активный гражданин"
===

# Системные требования

* Linux x86_64 (CentOS, RHEL, Debian)
* nginx > 1.8
* PHP > 5.3
    * php-memcache
    * php-xml
    * php-gd
    * php-curl
    * php-mysqli

* php-fpm
* MySQL > 5.6
* msmtp
* git
* wget
* gzip
* unzip

# Установка

- Убедиться, что установлено всё ПО, перечисленное в разделе *системные
  требования*. Отсутствующее ПО установить

## Получение необходимого кода
- `sudo adduser bitrix` - создаём нового пользователя;
- `sudo su bitrix`- заходим от лица нового пользователя;
- `cd` - переходим в домашний каталог /home/bitrix
- `rm -frv * .*`- опустошаем домашний каталог;
- `git clone https://github.com/active-citizen/shop.1c.git .`- клонируем в
  домашний каталог git-репозиторий проекта
- `cd www` - переходим в корневой каталог сайта;
- `rm -frv * .* local`- чистим корневую папку сайта (если этого не сделать, то при
  установке будут проблемы) 
- `wget -O bitrix.zip "http://www.1c-bitrix.ru/download/business_encode_php5.zip"`- скачиваем
  дистрибутив Битрикс-бизнес
- `unzip -o bitrix.zip` - распаковываем дистрибутив установщик с заменой
  существующих файлов;
- `rm bitrix.zip` - удаляем дистрибутив Битрикса.

## Настройка серверного ПО

Системные файлы настройки ПО могут располагаться в других местах в зависимости
от дистрибутива. Данный раздел описан для Debian Linux.

### Настройка nginx
- `sudo mv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.origin`- переименовываем
  системный конфиг;
- `sudo ln -s /home/bitrix/etc/nginx/nginx.conf /etc/nginx/nginx.conf`- делаем
  символическую ссылку с системного конфига на конфиг в проекте;
- `sudo service nginx restart` - перезапускаем nginx;

### htt-авторизация

Перед тем, как выложить проект на всеобще обозрение, возможно, возникнет желание
потестировать его приватно
- `sudo su bitrix` - заходим от имени пользователя bitrix
- `cd` - переходим в домашний каталог пользователя
- `cp -f etc/nginx/auth.conf.dist etc/nginx/auth.conf` копируем шаблонный конфиг
  включения htt-авторизации
- `htpasswd -c etc/nginx/auth.passwd shop` - формируем файл с паролями
- `sudo service nginx restart` - перезапускаем nginx;

### Настройка php
- `sudo mv /etc/php5/fpm/php.ini /etc/php5/fpm/php.ini.origin`- переименовываем
  системный конфиг;
- `sudo ln -s /home/bitrix/etc/php.ini /etc/php5/fpm/php.ini`- делаем
  символическую ссылку с системного конфига на конфиг в проекте;

### Настройка php-fpm

- `sudo mv /etc/php5/fpm/php-fpm.conf /etc/php5/fpm/php-fpm.conf.origin`- переименовываем
  системный конфиг;
- `sudo ln -s /home/bitrix/etc/php-fpm.conf /etc/php5/fpm/php-fpm.conf`- делаем
  символическую ссылку с системного конфига на конфиг в проекте;
- `sudo service php5-fpm restart` - перезапускаем php-fpm

### Настройка memcached

- `sudo mv /etc/memcached.conf /etc/memcached.origin`- переименовываем
  системный конфиг;
- `sudo ln -s /home/bitrix/etc/memcached.conf /etc/memcached.conf`- делаем
  символическую ссылку с системного конфига на конфиг в проекте;
- `sudo service memcached restart` - перезапускаем memcached




### Настройка mysql

*Рекомендуется имя базы, имя пользователя и пароль заменить на собственные.
Помните, что пароль должен содержать большие и маленькие латинские буквы, цифры,
знаки препинания и быть не короче 8 символов*

- `sudo mv /etc/mysql/my.cnf /etc/mysql/my.cnf.origin` - переименовываем
  системный конфиг
- `sudo ln -s /home/bitrix/etc/my.cnf /etc/mysql/my.cnf`
- `sudo service mysql restat`
- `echo "CREATE DATABASE agshop_prod;"|sudo mysql -u root -p` - создаём БД под
  проект
- `echo "GRANT ALL PRIVILEGES ON agshop_prod.* TO agshop_user@localhost IDENTIFIED BY 'd5Rt(s0Mxq';"|sudo mysql -u root -p` - создаём пользователя БД
  и даём ему пароль и права на БД.
- `echo "GRANT ALL PRIVILEGES ON agshop_prod.* TO agshop_user@127.0.0.1 IDENTIFIED BY 'd5Rt(s0Mxq';"|sudo mysql -u root -p` - то же самое для другого
  имени хоста

### Настройка msmtp
- `sudo cp /home/bitrix/etc/msmtprc /etc/msmtprc` - копируем шаблон конфига в системное место
- заполняем атрибуты подключение к SMTP-аккаунту


## Установка Битрикс

- Далее
- Принять условия лицинзии
- Снять чекбокс "Я хочу зарегистрировать свою копию продукта, устанавливать решения из
  Marketplace и получать обновления", поставить чекбокс "*Установить в кодировке
  UTF-8*"
- Убедиться в правильности похождения теста настроек
- Установить параметры БД
    - сервер *localhost*
    - Пользователь БД - *существующий*
    - Имя пользователя, пароль и БД из раздела "настройка mysql"
    - Тип таблиц - *стандартный*
    - Права на доступ к файлам - *644*
    - Права на доступ к папкам - *755*
- Дождаться установки ядра
- Ввести параметры администратора сайта
    - Логин
    - Пароль
    - email
- Выбрать решение для установки *Интернет-магазин*
- Выбрать любой шаблон, например *Адаптивный шаблон с горизонтальным меню*
- Выбрать любую тему, например *Зеленый*
- Снять чекбокс *установить мобильное приложение для интернет-магазина* и оставить
  чекбокс *Добавить для группы "Все пользователи (в том числе неавторизованные)"
  право на просмотр и на покупку по этому типу цен.*
- Оставить чекбокс *Включить складской учет*, а полу "Когда резервировать товар на
  складе" поставить в положение *при оплае заказа*
- Информацию о магазине не изменять
- Чекбоксы "тип плательщиков" - оставить только *физическое лицо*
- Способы оплаты - не трогать
- Способ доставки - оставить только "самовывоз"
- Местоположение - "Россия и СНГ"
- Дождаться установки решения


## Разворачивание магазина

Выполнить из под пользователя bitrix

- `cd ~/www` - перейти в корневую папку сайта
- `git checkout -- .`- восстановить удалённые файлы из репозитория
- `cd` - вернуться к домашний каталог пользователя
- `cp -f etc/secret.inc.php www/.integration/secret.inc.php` - копировать
   шаблонный файл ключей и паролей
- `vim www/.integration/secret.inc.php` - отредактировать, внеся актуальные
  ключи и пароли
- `cp -f etc/settings.inc.php www/local/php_interface/settings.inc.php` -
  копировать шаблонный файл настроек
- `vim www/local/php_interface/settings.inc.php` - отредактировать, внеся
  актуальные настройки
- Войти в браузере в */local/.migrations/* и авторизоваться как *admin*
- Выделить все элементы и запустить процесс выполнения скриптов миграции

##Разворачивание ККБ

Рекомендуется все указанные пароли заменить на собственные

- `echo "CREATE DATABASE scc;"|sudo mysql -u root -p` - создаём БД под
  ККБ
- `echo "GRANT ALL PRIVILEGES ON scc.* TO scc@localhost IDENTIFIED BY 'AKw5vxH_s';"|sudo mysql -u root -p` - создаём пользователя БД
  и даём ему пароль и права на БД.
- `echo "GRANT ALL PRIVILEGES ON scc.* TO scc@127.0.0.1 IDENTIFIED BY 'AKw5vxH_s';"|sudo mysql -u root -p` - то же самое для другого
  имени хоста
- `cp www/api/include/config.tmpl.php www/api/include/config.php` - скопировать
  шаблонный конфиг БД
- `vim www/api/include/config.php` заполнить файл актуальными доступами к БД 
    ККБ
- ```
$php www/api/migrations/000-sessions.mig.php dev.shop.ag.mos.ru
$php www/api/migrations/001-users.mig.php dev.shop.ag.mos.ru
$php www/api/migrations/002-apptications.mig.php dev.shop.ag.mos.ru
$php www/api/migrations/003-transacts-brief.mig.php dev.shop.ag.mos.ru
```




