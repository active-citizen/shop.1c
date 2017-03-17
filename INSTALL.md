Установка магазина поощрений "Активный гражданин"
===

# Системные требования

* Linux x86_64 (CentOS, RHEL, Debian)
* nginx > 1.8
* PHP > 5.3 (php-fpm, cli)
    * short_open_tag = 0
    * mbstring.internal_encoding = UTF-8
    * mbstring.func_overload = 2
    * realpath_cache_size = 4096k
    * pcre.recursion_limit = 10000
* php-fpm
* MySQL > 5.6
    * sql_mode = ALLOW_INVALID_DATES
* msmtp
* git
* wget
* gzip
* unzip

# Установка

- Убедиться, что установлено всё ПО, перечисленное в разделе *системные
  требования*. Отсутствующее ПО установить

## Получение необходимого кода
- `sudo adduser bitrix`
- `sudo su bitrix`
- `cd`
- `rm -frv * .*`
- `git clone https://github.com/active-citizen/shop.1c.git .`
- `cd www`
- `rm -frv local`
- `wget -O bitrix.zip "http://www.1c-bitrix.ru/download/business_encode_php5.zip"`
- `unzip -o bitrix.zip`
- `rm bitrix.zip`

## Настройка серверного ПО

### Настройка nginx
- `sudo mv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.origin`
- `sudo ln -s /home/bitrix/etc/nginx/nginx.conf /etc/nginx/nginx.conf`
- `sudo service nginx restart`

### Настройка php
- `sudo mv /etc/php5/fpm/php.ini /etc/php5/fpm/php.ini.origin`
- `sudo ln -s /home/bitrix/etc/php.ini /etc/php5/fpm/php.ini`
- `sudo service php5-fpm restart`

### Настройка php-fpm

- `sudo mv /etc/php5/fpm/php-fpm.conf /etc/php5/fpm/php-fpm.conf.origin`
- `sudo ln -s /home/bitrix/etc/php-fpm.conf /etc/php5/fpm/php-fpm.conf`
- `sudo service php5-fpm restart`

### Настройка mysql

*Рекомендуется имя базы, имя пользователя и пароль заменить на собственные.
Помните, что пароль должен содержать большие и маленькие латинские буквы, цифры,
знаки препинания и быть не короче 8 символов*

- `echo "CREATE DATABASE agshop_prod;"|sudo mysql -u root -p`
- `echo "GRANT ALL PRIVILEGES ON agshop_prod.* TO agshop_user@localhost IDENTIFIED BY 'd5Rt(s0Mxq';"|sudo mysql -u root -p`
- `echo "GRANT ALL PRIVILEGES ON agshop_prod.* TO agshop_user@127.0.0.1 IDENTIFIED BY 'd5Rt(s0Mxq';"|sudo mysql -u root -p`

### Настройка msmtp
- `sudo cp /home/bitrix/etc/msmtprc /etc/msmtprc`
- заполнить атрибуты подключение к SMTP-аккаунту


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





