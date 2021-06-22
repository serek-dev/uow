start: stop build check

check: cs stan unit

unit:
	docker-compose run --rm composer unit

stan:
	docker-compose run --rm composer phpstan

cs:
	docker-compose run --rm composer phpcs
csfix:
	docker-compose run --rm composer phpcbf

build:
	docker-compose pull
	docker-compose build --pull
	docker-compose run --rm composer install --ignore-platform-reqs

stop:
	docker-compose down -v --remove-orphans
	docker network prune -f

install:
	docker-compose run --rm composer require phpstan/phpstan-phpunit --dev --ignore-platform-reqs
