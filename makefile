start:
	docker-compose up -d

stop:
	docker-compose stop

test:
	docker-compose exec php bin/console doctrine:fixtures:load --env=test -n
	docker-compose exec php bin/phpunit

phpstan:
	docker-compose exec php vendor/bin/phpstan

phpcs:
	docker-compose exec php vendor/bin/phpcs

phpcbf:
	docker-compose exec php vendor/bin/phpcbf