Установка локальной рабочей копии магазина поощрений (для разработчика) версии
1.*
=====================

# Разворачивание в докере

+ установить docker, docker-compose, git
+ создать пустой каталог для проекта
+ слить код из репозитория, выполнив в каталоге проекта:
```
git clone git@github.com:active-citizen/shop.1c.git .
```

+ перейти в каталог docker и запустить систему
```
cd docker
make build
make up
```

+ очистить каталог www перед установкой битрикса:
```
sudo make clear-www
```

+ Скачать дистрибутив интернет-магазина в редакции Бизнес
(https://www.1c-bitrix.ru/download/business_encode_php5.zip)
и распаковать его в подкаталог *www*, запустить процедуру установки
(открыть в браузере localhost)

+ поменять владельца каталогов битрикса, чтобы они были доступны на запись
```
sudo make prepare-project
```

+ Установить базовую систему
	- на шаге создания бд задать следующие параметры
		- сервер - ag-shop-db
		- имя пользователя - user
		- пароль - secret 
		- имя базы данных - database
	- на шаге выбора готового решения для установки
	выбрать "Интернет-магазин". Все установки по-умолчанию, кроме 
    	- "Когда резервировать товар на складе" - выставить "при отгрузке"
    	- Типы плательщиков "физическое лицо"
    	- Способы оплаты "наличные"
    	- Способы доставки "самовывоз"

+ откатить очистку каталога www через git
```
sudo make return-www
```

+ сменить владельца каталога содержимого каталога www на пользователя
user заменить на свое имя пользователя в системе, можно передать uid (например 1000)
```
sudo make chown-www-user name=user
```

+ установить компоненты через composer
```
make composer-install
```

+ Копирование настроечных файлов. 
Репозиторий не содержит файлов с паролями и токенами доступпов, поэтому их
необходимо получить у администрации магазина и скопировать в рабочую копию
```
make replace-files
```

+ Выполнение миграций магазина.
  - Авторизуемся от пользователя admin и переходим по адресу /local/.migration
  - Выбираем все пункты и кликаем "Запустить". Дождаться окончания установки

+ Наполнение каталога товарами
  + выполнить `make copy-catalog`
  + Авторизуемся под пользователем admin
  + Запускаем из браузера /.integration/1c_exchange.php?type=catalog&mode=import&filename=import.xml
  + Запускаем из браузера /.integration/1c_exchange.php?type=catalog&mode=import&filename=offers.xml

# Наполнение заказами
+ Получаем у администрации магазина файл orders.xml и кладём в www/upload/1c_exchange/
+ Авторизуемся под пользователем admin
+ Запускаем из браузера /.integration/1c_exchange.php?type=sale&mode=file&filename=orders.xml




