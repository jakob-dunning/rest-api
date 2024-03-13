start:
	docker-compose up -d

stop:
	docker-compose stop

test:
	docker-compose exec php vendor/bin/phpunit

phpstan:
	docker-compose exec php vendor/bin/phpstan analyse -l 9 src tests

phpcs:
	docker-compose exec php vendor/bin/phpcs

phpcbf:
	docker-compose exec php vendor/bin/phpcbf