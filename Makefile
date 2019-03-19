prod-build:
	docker build --file=manager/docker/production/php-cli.docker --tag manager-php-cli manager

prod-cli:
	docker run --rm manager-php-cli php bin/app.php