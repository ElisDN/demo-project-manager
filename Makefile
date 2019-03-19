dev-up:
	docker run -d --name manager-apache -v ${PWD}/manager:/app -p 8080:80 manager-apache

dev-down:
	docker stop manager-apache
	docker rm manager-apache

dev-build:
	docker build --file=manager/docker/development/php-cli.docker --tag manager-php-cli manager/docker/development
	docker build --file=manager/docker/development/apache.docker --tag manager-apache manager/docker/development

dev-cli:
	docker run --rm -v ${PWD}/manager:/app manager-php-cli php bin/app.php

prod-up:
	docker run -d --name manager-apache -p 8080:80 manager-apache

prod-build:
	docker build --file=manager/docker/production/php-cli.docker --tag manager-php-cli manager
	docker build --file=manager/docker/production/apache.docker --tag manager-apache manager

prod-cli:
	docker run --rm manager-php-cli php bin/app.php