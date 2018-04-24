prepare-project:
	chmod 777 ../www
	chmod 777 ../www/bitrix
	chmod 777 ../www/bitrix/modules
	chmod 777 ../tmp
	chmod 777 ../tmp/sessions
	chown www-data:www-data ../www/index.php

build:
	docker-compose -p ag-shop build

build-all:
	make build
	make prepare-project

echo-user:
	echo $$(id -u)

up:
	docker-compose -p ag-shop up -d --remove-orphans --force-recreate

down:
	docker-compose -p ag-shop down

stop:
	docker-compose -p ag-shop stop

show-ips:
	docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}} {{.Name}}' \
	$$(docker-compose -p ag-shop ps -q) | grep -Eo '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\s\/.+$$' | sed 's/\///g'

bash:
	docker-compose -p ag-shop exec --user=www-data app bash

bash-http:
	docker-compose -p ag-shop exec --user=www-data http bash

logs:
	docker-compose -p ag-shop logs
