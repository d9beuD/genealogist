build:
	docker compose build --build-arg UID=$$(id -u) --build-arg GID=$$(id -g)

build-prod:
	APP_BUILD_TARGET=prod docker compose build --build-arg UID=$$(id -u) --build-arg GID=$$(id -g)

up:
	docker compose up -d

logs:
	docker compose logs -f

stop:
	docker compose stop

restart: stop up

exec:
	docker compose exec app /bin/bash

init-symfony:
	docker compose exec app sh -c "./init-symfony.sh"

init: build up init-symfony

# Command to remove Symfony project files only
rm-symfony:
	docker compose exec app sh -c \
	"rm -rf \
	./assets \
	./bin \
	./config \
	./migrations \
	./public/index.php \
	./src \
	./templates \
	./tests \
	./translations \
	./var \
	./vendor \
	./composer.json \
	./composer.lock \
	./symfony.lock \
	./phpunit.xml.dist \
	./.env \
	./.env.test \
	./.gitignore \
	./importmap.php"

# Command to remove Docker containers and volumes
rm-containers:
	docker compose down -v

# Command to prune unused Docker data
prune:
	docker system prune -f --volumes
