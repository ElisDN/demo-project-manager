cli:
	docker run --rm -v ${PWD}/manager:/app --workdir=/app php:7.2-cli php bin/app.php