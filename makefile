setup: start
	docker-compose exec php composer install
	docker-compose exec php bin/console doctrine:database:create --env=test -n
	docker-compose exec php bin/console doctrine:migrations:migrate --env=test -n
	docker-compose exec php bin/console doctrine:migrations:migrate -n
	cp phpunit.xml.dist phpunit.xml
	cp phpstan.dist.neon phpstan.neon
	cp phpcs.xml.dist phpcs.xml

start:
	docker-compose up -d

stop:
	docker-compose stop

test:
	docker-compose exec php bin/console doctrine:fixtures:load --env=test -n
	docker-compose exec php bin/phpunit

phpstan:
	docker-compose exec -T php vendor/bin/phpstan

phpcs:
	docker-compose exec -T php vendor/bin/phpcs

phpcbf:
	docker-compose exec php vendor/bin/phpcbf