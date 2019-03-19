docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-pull:
	docker compose pull

docker-build:
	docker compose build --pull

cli:
	docker compose run --rm manager-php-cli php bin/app.php