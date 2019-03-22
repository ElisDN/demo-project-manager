dev-up:
	docker network create app
	docker run -d --name manager-php-fpm -v ${PWD}/manager:/app --network=app manager-php-fpm
	docker run -d --name manager-nginx -v ${PWD}/manager:/app -p 8080:80 --network=app manager-nginx

dev-down:
	docker stop manager-nginx
	docker stop manager-php-fpm
	docker rm manager-nginx
	docker rm manager-php-fpm
	docker network remove app

dev-build:
	docker build --file=manager/docker/development/nginx.docker --tag manager-nginx manager/docker/development
	docker build --file=manager/docker/development/php-fpm.docker --tag manager-php-fpm manager/docker/development
	docker build --file=manager/docker/development/php-cli.docker --tag manager-php-cli manager/docker/development

dev-cli:
	docker run --rm -v ${PWD}/manager:/app manager-php-cli php bin/app.php

prod-up:
	docker run -d --name manager-php-fpm manager-php-fpm
	docker run -d --name manager-nginx -p 8080:80 manager-nginx

prod-build:
	docker build --file=manager/docker/production/nginx.docker --tag manager-php-fpm manager
	docker build --file=manager/docker/production/php-fpm.docker --tag manager-php-fpm manager
	docker build --file=manager/docker/production/php-cli.docker --tag manager-php-cli manager

prod-cli:
	docker run --rm manager-php-cli php bin/app.php